<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Supervisor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Production-grade Supervisor Assignment Service
 *
 * Implements strict, fair assignment:
 * - AOI match only
 * - Rank priority (Professor < Associate < Assistant < Lecturer by numeric rank_priority)
 * - Strict no-consecutive supervisor within the same AOI when an alternative exists
 * - Per-AOI low-watermark balancing during a run (distributes evenly within an AOI)
 * - Global capacity enforcement (shared across AOIs)
 * - Concurrency safety is provided by the controller transaction and DB row locks
 */
class SupervisorAssignmentService
{
    /**
     * Run lottery assignment with strict AOI fairness and global capacity.
     * Mode:
     * - 'aoi' (default): match by area of interest (existing behavior)
     * - 'ranking': ignore AOI and distribute globally by ranking in round-robin
     */
    public function runLotteryAssignment(Collection $groups, string $mode = 'aoi'): array
    {
        if ($mode === 'ranking') {
            return $this->runRankingAssignment($groups);
        }
        $results = [
            'assigned' => 0,
            'unassigned' => 0,
            'no_matches' => 0,
            'assignments' => []
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);

        // Build AOI pools and global capacity
        $areaIds = $sortedGroups->pluck('area_of_interest_id')->filter()->unique()->values();
        [$pools, $areaHasSupervisors, $globalCapacity] = $this->buildAOIPools($areaIds);

        // Per-run AOI fairness trackers
        $lastPickPerAoi = []; // [aoiId => supervisorId]
        $aoiRunCounts = [];   // [aoiId => [supervisorId => count]]

        foreach ($sortedGroups as $group) {
            if (!$group->area_of_interest_id) {
                $results['unassigned']++;
                continue;
            }

            $aoiId = (int) $group->area_of_interest_id;

            $pick = $this->selectSupervisor(
                $pools,
                $globalCapacity,
                $aoiId,
                $lastPickPerAoi,
                $aoiRunCounts
            );

            if ($pick === null) {
                if (!($areaHasSupervisors[$aoiId] ?? false)) {
                    $results['no_matches']++;
                } else {
                    $results['unassigned']++;
                }
                continue;
            }

            // Concurrency-safe recheck is handled by controller transaction + row locks
            $supId = $pick['supervisor']->id;

            // Persist assignment
            $group->update([
                'supervisor_id' => $supId,
                'is_manual_assignment' => false,
                'assigned_at' => now(),
                'assignment_priority' => $pick['rank_priority'],
            ]);

            $results['assigned']++;
            $results['assignments'][] = [
                'group' => $group->name,
                'supervisor' => $pick['supervisor']->fullname,
                'designation' => $pick['supervisor']->designation,
                'method' => 'aoi_round_robin_low_watermark',
            ];
        }

        return $results;
    }

    /**
     * Preview lottery assignment without persistence.
     * Mode:
     * - 'aoi' (default): match by area of interest
     * - 'ranking': ignore AOI and distribute globally by ranking
     */
    public function previewLotteryAssignment(Collection $groups, string $mode = 'aoi'): array
    {
        if ($mode === 'ranking') {
            return $this->previewRankingAssignment($groups);
        }
        $preview = [
            'assignments' => [],
            'unassigned' => [],
            'stats' => [
                'total_groups' => $groups->count(),
                'will_be_assigned' => 0,
                'will_remain_unassigned' => 0,
                'no_matching_supervisors' => 0
            ]
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);

        $areaIds = $sortedGroups->pluck('area_of_interest_id')->filter()->unique()->values();
        [$pools, $areaHasSupervisors, $globalCapacity] = $this->buildAOIPools($areaIds);

        // Per-run AOI fairness trackers
        $lastPickPerAoi = [];
        $aoiRunCounts = [];

        foreach ($sortedGroups as $group) {
            if (!$group->area_of_interest_id) {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => 'Not Set',
                    'reason' => 'no_area_of_interest'
                ];
                $preview['stats']['will_remain_unassigned']++;
                continue;
            }

            $aoiId = (int) $group->area_of_interest_id;

            $pick = $this->selectSupervisor(
                $pools,
                $globalCapacity,
                $aoiId,
                $lastPickPerAoi,
                $aoiRunCounts
            );

            if ($pick === null) {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => optional($group->areaOfInterest)->name,
                    'reason' => (!($areaHasSupervisors[$aoiId] ?? false)) ? 'no_matches' : 'no_available_slots',
                ];
                if (!($areaHasSupervisors[$aoiId] ?? false)) {
                    $preview['stats']['no_matching_supervisors']++;
                } else {
                    $preview['stats']['will_remain_unassigned']++;
                }
                continue;
            }

