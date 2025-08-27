<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AdminCreatedGroup;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCreatedGroupController extends Controller
{
    /**
     * Display admin-created groups for the current advisor
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $selectedBatch = $request->get('batch');
            
            // Get available batches where this advisor has admin-created groups
            $availableBatches = AdminCreatedGroup::forAdvisor($user->id)
                ->select('batch_number')
                ->distinct()
                ->orderBy('batch_number', 'desc')
                ->pluck('batch_number');
            
            $adminGroups = collect();
            
            if ($selectedBatch) {
                // Get admin-created groups for this advisor in the selected batch
                $adminGroups = AdminCreatedGroup::forAdvisor($user->id)
                    ->forBatch($selectedBatch)
                    ->orderBy('name')
                    ->get();
            }
            
            return view('advisor.admin-groups.index', compact(
                'availableBatches',
                'selectedBatch',
                'adminGroups'
            ));
            
        } catch (\Exception $e) {
            Log::error('Advisor admin groups page failed', [
                'advisor_id' => auth()->id(),
                'error' => $e->getMessage(),
                'batch' => $request->get('batch')
            ]);
            
            return redirect()->back()->with('error', 'Failed to load admin-created groups: ' . $e->getMessage());
        }
    }

    /**
     * Show details of a specific admin-created group
     */
    public function show(Request $request, int $groupId)
    {
        try {
            $user = auth()->user();
            
            // Get the admin-created group (read-only view)
            $adminGroup = AdminCreatedGroup::where('id', $groupId)
                ->forAdvisor($user->id)
                ->firstOrFail();
            
            // Get the actual group for additional details if needed
            $actualGroup = Group::with(['students', 'areasOfInterest', 'supervisor', 'createdByAdmin'])
                ->findOrFail($groupId);
            
            return view('advisor.admin-groups.show', compact('adminGroup', 'actualGroup'));
            
        } catch (\Exception $e) {
            Log::error('Advisor admin group details failed', [
                'advisor_id' => auth()->id(),
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to load group details: ' . $e->getMessage());
        }
    }

    /**
     * Get summary statistics for admin-created groups
     */
    public function getSummary(Request $request)
    {
        try {
            $user = auth()->user();
            $selectedBatch = $request->get('batch');
            
            $query = AdminCreatedGroup::forAdvisor($user->id);
            
            if ($selectedBatch) {
                $query->forBatch($selectedBatch);
            }
            
            $groups = $query->get();
            
            $summary = [
                'total_groups' => $groups->count(),
                'groups_with_students' => $groups->where('student_count', '>', 0)->count(),
                'groups_with_areas' => $groups->whereNotNull('area_of_interest_id')->count(),
                'groups_with_supervisors' => $groups->whereNotNull('supervisor_id')->count(),
                'total_students' => $groups->sum('student_count'),
                'empty_groups' => $groups->where('student_count', 0)->count(),
                'complete_groups' => $groups->whereNotNull('supervisor_id')->count(),
            ];
            
            return response()->json($summary);
            
        } catch (\Exception $e) {
            Log::error('Advisor admin groups summary failed', [
                'advisor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to load summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}