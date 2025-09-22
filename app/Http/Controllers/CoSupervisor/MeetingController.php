<?php

namespace App\Http\Controllers\CoSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MeetingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        // Get groups where user is co-supervisor and has meeting management permission
        $authorizedGroups = Group::where('co_supervisor_id', $supervisor->id)
            ->where('co_supervisor_can_manage_meetings', true)
            ->with(['students', 'supervisor', 'advisor'])
            ->get();

        if ($authorizedGroups->isEmpty()) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You do not have permission to manage meetings for any groups. Please contact the main supervisor.');
        }

        // Get meetings for authorized groups
        $meetings = Meeting::whereIn('group_id', $authorizedGroups->pluck('id'))
            ->with(['group', 'group.students'])
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return view('co-supervisor.meetings.index', compact('supervisor', 'meetings', 'authorizedGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $supervisor = SupervisorModel::where('email', $user->email)->first();

            if (!$supervisor) {
                return response()->json(['error' => 'Supervisor not found'], 404);
            }

            $group = Group::findOrFail($request->group_id);

            // Verify co-supervisor has permission for this group
            if ($group->co_supervisor_id !== $supervisor->id || !$group->co_supervisor_can_manage_meetings) {
                return response()->json(['error' => 'You do not have permission to manage meetings for this group'], 403);
            }

            $meeting = Meeting::create([
                'group_id' => $group->id,
                'title' => $request->title,
                'description' => $request->description,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes,
                'location' => $request->location,
                'created_by' => $supervisor->id,
                'created_by_type' => 'co_supervisor',
            ]);

            Log::info('Co-supervisor created meeting', [
                'meeting_id' => $meeting->id,
                'group_id' => $group->id,
                'co_supervisor_id' => $supervisor->id,
                'title' => $meeting->title
            ]);

            return redirect()->route('co-supervisor.meetings.index')
                ->with('success', 'Meeting scheduled successfully.');

        } catch (\Exception $e) {
            Log::error('Co-supervisor meeting creation failed', [
                'error' => $e->getMessage(),
                'group_id' => $request->group_id,
                'co_supervisor_id' => $supervisor->id ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Failed to schedule meeting: ' . $e->getMessage());
        }
    }

    public function show(Meeting $meeting)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        $group = $meeting->group;

        // Verify co-supervisor has access to this meeting
        if ($group->co_supervisor_id !== $supervisor->id) {
            return redirect()->route('co-supervisor.meetings.index')
                ->with('error', 'You do not have access to this meeting.');
        }

        $meeting->load(['group.students', 'attendances']);

        return view('co-supervisor.meetings.show', compact('meeting', 'supervisor'));
    }

    public function edit(Meeting $meeting)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        $group = $meeting->group;

        // Verify co-supervisor has permission to edit this meeting
        if ($group->co_supervisor_id !== $supervisor->id || !$group->co_supervisor_can_manage_meetings) {
            return redirect()->route('co-supervisor.meetings.index')
                ->with('error', 'You do not have permission to edit this meeting.');
        }

        return view('co-supervisor.meetings.edit', compact('meeting', 'supervisor'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::user();
            $supervisor = SupervisorModel::where('email', $user->email)->first();

            if (!$supervisor) {
                return redirect()->route('co-supervisor.dashboard')
                    ->with('error', 'You are not registered as a co-supervisor in the system.');
            }

            $group = $meeting->group;

            // Verify co-supervisor has permission to update this meeting
            if ($group->co_supervisor_id !== $supervisor->id || !$group->co_supervisor_can_manage_meetings) {
                return redirect()->route('co-supervisor.meetings.index')
                    ->with('error', 'You do not have permission to update this meeting.');
            }

            $meeting->update([
                'title' => $request->title,
                'description' => $request->description,
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes,
                'location' => $request->location,
            ]);

            Log::info('Co-supervisor updated meeting', [
                'meeting_id' => $meeting->id,
                'group_id' => $group->id,
                'co_supervisor_id' => $supervisor->id
            ]);

            return redirect()->route('co-supervisor.meetings.show', $meeting)
                ->with('success', 'Meeting updated successfully.');

        } catch (\Exception $e) {
            Log::error('Co-supervisor meeting update failed', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
                'co_supervisor_id' => $supervisor->id ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update meeting: ' . $e->getMessage());
        }
    }
}