            $preview['assignments'][] = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'area_of_interest' => optional($group->areaOfInterest)->name,
                'supervisor_id' => $pick['supervisor']->id,
                'supervisor_name' => $pick['supervisor']->fullname,
                'designation' => $pick['supervisor']->designation,
                'rank_priority' => $pick['rank_priority'],
                'method' => 'aoi_round_robin_low_watermark'
            ];
            $preview['stats']['will_be_assigned']++;
        }

        return $preview;
    }

    /**
     * Run global ranking-based lottery ignoring AOI.
     * Distributes fairly across all active supervisors using round-robin across the globally
     * sorted list (by rank_priority asc, then current load, then name), avoiding immediate
     * consecutive picks when alternatives exist.
     */
    private function runRankingAssignment(Collection $groups): array
    {
        $results = [
            'assigned' => 0,
            'unassigned' => 0,
            'no_matches' => 0,
            'assignments' => []
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);

        // Build global pool and availability
        $supervisors = Supervisor::where('is_active', true)->get();

        $availability = [];
        $list = [];
        foreach ($supervisors as $s) {
            $availability[$s->id] = $s->available_slots;
            if ($s->available_slots > 0) {
                $list[] = [
                    'id' => $s->id,
                    'model' => $s,
                    'rank' => $s->rank_priority,
                ];
            }
        }

        // Sort by rank asc (1 best), then current assigned load, then name
        usort($list, function ($a, $b) {
            if ($a['rank'] !== $b['rank']) return $a['rank'] <=> $b['rank'];
            $aLoad = $a['model']->assigned_theses_count;
            $bLoad = $b['model']->assigned_theses_count;
            if ($aLoad !== $bLoad) return $aLoad <=> $bLoad;
            return strcasecmp($a['model']->fullname, $b['model']->fullname);
        });

        $count = count($list);
        if ($count === 0) {
            // No capacity available anywhere
            foreach ($sortedGroups as $group) {
                $results['unassigned']++;
            }
            return $results;
        }

        $cursor = 0;
        $lastPick = null;

        foreach ($sortedGroups as $group) {
            // Select next available supervisor by global round-robin
            $pickIdx = null;
            $pick = null;

            // First pass: try to avoid consecutive same-supervisor when alternative exists
            for ($i = 0; $i < $count; $i++) {
                $idx = ($cursor + $i) % $count;
                $sup = $list[$idx];
                $supId = $sup['id'];
                if (($availability[$supId] ?? 0) <= 0) continue;
                if ($lastPick !== null && $supId === $lastPick && $count > 1) continue;
                $pickIdx = $idx;
                $pick = $sup;
                break;
            }

            // Second pass: allow consecutive if no alternative exists
            if ($pick === null) {
                for ($i = 0; $i < $count; $i++) {
                    $idx = ($cursor + $i) % $count;
                    $sup = $list[$idx];
                    $supId = $sup['id'];
                    if (($availability[$supId] ?? 0) > 0) {
                        $pickIdx = $idx;
                        $pick = $sup;
                        break;
                    }
                }
            }

            if ($pick === null) {
                // No one available globally
                $results['unassigned']++;
                continue;
            }

            // Apply
            $supModel = $pick['model'];
            $supId = $pick['id'];
            $availability[$supId] = max(0, $availability[$supId] - 1);
            $lastPick = $supId;
            $cursor = ($pickIdx + 1) % $count;

            // Persist
            $group->update([
                'supervisor_id' => $supId,
                'is_manual_assignment' => false,
                'assigned_at' => now(),
                'assignment_priority' => $pick['rank'],
            ]);

            $results['assigned']++;
            $results['assignments'][] = [
                'group' => $group->name,
                'supervisor' => $supModel->fullname,
                'designation' => $supModel->designation,
                'method' => 'global_ranking_round_robin',
            ];
        }

        return $results;
    }

    /**
     * Preview of the global ranking-based lottery without persistence.
     */
    private function previewRankingAssignment(Collection $groups): array
    {
        $preview = [
            'assignments' => [],
            'unassigned' => [],
            'stats' => [
                'total_groups' => $groups->count(),
                'will_be_assigned' => 0,
                'will_remain_unassigned' => 0,
                'no_matching_supervisors' => 0
            ]
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);

        // Build global pool and availability
        $supervisors = Supervisor::where('is_active', true)->get();

        $availability = [];
        $list = [];
        foreach ($supervisors as $s) {
            $availability[$s->id] = $s->available_slots;
            if ($s->available_slots > 0) {
                $list[] = [
                    'id' => $s->id,
                    'model' => $s,
                    'rank' => $s->rank_priority,
                ];
            }
        }

        usort($list, function ($a, $b) {
            if ($a['rank'] !== $b['rank']) return $a['rank'] <=> $b['rank'];
            $aLoad = $a['model']->assigned_theses_count;
            $bLoad = $b['model']->assigned_theses_count;
            if ($aLoad !== $bLoad) return $aLoad <=> $bLoad;
            return strcasecmp($a['model']->fullname, $b['model']->fullname);
        });

        $count = count($list);
        $cursor = 0;
        $lastPick = null;

        foreach ($sortedGroups as $group) {
            $pickIdx = null;
            $pick = null;

            // Avoid consecutive when possible
            for ($i = 0; $i < max(1, $count); $i++) {
                if ($count === 0) break;
                $idx = ($cursor + $i) % $count;
                $sup = $list[$idx];
                $supId = $sup['id'];
                if (($availability[$supId] ?? 0) <= 0) continue;
                if ($lastPick !== null && $supId === $lastPick && $count > 1) continue;
                $pickIdx = $idx;
                $pick = $sup;
                break;
            }

            if ($pick === null && $count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    $idx = ($cursor + $i) % $count;
                    $sup = $list[$idx];
                    $supId = $sup['id'];
                    if (($availability[$supId] ?? 0) > 0) {
                        $pickIdx = $idx;
                        $pick = $sup;
                        break;
                    }
                }
            }

            if ($pick === null) {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => optional($group->areaOfInterest)->name,
                    'reason' => 'no_available_slots',
                ];
                $preview['stats']['will_remain_unassigned']++;
                continue;
            }

            // Apply simulated pick
            $supModel = $pick['model'];
            $supId = $pick['id'];
            $availability[$supId] = max(0, $availability[$supId] - 1);
            $lastPick = $supId;
            $cursor = ($pickIdx + 1) % max(1, $count);

            $preview['assignments'][] = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'area_of_interest' => optional($group->areaOfInterest)->name,
                'supervisor_id' => $supId,
                'supervisor_name' => $supModel->fullname,
                'designation' => $supModel->designation,
                'rank_priority' => $pick['rank'],
                'method' => 'global_ranking_round_robin'
            ];
            $preview['stats']['will_be_assigned']++;
        }

        return $preview;
    }

    /**
     * Build AOI pools grouped by rank, plus a global availability map and AOI availability flags.
     *
     * pools[aoiId][rank] = [
     *   'cursor' => int,
     *   'supervisors' => [ [ 'id' => int, 'model' => Supervisor ], ... ]
     * ]
     */
    private function buildAOIPools(Collection $areaIds): array
    {
        $pools = [];
        $areaHasSupervisors = [];
        $availability = [];

        if ($areaIds->isEmpty()) {
            return [$pools, $areaHasSupervisors, $availability];
        }

        $supervisors = Supervisor::where('is_active', true)
            ->with('areasOfInterest')
            ->get();

        $targetAreas = $areaIds->map(fn($id) => (int) $id)->toArray();

        foreach ($supervisors as $s) {
            $availability[$s->id] = $s->available_slots;
        }

        foreach ($supervisors as $s) {
            if (($availability[$s->id] ?? 0) <= 0) {
                continue;
            }
            $aoiList = $s->areasOfInterest->pluck('id')->map(fn($id) => (int) $id)->toArray();
            foreach ($aoiList as $aoiId) {
                if (!in_array($aoiId, $targetAreas, true)) continue;
                $areaHasSupervisors[$aoiId] = true;
                $rank = $s->rank_priority;
                $pools[$aoiId] = $pools[$aoiId] ?? [];
                $pools[$aoiId][$rank] = $pools[$aoiId][$rank] ?? ['cursor' => 0, 'supervisors' => []];
                $pools[$aoiId][$rank]['supervisors'][] = [
                    'id' => $s->id,
                    'model' => $s,
                ];
            }
        }

        // Sort ranks and set initial cursors
        foreach ($pools as $aoiId => &$ranks) {
            ksort($ranks);
            foreach ($ranks as $rank => &$bucket) {
                // Stable order by current assigned count then name for fairness start
                usort($bucket['supervisors'], function ($a, $b) {
                    $aLoad = $a['model']->assigned_theses_count;
                    $bLoad = $b['model']->assigned_theses_count;
                    if ($aLoad === $bLoad) {
                        return strcasecmp($a['model']->fullname, $b['model']->fullname);
                    }
                    return $aLoad <=> $bLoad;
                });
                $bucket['cursor'] = 0;
            }
        }
        unset($ranks, $bucket);

        return [$pools, $areaHasSupervisors, $availability];
    }

    /**
     * Select next supervisor for a given AOI using:
     * - Highest available rank first
     * - Strict AOI round-robin (cursor) with low-watermark balancing in rank
     * - Avoid immediate consecutive same-supervisor within AOI when alternative exists
     * - Global availability enforcement
     *
     * Modifies $pools (cursor) and $availability (decrement) and $aoiRunCounts (increment)
     */
    private function selectSupervisor(
        array &$pools,
        array &$availability,
        int $aoiId,
        array &$lastPickPerAoi,
        array &$aoiRunCounts
    ): ?array {
        if (!isset($pools[$aoiId])) return null;

        $lastId = $lastPickPerAoi[$aoiId] ?? null;

        // Build cross-rank candidate list (excluding last-picked) with rotation ordering
        $candidates = [];
        foreach ($pools[$aoiId] as $rank => &$bucket) {
            $count = count($bucket['supervisors']);
            if ($count === 0) continue;
            $start = $bucket['cursor'] % max(1, $count);
            for ($i = 0; $i < $count; $i++) {
                $idx = ($start + $i) % $count;
                $supId = $bucket['supervisors'][$idx]['id'];
                if ($lastId !== null && $supId === $lastId) continue; // avoid consecutive within AOI when alternative exists
                if (($availability[$supId] ?? 0) <= 0) continue; // no capacity
                $run = $aoiRunCounts[$aoiId][$supId] ?? 0;
                $candidates[] = [
                    'rank' => $rank,
                    'idx' => $idx,
                    'orderInBucket' => $i, // closer to cursor first
                    'supId' => $supId,
                    'model' => $bucket['supervisors'][$idx]['model'],
                    'run' => $run,
                ];
            }
        }

        if (!empty($candidates)) {
            // Low-watermark across all ranks: prefer lowest per-run AOI count, then higher rank, then rotation order
            usort($candidates, function ($a, $b) {
                if ($a['run'] !== $b['run']) return $a['run'] <=> $b['run'];
                if ($a['rank'] !== $b['rank']) return $a['rank'] <=> $b['rank']; // rank_priority asc (1 best)
                return $a['orderInBucket'] <=> $b['orderInBucket'];
            });

            $choice = $candidates[0];
            // Apply selection
            $availability[$choice['supId']] = max(0, $availability[$choice['supId']] - 1);
            $bucketCount = count($pools[$aoiId][$choice['rank']]['supervisors']);
            $pools[$aoiId][$choice['rank']]['cursor'] = ($choice['idx'] + 1) % max(1, $bucketCount);
            $aoiRunCounts[$aoiId][$choice['supId']] = ($aoiRunCounts[$aoiId][$choice['supId']] ?? 0) + 1;
            $lastPickPerAoi[$aoiId] = $choice['supId'];

            return [
                'supervisor' => $choice['model'],
                'rank_priority' => $choice['rank'],
            ];
        }

        // Fallback: allow last-picked if no alternative exists across ranks
        if ($lastId !== null) {
            foreach ($pools[$aoiId] as $rank => &$bucket) {
                $count = count($bucket['supervisors']);
                if ($count === 0) continue;
                $start = $bucket['cursor'] % max(1, $count);
                for ($i = 0; $i < $count; $i++) {
                    $idx = ($start + $i) % $count;
                    $supId = $bucket['supervisors'][$idx]['id'];
                    if ($supId !== $lastId) continue;
                    if (($availability[$supId] ?? 0) > 0) {
                        $availability[$supId] = max(0, $availability[$supId] - 1);
                        $bucket['cursor'] = ($idx + 1) % $count;
                        $aoiRunCounts[$aoiId][$supId] = ($aoiRunCounts[$aoiId][$supId] ?? 0) + 1;
                        $lastPickPerAoi[$aoiId] = $supId;
                        return [
                            'supervisor' => $bucket['supervisors'][$idx]['model'],
                            'rank_priority' => $rank,
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Sort groups by numeric suffix for deterministic processing.
     */
    private function sortGroupsByNumber(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            if (preg_match('/(\d+)/', $group->name, $m)) return (int) $m[1];
            return 9999;
        });
    }
}
