<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Supervisor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SupervisorAssignmentService
{
    /**
     * Run the lottery assignment process for groups
     */
    public function runLotteryAssignment(Collection $groups): array
    {
        $results = [
            'assigned' => 0,
            'unassigned' => 0,
            'no_matches' => 0,
            'assignments' => []
        ];

        // Sort groups by name (Group 1, 2, 3...)
        $sortedGroups = $this->sortGroupsByNumber($groups);

        foreach ($sortedGroups as $group) {
            $assignment = $this->assignSupervisorToGroup($group);
            
            if ($assignment['success']) {
                $results['assigned']++;
                $results['assignments'][] = [
                    'group' => $group->name,
                    'supervisor' => $assignment['supervisor']->fullname,
                    'designation' => $assignment['supervisor']->designation,
                    'method' => $assignment['method']
                ];
            } else {
                if ($assignment['reason'] === 'no_matches') {
                    $results['no_matches']++;
                } else {
                    $results['unassigned']++;
                }
            }
        }

        return $results;
    }

    /**
     * Preview lottery assignment without saving to database
     */
    public function previewLotteryAssignment(Collection $groups): array
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

        // Sort groups by name (Group 1, 2, 3...)
        $sortedGroups = $this->sortGroupsByNumber($groups);

        // Track supervisor availability for preview
        $supervisorAvailability = $this->buildSupervisorAvailabilityMap();

        foreach ($sortedGroups as $group) {
            $assignment = $this->findBestSupervisorForGroup($group, $supervisorAvailability, true);
            
            if ($assignment['success']) {
                $preview['assignments'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => $group->areaOfInterest->name,
                    'supervisor_id' => $assignment['supervisor']->id,
                    'supervisor_name' => $assignment['supervisor']->fullname,
                    'designation' => $assignment['supervisor']->designation,
                    'rank_priority' => $assignment['supervisor']->rank_priority,
                    'method' => $assignment['method']
                ];
                
                // Reduce supervisor availability for preview
                $supervisorAvailability[$assignment['supervisor']->id]--;
                $preview['stats']['will_be_assigned']++;
            } else {
                $preview['unassigned'][] = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'area_of_interest' => $group->areaOfInterest->name,
                    'reason' => $assignment['reason']
                ];
                
                if ($assignment['reason'] === 'no_matches') {
                    $preview['stats']['no_matching_supervisors']++;
                } else {
                    $preview['stats']['will_remain_unassigned']++;
                }
            }
        }

        return $preview;
    }

    /**
     * Assign a supervisor to a specific group
     */
    private function assignSupervisorToGroup(Group $group): array
    {
        if (!$group->area_of_interest_id) {
            return [
                'success' => false,
                'reason' => 'no_area_of_interest',
                'message' => 'Group has no area of interest assigned'
            ];
        }

        $assignment = $this->findBestSupervisorForGroup($group);

        if (!$assignment['success']) {
            return $assignment;
        }

        // Actually assign the supervisor
        $group->update([
            'supervisor_id' => $assignment['supervisor']->id,
            'is_manual_assignment' => false,
            'assigned_at' => now(),
            'assignment_priority' => $assignment['supervisor']->rank_priority
        ]);

        Log::info("Lottery assignment: {$group->name} assigned to {$assignment['supervisor']->fullname}");

        return $assignment;
    }

    /**
     * Find the best available supervisor for a group
     */
    private function findBestSupervisorForGroup(Group $group, ?array $availabilityMap = null, bool $previewMode = false): array
    {
        // Get supervisors with matching area of interest
        $matchingSupervisors = Supervisor::where('is_active', true)
            ->whereHas('areasOfInterest', function($query) use ($group) {
                $query->where('area_of_interests.id', $group->area_of_interest_id);
            })
            ->byRankPriority()
            ->get();

        if ($matchingSupervisors->isEmpty()) {
            return [
                'success' => false,
                'reason' => 'no_matches',
                'message' => 'No supervisors found with matching area of interest'
            ];
        }

        // Filter supervisors with available slots
        $availableSupervisors = $matchingSupervisors->filter(function ($supervisor) use ($availabilityMap, $previewMode) {
            if ($previewMode && $availabilityMap) {
                return isset($availabilityMap[$supervisor->id]) && $availabilityMap[$supervisor->id] > 0;
            }
            return $supervisor->available_slots > 0;
        });

        if ($availableSupervisors->isEmpty()) {
            return [
                'success' => false,
                'reason' => 'no_available_slots',
                'message' => 'All matching supervisors have reached their thesis limit'
            ];
        }

        // Group by rank priority
        $supervisorsByRank = $availableSupervisors->groupBy('rank_priority');

        // Get the highest priority rank that has available supervisors
        $highestRank = $supervisorsByRank->keys()->min();
        $highestRankSupervisors = $supervisorsByRank[$highestRank];

        // If multiple supervisors at the same rank, select randomly
        $selectedSupervisor = $highestRankSupervisors->count() > 1 
            ? $highestRankSupervisors->random()
            : $highestRankSupervisors->first();

        $method = $highestRankSupervisors->count() > 1 ? 'random_selection' : 'rank_priority';

        return [
            'success' => true,
            'supervisor' => $selectedSupervisor,
            'method' => $method,
            'rank_priority' => $highestRank
        ];
    }

    /**
     * Sort groups by their numeric value extracted from names
     */
    private function sortGroupsByNumber(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            // Extract number from group name (e.g., "Group 1" -> 1)
            if (preg_match('/(\d+)/', $group->name, $matches)) {
                return (int) $matches[1];
            }
            return 9999; // Put groups without numbers at the end
        });
    }

    /**
     * Build a map of supervisor availability for preview mode
     */
    private function buildSupervisorAvailabilityMap(): array
    {
        $supervisors = Supervisor::where('is_active', true)->get();
        $availability = [];
        
        foreach ($supervisors as $supervisor) {
            $availability[$supervisor->id] = $supervisor->available_slots;
        }
        
        return $availability;
    }

    /**
     * Get assignment statistics for all groups
     */
    public function getAssignmentStatistics(): array
    {
        $totalGroups = Group::count();
        $assignedGroups = Group::assigned()->count();
        $manualAssignments = Group::manuallyAssigned()->count();
        $lotteryAssignments = Group::assigned()->where('is_manual_assignment', false)->count();
        
        $supervisorStats = Supervisor::where('is_active', true)
            ->get()
            ->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'name' => $supervisor->fullname,
                    'designation' => $supervisor->designation,
                    'thesis_limit' => $supervisor->thesis_limit,
                    'assigned_count' => $supervisor->assigned_theses_count,
                    'available_slots' => $supervisor->available_slots,
                    'utilization_rate' => $supervisor->thesis_limit > 0 
                        ? round(($supervisor->assigned_theses_count / $supervisor->thesis_limit) * 100, 1)
                        : 0
                ];
            });

        return [
            'total_groups' => $totalGroups,
            'assigned_groups' => $assignedGroups,
            'unassigned_groups' => $totalGroups - $assignedGroups,
            'manual_assignments' => $manualAssignments,
            'lottery_assignments' => $lotteryAssignments,
            'assignment_rate' => $totalGroups > 0 ? round(($assignedGroups / $totalGroups) * 100, 1) : 0,
            'supervisor_stats' => $supervisorStats
        ];
    }

    /**
     * Validate assignment rules
     */
    public function validateAssignment(Group $group, Supervisor $supervisor): array
    {
        $errors = [];

        if (!$supervisor->is_active) {
            $errors[] = 'Supervisor is not active';
        }

        if ($supervisor->available_slots <= 0) {
            $errors[] = 'Supervisor has no available thesis slots';
        }

        if ($group->area_of_interest_id && !$supervisor->hasAreaOfInterest($group->area_of_interest_id)) {
            $errors[] = 'Supervisor does not have expertise in the required area of interest';
        }

        if ($group->hasSupervisor()) {
            $errors[] = 'Group already has a supervisor assigned';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
