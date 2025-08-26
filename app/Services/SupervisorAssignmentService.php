<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Supervisor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Supervisor Assignment Service with Multiple Areas Support and Randomization
 *
 * Implements flexible assignment modes:
 * - 'aoi': Area of Interest matching only (with randomization)
 * - 'ranking': Ranking priority only (ignores AOI)
 * - 'both': Prioritizes both AOI and ranking
 * - Supports multiple areas of interest per group (fallback to secondary areas)
 * - Random selection within same rank for fairness
 */
class SupervisorAssignmentService
{
    /**
     * Run lottery assignment with flexible mode support
     */
    public function runLotteryAssignment(Collection $groups, string $mode = 'aoi'): array
    {
        switch ($mode) {
            case 'ranking':
                return $this->runRankingAssignment($groups);
            case 'both':
                return $this->runCombinedAssignment($groups);
            case 'aoi':
            default:
                return $this->runAOIAssignment($groups);
        }
    }

    /**
     * Preview lottery assignment without persistence
     */
    public function previewLotteryAssignment(Collection $groups, string $mode = 'aoi'): array
    {
        switch ($mode) {
            case 'ranking':
                return $this->previewRankingAssignment($groups);
            case 'both':
                return $this->previewCombinedAssignment($groups);
            case 'aoi':
            default:
                return $this->previewAOIAssignment($groups);
        }
    }

    /**
     * Run AOI-based assignment with multiple areas support and randomization
     */
    private function runAOIAssignment(Collection $groups): array
    {
        $results = [
            'assigned' => 0,
            'unassigned' => 0,
            'no_matches' => 0,
            'assignments' => []
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);
        
        // Collect all unique area IDs from groups (including multiple areas)
        $allAreaIds = [];
        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            $allAreaIds = array_merge($allAreaIds, $areaIds);
        }
        $allAreaIds = array_unique($allAreaIds);

        // Build pools and capacity with randomization
        [$pools, $areaHasSupervisors, $globalCapacity] = $this->buildAOIPoolsWithRandomization(collect($allAreaIds));

        // Track assignments per supervisor for load balancing
        $assignmentCounts = [];

        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            
            if (empty($areaIds)) {
                $results['unassigned']++;
                continue;
            }

            // Try each area of interest in order (primary first, then fallbacks)
            $assigned = false;
            foreach ($areaIds as $aoiId) {
                $aoiId = (int) $aoiId;
                
                $pick = $this->selectRandomSupervisor(
                    $pools,
                    $globalCapacity,
                    $aoiId,
                    $assignmentCounts,
                    $group->id
                );

                if ($pick !== null) {
                    // Assign supervisor and store which area was matched
                    $group->update([
                        'supervisor_id' => $pick['supervisor']->id,
                        'matched_area_of_interest_id' => $aoiId,
                        'is_manual_assignment' => false,
                        'assigned_at' => now(),
                        'assignment_priority' => $pick['rank_priority'],
                    ]);

                    // Record assignment history for intelligent future assignments
                    \App\Models\AssignmentHistory::recordAssignment(
                        $group->id,
                        $pick['supervisor']->id,
                        $aoiId,
                        'lottery_aoi'
                    );

                    $results['assigned']++;
                    $results['assignments'][] = [
                        'group' => $group->name,
                        'supervisor' => $pick['supervisor']->fullname,
                        'designation' => $pick['supervisor']->designation,
                        'area_matched' => $aoiId,
                        'method' => 'aoi_matching_random',
                    ];
                    
                    $assigned = true;
                    break; // Stop trying other areas once assigned
                }
            }

