<?php

namespace App\Http\Controllers\CoSupervisor;

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

        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        // Get groups where user is co-supervisor only
        $groups = Group::with(['students', 'areaOfInterest', 'areasOfInterest', 'supervisor', 'advisor', 'coSupervisor'])
            ->where('co_supervisor_id', $supervisor->id)
            ->orderBy('batch_number', 'desc')
            ->orderBy('name')
            ->paginate(12);

        return view('co-supervisor.groups.index', compact('supervisor', 'groups'));
    }

    /**
     * Toggle co-supervisor meeting management permission
     * Note: Co-supervisors cannot change their own permissions
     */
    public function toggleCoSupervisorMeetingPermission(Request $request)
    {
        return response()->json([
            'error' => 'Co-supervisors cannot modify their own meeting permissions. Please contact the main supervisor.'
        ], 403);
    }
}