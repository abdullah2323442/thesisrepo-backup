<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $supervisor = SupervisorModel::query()
            ->where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->first();

        // Get groups where user is main supervisor, co-supervisor, or panel member
        $groups = Group::with(['students', 'areaOfInterest', 'supervisor', 'coSupervisor', 'panelMembers'])
            ->when($supervisor, function($q) use ($supervisor) {
                $q->where(function($query) use ($supervisor) {
                    $query->where('supervisor_id', $supervisor->id)
                          ->orWhere('co_supervisor_id', $supervisor->id)
                          ->orWhereHas('panelMembers', function($panelQuery) use ($supervisor) {
                              $panelQuery->where('supervisor_id', $supervisor->id);
                          });
                });
            })
            ->orderBy('batch_number', 'desc')
            ->orderBy('name')
            ->paginate(12);

        return view('supervisor.groups.index', compact('supervisor', 'groups'));
    }

    /**
     * Toggle co-supervisor meeting management permission
     */
    public function toggleCoSupervisorMeetingPermission(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'allow_meetings' => 'required|boolean'
        ]);

        try {
            $user = Auth::user();
            $supervisor = SupervisorModel::query()
                ->where('email', $user->email)
                ->orWhere('fullname', $user->name)
                ->first();

            if (!$supervisor) {
                return response()->json(['error' => 'Supervisor not found'], 404);
            }

            $group = Group::findOrFail($request->group_id);

            // Check if current user is the main supervisor
            if ($group->supervisor_id !== $supervisor->id) {
                return response()->json(['error' => 'Only the main supervisor can manage meeting permissions'], 403);
            }

            // Check if group has a co-supervisor
            if (!$group->hasCoSupervisor()) {
                return response()->json(['error' => 'This group does not have a co-supervisor'], 400);
            }

            // Update the permission
            $group->update([
                'co_supervisor_can_manage_meetings' => $request->allow_meetings
            ]);

            $message = $request->allow_meetings 
                ? 'Co-supervisor can now manage meetings for this group'
                : 'Co-supervisor meeting management disabled for this group';

            return response()->json([
                'success' => true,
                'message' => $message,
                'can_manage_meetings' => $group->co_supervisor_can_manage_meetings
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update permission: ' . $e->getMessage()], 500);
        }
    }
}