            if (!$assigned) {
                // Check if any area had supervisors
                $hasAnySupervisors = false;
                foreach ($areaIds as $aoiId) {
                    if ($areaHasSupervisors[(int)$aoiId] ?? false) {
                        $hasAnySupervisors = true;
                        break;
                    }
                }
                
                if (!$hasAnySupervisors) {
                    $results['no_matches']++;
                } else {
                    $results['unassigned']++;
                }
            }
        }

        return $results;
    }

    /**
     * Run ranking-based assignment with PROPER ROUND-ROBIN
     * Each supervisor gets one group before any supervisor gets a second
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

        // Build supervisor list sorted by rank
        $supervisors = Supervisor::where('is_active', true)->get();
        $supervisorList = [];
        
        foreach ($supervisors as $s) {
            if ($s->available_slots > 0) {
                $supervisorList[] = [
                    'id' => $s->id,
                    'model' => $s,
                    'rank' => $s->rank_priority,
                    'available_slots' => $s->available_slots,
                    'assigned_count' => 0
                ];
            }
        }

        // Sort by rank priority (Professor > Associate > Assistant > Lecturer)
        usort($supervisorList, function ($a, $b) {
            if ($a['rank'] !== $b['rank']) {
                return $a['rank'] <=> $b['rank'];
            }
            // If same rank, sort by name for consistency
            return strcasecmp($a['model']->fullname, $b['model']->fullname);
        });

        if (count($supervisorList) === 0) {
            foreach ($sortedGroups as $group) {
                $results['unassigned']++;
            }
            return $results;
        }

        // PROPER ROUND-ROBIN IMPLEMENTATION
        $currentRound = 0;
        $supervisorIndex = 0;
        
        foreach ($sortedGroups as $group) {
            $assigned = false;
            $attempts = 0;
            $totalSupervisors = count($supervisorList);
            
            // Try to find a supervisor who hasn't been assigned in this round
            while (!$assigned && $attempts < $totalSupervisors) {
                $supervisor = $supervisorList[$supervisorIndex];
                
                // Check if this supervisor can take more groups
                if ($supervisor['assigned_count'] <= $currentRound && 
                    $supervisor['assigned_count'] < $supervisor['available_slots']) {
                    
                    // Assign the supervisor
                    $group->update([
                        'supervisor_id' => $supervisor['id'],
                        'is_manual_assignment' => false,
                        'assigned_at' => now(),
                        'assignment_priority' => $supervisor['rank'],
                    ]);

                    // Update tracking
                    $supervisorList[$supervisorIndex]['assigned_count']++;
                    
                    $results['assigned']++;
                    $results['assignments'][] = [
                        'group' => $group->name,
                        'supervisor' => $supervisor['model']->fullname,
                        'designation' => $supervisor['model']->designation,
                        'method' => 'ranking_round_robin',
                    ];
                    
                    $assigned = true;
                }
                
                // Move to next supervisor
                $supervisorIndex = ($supervisorIndex + 1) % $totalSupervisors;
                
                // If we've completed a full cycle, move to next round
                if ($supervisorIndex === 0) {
                    $currentRound++;
                }
                
                $attempts++;
            }
            
            if (!$assigned) {
                $results['unassigned']++;
            }
        }

        return $results;
    }

    /**
     * Run combined assignment with INTELLIGENT ROUND-ROBIN
     * Prioritizes both AOI and ranking but ensures fair load distribution
     * Prevents senior professors from getting all groups
     */
    private function runCombinedAssignment(Collection $groups): array
    {
        $results = [
            'assigned' => 0,
            'unassigned' => 0,
            'no_matches' => 0,
            'assignments' => []
        ];

        $sortedGroups = $this->sortGroupsByNumber($groups);
        
        // Collect all unique area IDs
        $allAreaIds = [];
        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            $allAreaIds = array_merge($allAreaIds, $areaIds);
        }
        $allAreaIds = array_unique($allAreaIds);

        // Build pools WITHOUT randomization for strict ranking
        [$pools, $areaHasSupervisors, $globalCapacity] = $this->buildAOIPoolsWithoutRandomization(collect($allAreaIds));
        
        // Track global assignments for intelligent load balancing
        $globalAssignmentCounts = [];
        $lastAssignedPerArea = [];

        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            $assigned = false;

            // Try AOI matching with intelligent round-robin
            if (!empty($areaIds)) {
                foreach ($areaIds as $aoiId) {
                    $aoiId = (int) $aoiId;
                    
                    $pick = $this->selectSupervisorWithIntelligentRoundRobin(
                        $pools,
                        $globalCapacity,
                        $aoiId,
                        $globalAssignmentCounts,
                        $lastAssignedPerArea
                    );

                    if ($pick !== null) {
                        // Assign supervisor
                        $group->update([
                            'supervisor_id' => $pick['supervisor']->id,
                            'matched_area_of_interest_id' => $aoiId,
                            'is_manual_assignment' => false,
                            'assigned_at' => now(),
                            'assignment_priority' => $pick['rank_priority'],
                        ]);

                        $results['assigned']++;
                        $results['assignments'][] = [
                            'group' => $group->name,
                            'supervisor' => $pick['supervisor']->fullname,
                            'designation' => $pick['supervisor']->designation,
                            'area_matched' => $aoiId,
                            'method' => 'combined_intelligent_round_robin',
                        ];
                        
                        $assigned = true;
                        break;
                    }
                }
            }

            if (!$assigned) {
                // Check if any area had supervisors
                $hasAnySupervisors = false;
                foreach ($areaIds as $aoiId) {
                    if ($areaHasSupervisors[(int)$aoiId] ?? false) {
                        $hasAnySupervisors = true;
                        break;
                    }
                }
                
                if (!$hasAnySupervisors) {
                    $results['no_matches']++;
                } else {
                    $results['unassigned']++;
                }
            }
        }

        return $results;
    }

    /**
     * Preview AOI-based assignment
     */
    private function previewAOIAssignment(Collection $groups): array
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
        
        $allAreaIds = [];
        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            $allAreaIds = array_merge($allAreaIds, $areaIds);
        }
        $allAreaIds = array_unique($allAreaIds);

        [$pools, $areaHasSupervisors, $globalCapacity] = $this->buildAOIPoolsWithRandomization(collect($allAreaIds));
        $assignmentCounts = [];

        foreach ($sortedGroups as $group) {
            $areaIds = $group->getAreaOfInterestIds();
            
            if (empty($areaIds)) {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => 'Not Set',
                    'reason' => 'no_area_of_interest'
                ];
                $preview['stats']['will_remain_unassigned']++;
                continue;
            }

            $assigned = false;
            $areaNames = [];
            
            if ($group->areasOfInterest->count() > 0) {
                $areaNames = $group->areasOfInterest->pluck('name')->toArray();
            } elseif ($group->areaOfInterest) {
                $areaNames = [$group->areaOfInterest->name];
            }

            foreach ($areaIds as $aoiId) {
                $aoiId = (int) $aoiId;
                
                $pick = $this->selectRandomSupervisor(
                    $pools,
                    $globalCapacity,
                    $aoiId,
                    $assignmentCounts
                );

                if ($pick !== null) {
                    $preview['assignments'][] = [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'area_of_interest' => implode(', ', $areaNames),
                        'supervisor_id' => $pick['supervisor']->id,
                        'supervisor_name' => $pick['supervisor']->fullname,
                        'designation' => $pick['supervisor']->designation,
                        'rank_priority' => $pick['rank_priority'],
                        'method' => 'aoi_matching_random'
                    ];
                    $preview['stats']['will_be_assigned']++;
                    $assigned = true;
                    break;
                }
            }

            if (!$assigned) {
                $hasAnySupervisors = false;
                foreach ($areaIds as $aoiId) {
                    if ($areaHasSupervisors[(int)$aoiId] ?? false) {
                        $hasAnySupervisors = true;
                        break;
                    }
                }
                
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => implode(', ', $areaNames),
                    'reason' => $hasAnySupervisors ? 'no_available_slots' : 'no_matches'
                ];
                
                if (!$hasAnySupervisors) {
                    $preview['stats']['no_matching_supervisors']++;
                } else {
                    $preview['stats']['will_remain_unassigned']++;
                }
            }
        }

        return $preview;
    }

    /**
     * Preview ranking-based assignment
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

        $supervisors = Supervisor::where('is_active', true)->get();
        $availability = [];
        $rankGroups = [];
        
        foreach ($supervisors as $s) {
            $availability[$s->id] = $s->available_slots;
            if ($s->available_slots > 0) {
                $rank = $s->rank_priority;
                if (!isset($rankGroups[$rank])) {
                    $rankGroups[$rank] = [];
                }
                $rankGroups[$rank][] = [
                    'id' => $s->id,
                    'model' => $s,
                    'rank' => $rank,
                ];
            }
        }

        ksort($rankGroups);
        foreach ($rankGroups as &$group) {
            shuffle($group);
        }

        $list = [];
        foreach ($rankGroups as $group) {
            $list = array_merge($list, $group);
        }

        $assignmentCounts = [];

        foreach ($sortedGroups as $group) {
            $areaNames = [];
            if ($group->areasOfInterest->count() > 0) {
                $areaNames = $group->areasOfInterest->pluck('name')->toArray();
            } elseif ($group->areaOfInterest) {
                $areaNames = [$group->areaOfInterest->name];
            }

            $pick = $this->selectFromRandomizedList($list, $availability, $assignmentCounts);
            
            if ($pick === null) {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => !empty($areaNames) ? implode(', ', $areaNames) : 'Not Set',
                    'reason' => 'no_available_slots'
                ];
                $preview['stats']['will_remain_unassigned']++;
            } else {
                $preview['assignments'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => !empty($areaNames) ? implode(', ', $areaNames) : 'Not Set',
                    'supervisor_id' => $pick['id'],
                    'supervisor_name' => $pick['model']->fullname,
                    'designation' => $pick['model']->designation,
                    'rank_priority' => $pick['rank'],
                    'method' => 'ranking_priority_random'
                ];
                $preview['stats']['will_be_assigned']++;
            }
        }

        return $preview;
    }

    /**
     * Preview combined assignment
     */
    private function previewCombinedAssignment(Collection $groups): array
    {
        return $this->previewAOIAssignment($groups);
    }

    /**
     * Select from randomized list with load balancing
     */
    private function selectFromRandomizedList(array $list, array &$availability, array &$assignmentCounts): ?array
    {
        if (empty($list)) return null;

        // Group by rank
        $byRank = [];
        foreach ($list as $item) {
            $rank = $item['rank'];
            if (!isset($byRank[$rank])) {
                $byRank[$rank] = [];
            }
            if (($availability[$item['id']] ?? 0) > 0) {
                $byRank[$rank][] = $item;
            }
        }

        // Try each rank in order
        ksort($byRank);
        foreach ($byRank as $rank => $candidates) {
            if (empty($candidates)) continue;

            // Find candidates with minimum assignments for load balancing
            $minAssignments = PHP_INT_MAX;
            foreach ($candidates as $candidate) {
                $count = $assignmentCounts[$candidate['id']] ?? 0;
                if ($count < $minAssignments) {
                    $minAssignments = $count;
                }
            }

            // Filter to only those with minimum assignments
            $bestCandidates = array_filter($candidates, function($c) use ($assignmentCounts, $minAssignments) {
                return ($assignmentCounts[$c['id']] ?? 0) == $minAssignments;
            });

            if (!empty($bestCandidates)) {
                // Randomly select from best candidates
                $pick = $bestCandidates[array_rand($bestCandidates)];
                
                // Update state
                $availability[$pick['id']] = max(0, $availability[$pick['id']] - 1);
                $assignmentCounts[$pick['id']] = ($assignmentCounts[$pick['id']] ?? 0) + 1;
                
                return $pick;
            }
        }

        return null;
    }

    /**
     * Build AOI pools with randomization
     */
    private function buildAOIPoolsWithRandomization(Collection $areaIds): array
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
                $pools[$aoiId][$rank] = $pools[$aoiId][$rank] ?? ['supervisors' => []];
                $pools[$aoiId][$rank]['supervisors'][] = [
                    'id' => $s->id,
                    'model' => $s,
                ];
            }
        }

        // Sort ranks and shuffle supervisors within each rank for randomization
        foreach ($pools as $aoiId => &$ranks) {
            ksort($ranks);
            foreach ($ranks as $rank => &$bucket) {
                // Shuffle for randomization
                shuffle($bucket['supervisors']);
            }
        }
        unset($ranks, $bucket);

        return [$pools, $areaHasSupervisors, $availability];
    }

    /**
     * Select random supervisor for a given AOI with TRUE randomization
     * Simply excludes the last assigned supervisor to ensure different selection each time
     */
    private function selectRandomSupervisor(
        array &$pools,
        array &$availability,
        int $aoiId,
        array &$assignmentCounts,
        ?int $groupId = null
    ): ?array {
        if (!isset($pools[$aoiId])) return null;

        // Get the last assigned supervisor for this group (if any)
        $lastSupervisorId = null;
        if ($groupId) {
            // Get the most recent supervisor assigned to this group
            $lastAssignment = \App\Models\AssignmentHistory::where('group_id', $groupId)
                ->orderBy('assigned_at', 'desc')
                ->first();
            
            if ($lastAssignment) {
                $lastSupervisorId = $lastAssignment->supervisor_id;
            }
        }

        // Collect ALL available candidates from ALL ranks
        $allCandidates = [];
        
        foreach ($pools[$aoiId] as $rank => $bucket) {
            foreach ($bucket['supervisors'] as $sup) {
                // Check if supervisor has capacity
                if (($availability[$sup['id']] ?? 0) <= 0) continue;
                
                // EXCLUDE the last assigned supervisor to ensure different selection
                if ($lastSupervisorId && $sup['id'] == $lastSupervisorId) {
                    // Skip this supervisor ONLY if there are other options available
                    $hasOtherOptions = false;
                    foreach ($pools[$aoiId] as $r => $b) {
                        foreach ($b['supervisors'] as $s) {
                            if ($s['id'] != $lastSupervisorId && ($availability[$s['id']] ?? 0) > 0) {
                                $hasOtherOptions = true;
                                break 2;
                            }
                        }
                    }
                    if ($hasOtherOptions) continue;
                }
                
                $allCandidates[] = [
                    'supervisor' => $sup,
                    'rank' => $rank
                ];
            }
        }

        if (empty($allCandidates)) return null;

        // PURE RANDOM SELECTION - pick any available supervisor randomly
        $randomIndex = array_rand($allCandidates);
        $choice = $allCandidates[$randomIndex];
        
        // Update state
        $availability[$choice['supervisor']['id']] = max(0, $availability[$choice['supervisor']['id']] - 1);
        $assignmentCounts[$choice['supervisor']['id']] = ($assignmentCounts[$choice['supervisor']['id']] ?? 0) + 1;
        
        return [
            'supervisor' => $choice['supervisor']['model'],
            'rank_priority' => $choice['rank'],
        ];
    }

    /**
     * Build AOI pools WITHOUT randomization for strict ranking
     */
    private function buildAOIPoolsWithoutRandomization(Collection $areaIds): array
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
                $pools[$aoiId][$rank] = $pools[$aoiId][$rank] ?? ['supervisors' => [], 'cursor' => 0];
                $pools[$aoiId][$rank]['supervisors'][] = [
                    'id' => $s->id,
                    'model' => $s,
                ];
            }
        }

        // Sort ranks and supervisors by load (NO SHUFFLING for strict ranking)
        foreach ($pools as $aoiId => &$ranks) {
            ksort($ranks);
            foreach ($ranks as $rank => &$bucket) {
                // Sort by current load (ascending) for fair distribution
                usort($bucket['supervisors'], function ($a, $b) {
                    $aLoad = $a['model']->assigned_theses_count;
                    $bLoad = $b['model']->assigned_theses_count;
                    if ($aLoad === $bLoad) {
                        // If same load, sort alphabetically for consistency
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
     * Select supervisor by strict ranking (no randomization) with round-robin within same rank
     */
    private function selectSupervisorByRanking(
        array &$pools,
        array &$availability,
        int $aoiId,
        array &$assignmentCounts,
        array &$lastAssignedPerRank
    ): ?array {
        if (!isset($pools[$aoiId])) return null;

        // Try each rank in strict order (Professor > Associate > Assistant > Lecturer)
        foreach ($pools[$aoiId] as $rank => &$bucket) {
            $count = count($bucket['supervisors']);
            if ($count === 0) continue;

            // Round-robin within the same rank for fairness
            $cursor = $bucket['cursor'] ?? 0;
            $lastId = $lastAssignedPerRank[$aoiId][$rank] ?? null;
            
            // Try to find next available supervisor in this rank
            for ($i = 0; $i < $count; $i++) {
                $idx = ($cursor + $i) % $count;
                $sup = $bucket['supervisors'][$idx];
                
                // Skip if no capacity
                if (($availability[$sup['id']] ?? 0) <= 0) continue;
                
                // Avoid consecutive assignments to same supervisor if possible
                if ($lastId !== null && $sup['id'] === $lastId && $count > 1) {
                    // Check if there's another available supervisor
                    $hasAlternative = false;
                    for ($j = 1; $j < $count; $j++) {
                        $altIdx = ($cursor + $j) % $count;
                        $altSup = $bucket['supervisors'][$altIdx];
                        if ($altSup['id'] !== $lastId && ($availability[$altSup['id']] ?? 0) > 0) {
                            $hasAlternative = true;
                            break;
                        }
                    }
                    if ($hasAlternative) continue;
                }
                
                // Found a suitable supervisor
                $availability[$sup['id']] = max(0, $availability[$sup['id']] - 1);
                $assignmentCounts[$sup['id']] = ($assignmentCounts[$sup['id']] ?? 0) + 1;
                $bucket['cursor'] = ($idx + 1) % $count;
                $lastAssignedPerRank[$aoiId][$rank] = $sup['id'];
                
                return [
                    'supervisor' => $sup['model'],
                    'rank_priority' => $rank,
                ];
            }
        }

        return null;
    }

    /**
     * Select supervisor with ABSOLUTE FAIR ROUND-ROBIN for combined mode
     * NO ONE in the same area gets 2 groups before EVERYONE in that area gets 1
     * This ensures perfect distribution within each area of interest
     */
    private function selectSupervisorWithIntelligentRoundRobin(
        array &$pools,
        array &$availability,
        int $aoiId,
        array &$globalAssignmentCounts,
        array &$lastAssignedPerArea
    ): ?array {
        if (!isset($pools[$aoiId])) return null;

        // Collect all supervisors FOR THIS SPECIFIC AREA with availability
        $areaSupervisors = [];
        $areaMinCount = PHP_INT_MAX;
        
        foreach ($pools[$aoiId] as $rank => $bucket) {
            foreach ($bucket['supervisors'] as $sup) {
                if (($availability[$sup['id']] ?? 0) > 0) {
                    $currentCount = $globalAssignmentCounts[$sup['id']] ?? 0;
                    
                    // Track minimum count FOR THIS AREA's supervisors
                    if ($currentCount < $areaMinCount) {
                        $areaMinCount = $currentCount;
                    }
                    
                    $areaSupervisors[] = [
                        'supervisor' => $sup,
                        'rank' => $rank,
                        'assignment_count' => $currentCount,
                        'id' => $sup['id']
                    ];
                }
            }
        }

        if (empty($areaSupervisors)) return null;

        // ABSOLUTE FAIRNESS WITHIN AREA:
        // Only allow supervisors with the minimum count FOR THIS AREA
        // This ensures no one in Machine Learning gets 2 before everyone in ML gets 1
        $eligibleSupervisors = array_filter($areaSupervisors, function($s) use ($areaMinCount) {
            return $s['assignment_count'] === $areaMinCount;
        });

        if (empty($eligibleSupervisors)) {
            // This should never happen, but fallback to all area supervisors
            $eligibleSupervisors = $areaSupervisors;
        }

        // Avoid consecutive assignments to the same supervisor
        $lastAssignedId = $lastAssignedPerArea[$aoiId] ?? null;
        
        // First, try to find someone who wasn't just assigned
        $notLastAssigned = [];
        if ($lastAssignedId !== null) {
            $notLastAssigned = array_filter($eligibleSupervisors, function($s) use ($lastAssignedId) {
                return $s['id'] !== $lastAssignedId;
            });
        }
        
        // Use non-consecutive candidates if available, otherwise use all eligible
        $finalCandidates = !empty($notLastAssigned) ? $notLastAssigned : $eligibleSupervisors;
        
        // Among candidates with EQUAL assignment count, apply ranking
        if (count($finalCandidates) > 1) {
            // Sort by rank (Professor > Associate > Assistant > Lecturer)
            usort($finalCandidates, function($a, $b) {
                // Only use rank when counts are EXACTLY equal
                if ($a['assignment_count'] === $b['assignment_count']) {
                    if ($a['rank'] !== $b['rank']) {
                        return $a['rank'] <=> $b['rank'];
                    }
                    // Same rank and count: rotate fairly (alphabetical for consistency)
                    return strcasecmp($a['supervisor']['model']->fullname, $b['supervisor']['model']->fullname);
                }
                // This shouldn't happen as we filtered by min count, but just in case
                return $a['assignment_count'] <=> $b['assignment_count'];
            });
        }
        
        // Pick the first candidate (highest rank among those with minimum assignments)
        $choice = reset($finalCandidates);

        if ($choice) {
            // Update state
            $availability[$choice['supervisor']['id']] = max(0, $availability[$choice['supervisor']['id']] - 1);
            $globalAssignmentCounts[$choice['supervisor']['id']] = ($globalAssignmentCounts[$choice['supervisor']['id']] ?? 0) + 1;
            $lastAssignedPerArea[$aoiId] = $choice['supervisor']['id'];
            
            // Log for debugging
            \Log::info("Combined Mode Assignment", [
                'area_id' => $aoiId,
                'supervisor' => $choice['supervisor']['model']->fullname,
                'rank' => $choice['rank'],
                'assignment_count' => $choice['assignment_count'] + 1,
                'area_min_count' => $areaMinCount,
                'total_eligible' => count($eligibleSupervisors)
            ]);
            
            return [
                'supervisor' => $choice['supervisor']['model'],
                'rank_priority' => $choice['rank'],
            ];
        }

        return null;
    }

    /**
     * Sort groups by numeric suffix
     */
    private function sortGroupsByNumber(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            if (preg_match('/(\d+)/', $group->name, $m)) return (int) $m[1];
            return 9999;
        });
    }
}