<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    protected function currentSupervisor()
    {
        $user = Auth::user();
        return SupervisorModel::query()
            ->where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->first();
    }

    public function index(Request $request)
    {
        $supervisor = $this->currentSupervisor();

        $groups = Group::with(['students'])
            ->when($supervisor, fn($q) => $q->where('supervisor_id', $supervisor->id))
            ->orderBy('batch_number', 'desc')
            ->orderBy('name')
            ->get();

        $meetings = Meeting::with(['group', 'attendances'])
            ->when($groups->isNotEmpty(), fn($q) => $q->whereIn('group_id', $groups->pluck('id')))
            ->when($request->group_id, fn($q) => $q->where('group_id', $request->group_id))
            ->when($request->date_from, fn($q) => $q->where('meeting_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('meeting_date', '<=', $request->date_to))
            ->orderBy('meeting_date', 'desc')
            ->paginate(12);

        return view('supervisor.meetings.index', compact('supervisor', 'groups', 'meetings'));
    }

    public function store(Request $request)
    {
        $supervisor = $this->currentSupervisor();

        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'meeting_date' => ['required', 'date'],
            'discussed_topics' => ['nullable', 'string'],
            'outcomes' => ['nullable', 'string'],
            'attendance' => ['required', 'array'],
            'attendance.*.group_student_id' => ['required', 'integer', 'exists:group_students,id'],
            'attendance.*.present' => ['required', 'boolean'],
        ]);

        $group = Group::with('students')->findOrFail($data['group_id']);
        if (!$supervisor || $group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized');
        }

        $meeting = Meeting::create([
            'group_id' => $group->id,
            'meeting_date' => $data['meeting_date'],
            'discussed_topics' => $data['discussed_topics'] ?? null,
            'outcomes' => $data['outcomes'] ?? null,
        ]);

        foreach ($data['attendance'] as $row) {
            MeetingAttendance::create([
                'meeting_id' => $meeting->id,
                'group_student_id' => $row['group_student_id'],
                'present' => (bool)$row['present'],
            ]);
        }

        return redirect()->route('supervisor.meetings.index')->with('success', 'Meeting saved.');
    }

    public function show(Meeting $meeting)
    {
        $supervisor = $this->currentSupervisor();
        $meeting->load(['group.students', 'attendances.groupStudent']);
        
        if (!$supervisor || $meeting->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized');
        }

        return view('supervisor.meetings.show', compact('meeting', 'supervisor'));
    }

    public function edit(Meeting $meeting)
    {
        $supervisor = $this->currentSupervisor();
        $meeting->load(['group.students', 'attendances.groupStudent']);
        
        if (!$supervisor || $meeting->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized');
        }

        return view('supervisor.meetings.edit', compact('meeting', 'supervisor'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $supervisor = $this->currentSupervisor();
        
        if (!$supervisor || $meeting->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'meeting_date' => ['required', 'date'],
            'discussed_topics' => ['nullable', 'string'],
            'outcomes' => ['nullable', 'string'],
            'attendance' => ['required', 'array'],
            'attendance.*.group_student_id' => ['required', 'integer', 'exists:group_students,id'],
            'attendance.*.present' => ['required', 'boolean'],
        ]);

        $meeting->update([
            'meeting_date' => $data['meeting_date'],
            'discussed_topics' => $data['discussed_topics'] ?? null,
            'outcomes' => $data['outcomes'] ?? null,
        ]);

        // Delete existing attendance records and create new ones
        $meeting->attendances()->delete();
        
        foreach ($data['attendance'] as $row) {
            MeetingAttendance::create([
                'meeting_id' => $meeting->id,
                'group_student_id' => $row['group_student_id'],
                'present' => (bool)$row['present'],
            ]);
        }

        return redirect()->route('supervisor.meetings.index')->with('success', 'Meeting updated successfully.');
    }

    public function students(Request $request)
    {
        $supervisor = $this->currentSupervisor();
        $request->validate(['group_id' => ['required', 'integer', 'exists:groups,id']]);
        $group = Group::with('students')->findOrFail($request->group_id);
        if (!$supervisor || $group->supervisor_id !== $supervisor->id) {
            abort(403);
        }
        return response()->json($group->students->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->student_name,
            'student_id' => $s->student_id,
        ]));
    }
}
