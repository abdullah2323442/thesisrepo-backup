<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Services\SupervisorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorAssignmentController extends Controller
{
    private SupervisorAssignmentService $assignmentService;

    public function __construct(SupervisorAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display the supervisor assignment management page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get groups for this advisor with optional batch filtering
        $groupsQuery = Group::with(['areaOfInterest', 'supervisor', 'students'])
            ->where('advisor_id', $user->id)
            ->whereHas('students'); // Only show groups that have students
            
        // Apply batch filter if provided
        if ($request->has('batch') && $request->batch !== '') {
            $groupsQuery->where('batch_number', $request->batch);
        }
        
        $groups = $groupsQuery->get()->sortBy(function ($group) {
            // Extract number from group name for proper sorting (Group 1, Group 2, ..., Group 10)
            if (preg_match('/(\d+)/', $group->name, $matches)) {
                return (int) $matches[1];
            }
            return 9999; // Put groups without numbers at the end
        });

        // Get assignment statistics
        $stats = [
            'total_groups' => $groups->count(),
            'assigned_groups' => $groups->where('supervisor_id', '!=', null)->count(),
            'unassigned_groups' => $groups->where('supervisor_id', null)->count(),
            'manual_assignments' => $groups->where('is_manual_assignment', true)->count(),
            'lottery_eligible' => $groups->where('supervisor_id', null)
                                      ->where('is_manual_assignment', false)
                                      ->where('area_of_interest_id', '!=', null)
                                      ->count(),
        ];

        // Get supervisors with their current load
        $supervisors = Supervisor::with(['areasOfInterest', 'groups'])
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($supervisor) {
                // Use normalized, computed rank_priority from the model to avoid string mismatches
                return sprintf('%02d-%s', $supervisor->rank_priority, mb_strtolower($supervisor->fullname ?? ''));
            });

        // Get areas of interest
        $areasOfInterest = AreaOfInterest::where('is_active', true)->get();
        
        // Get available batches for filtering
        $availableBatches = Group::where('advisor_id', $user->id)
            ->select('batch_number')
            ->distinct()
            ->orderBy('batch_number')
            ->pluck('batch_number');

        return view('advisor.supervisor-assignment.index', compact(
            'groups',
            'supervisors', 
            'areasOfInterest',
            'stats',
            'availableBatches'
        ));
    }

    /**
     * Manually assign a supervisor to a group
     */
    public function assignManual(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'supervisor_id' => 'required|exists:supervisors,id',
        ]);

        $user = auth()->user();
        $group = Group::where('id', $request->group_id)
                     ->where('advisor_id', $user->id)
                     ->firstOrFail();

        $supervisor = Supervisor::findOrFail($request->supervisor_id);

        // Check if supervisor has available slots
        if (!$supervisor->canTakeThesis()) {
            return redirect()->back()
                ->with('error', "Supervisor {$supervisor->fullname} has no available thesis slots.");
        }

        // Check if supervisor has matching area of interest
        if ($group->area_of_interest_id && !$supervisor->hasAreaOfInterest($group->area_of_interest_id)) {
            return redirect()->back()
                ->with('error', "Supervisor {$supervisor->fullname} does not have expertise in the selected area of interest.");
        }

        // Assign supervisor manually
        $group->update([
            'supervisor_id' => $supervisor->id,
            'is_manual_assignment' => true,
            'assigned_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Supervisor {$supervisor->fullname} has been manually assigned to {$group->name}.");
    }

    /**
     * Remove supervisor assignment from a group
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $user = auth()->user();
        $group = Group::where('id', $request->group_id)
                     ->where('advisor_id', $user->id)
                     ->firstOrFail();

        $supervisorName = $group->supervisor ? $group->supervisor->fullname : 'Unknown';

        $group->update([
            'supervisor_id' => null,
            'is_manual_assignment' => false,
            'assigned_at' => null,
            'assignment_priority' => null,
        ]);

        return redirect()->back()
            ->with('success', "Supervisor assignment removed from {$group->name}. {$supervisorName} is now available for other assignments.");
    }

    /**
     * Run the lottery assignment process
     */
    public function runLottery(Request $request)
    {
        $user = auth()->user();
        
        $mode = $request->get('mode', 'aoi');
        
        // Get lottery eligible groups for this advisor based on mode
        if ($mode === 'ranking') {
            // Ignore AOI; include all groups with students, not manually assigned and unassigned
            $eligibleGroupsQuery = Group::query()
                ->whereNull('supervisor_id')
                ->where('is_manual_assignment', false)
                ->where('advisor_id', $user->id)
                ->whereHas('students') // Only groups with students
                ->with(['areaOfInterest']);
        } else {
            // Default AOI-based eligibility
            $eligibleGroupsQuery = Group::lotteryEligible()
                ->where('advisor_id', $user->id)
                ->whereHas('students') // Only groups with students
                ->with(['areaOfInterest']);
        }
            
        // Apply batch filter if provided
        if ($request->has('batch') && $request->batch !== '') {
            $eligibleGroupsQuery->where('batch_number', $request->batch);
        }
        
        $eligibleGroups = $eligibleGroupsQuery->get();

        if ($eligibleGroups->isEmpty()) {
            // Debug information
            $allGroups = Group::where('advisor_id', $user->id)->count();
            $groupsWithStudents = Group::where('advisor_id', $user->id)->whereHas('students')->count();
            $groupsWithAreas = Group::where('advisor_id', $user->id)->whereHas('students')->whereNotNull('area_of_interest_id')->count();
            
            if ($mode === 'ranking') {
                $message = "No groups are eligible for lottery assignment (Ranking mode). ";
                $message .= "Debug info - User ID: {$user->id}, ";
                $message .= "Total groups: {$allGroups}, ";
                $message .= "Groups with students: {$groupsWithStudents}. ";
                $message .= "Make sure groups are not manually assigned and have students.";
            } else {
                $message = "No groups are eligible for lottery assignment. ";
                $message .= "Debug info - User ID: {$user->id}, ";
                $message .= "Total groups: {$allGroups}, ";
                $message .= "Groups with students: {$groupsWithStudents}, ";
                $message .= "Groups with areas: {$groupsWithAreas}. ";
                $message .= "Make sure groups have areas of interest assigned and are not manually assigned.";
            }
            
            return redirect()->back()->with('error', $message);
        }

        try {
            DB::beginTransaction();

            $result = $this->assignmentService->runLotteryAssignment($eligibleGroups, $mode);

            DB::commit();

            $message = "Lottery assignment completed! ";
            $message .= "Assigned: {$result['assigned']}, ";
            $message .= "Unassigned: {$result['unassigned']}, ";
            $message .= "No matching supervisors: {$result['no_matches']}";

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Lottery assignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Get available supervisors for a specific area of interest
     */
    public function getAvailableSupervisors(Request $request)
    {
        try {
            \Log::info('getAvailableSupervisors called', [
                'area_of_interest_id' => $request->get('area_of_interest_id'),
                'user_id' => auth()->id()
            ]);

            $request->validate([
                'area_of_interest_id' => 'required|exists:area_of_interests,id',
            ]);

            $areaOfInterestId = $request->area_of_interest_id;

            // First get all active supervisors
            $allActiveSupervisors = Supervisor::where('is_active', true)->get();
            \Log::info('Found active supervisors', ['count' => $allActiveSupervisors->count()]);

            // Filter supervisors with available slots
            $supervisorsWithSlots = $allActiveSupervisors->filter(function ($supervisor) {
                return $supervisor->available_slots > 0;
            });
            \Log::info('Supervisors with available slots', ['count' => $supervisorsWithSlots->count()]);

            // Filter by area of interest
            $matchingSupervisors = $supervisorsWithSlots->filter(function ($supervisor) use ($areaOfInterestId) {
                $hasArea = $supervisor->areasOfInterest()
                    ->where('area_of_interests.id', $areaOfInterestId)
                    ->exists();
                return $hasArea;
            });
            \Log::info('Supervisors matching area of interest', ['count' => $matchingSupervisors->count()]);

            // Sort by rank priority and format response
            $supervisors = $matchingSupervisors->sortBy('rank_priority')->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'fullname' => $supervisor->fullname,
                    'designation' => $supervisor->designation,
                    'available_slots' => $supervisor->available_slots,
                    'rank_priority' => $supervisor->rank_priority,
                ];
            })->values();

            \Log::info('Final supervisor list', ['count' => $supervisors->count()]);

            return response()->json($supervisors);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in getAvailableSupervisors', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Invalid area of interest ID',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableSupervisors', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'input' => $request->all()
            ]);
            return response()->json([
                'error' => 'Failed to load supervisors',
                'message' => $e->getMessage(),
                'details' => 'Check server logs for more information'
            ], 500);
        }
    }

    /**
     * Preview lottery assignment without saving
     */
    public function previewLottery(Request $request)
    {
        $user = auth()->user();
        
        $mode = $request->get('mode', 'aoi');
        
        if ($mode === 'ranking') {
            $eligibleGroupsQuery = Group::query()
                ->whereNull('supervisor_id')
                ->where('is_manual_assignment', false)
                ->where('advisor_id', $user->id)
                ->whereHas('students') // Only groups with students
                ->with(['areaOfInterest']);
        } else {
            $eligibleGroupsQuery = Group::lotteryEligible()
                ->where('advisor_id', $user->id)
                ->whereHas('students') // Only groups with students
                ->with(['areaOfInterest']);
        }
            
        // Apply batch filter if provided
        if ($request->has('batch') && $request->batch !== '') {
            $eligibleGroupsQuery->where('batch_number', $request->batch);
        }
        
        $eligibleGroups = $eligibleGroupsQuery->get();

        if ($eligibleGroups->isEmpty()) {
            // Debug information
            $allGroups = Group::where('advisor_id', $user->id)->count();
            $groupsWithStudents = Group::where('advisor_id', $user->id)->whereHas('students')->count();
            $groupsWithAreas = Group::where('advisor_id', $user->id)->whereHas('students')->whereNotNull('area_of_interest_id')->count();
            
            if ($mode === 'ranking') {
                $message = "No groups are eligible for lottery assignment (Ranking mode). ";
                $message .= "Debug info - User ID: {$user->id}, ";
                $message .= "Total groups: {$allGroups}, ";
                $message .= "Groups with students: {$groupsWithStudents}. ";
                $message .= "Make sure groups are not manually assigned and have students.";
            } else {
                $message = "No groups are eligible for lottery assignment. ";
                $message .= "Debug info - User ID: {$user->id}, ";
                $message .= "Total groups: {$allGroups}, ";
                $message .= "Groups with students: {$groupsWithStudents}, ";
                $message .= "Groups with areas: {$groupsWithAreas}.";
            }
            
            return response()->json([
                'success' => false,
                'message' => $message
            ]);
        }

        $preview = $this->assignmentService->previewLotteryAssignment($eligibleGroups, $mode);

        return response()->json([
            'success' => true,
            'preview' => $preview
        ]);
    }

    /**
     * Bulk unassign all supervisors from this advisor's groups (optional batch filter)
     */
    public function unassignAll(Request $request)
    {
        $user = auth()->user();

        // Build query for this advisor's groups with assigned supervisors
        $query = \App\Models\Group::where('advisor_id', $user->id)
            ->whereNotNull('supervisor_id');

        // Optional: batch filter
        if ($request->has('batch') && $request->batch !== '') {
            $query->where('batch_number', $request->batch);
        }

        $affected = $query->count();

        if ($affected === 0) {
            return redirect()->back()->with('success', 'No supervisor assignments to unassign.');
        }

        try {
            DB::beginTransaction();

            $query->update([
                'supervisor_id' => null,
                'is_manual_assignment' => false,
                'assigned_at' => null,
                'assignment_priority' => null,
            ]);

            DB::commit();

            $suffix = ($request->has('batch') && $request->batch !== '') ? " in Batch {$request->batch}" : '';
            return redirect()->back()->with('success', "Unassigned supervisors from {$affected} groups{$suffix}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to unassign all supervisors: ' . $e->getMessage());
        }
    }
}
