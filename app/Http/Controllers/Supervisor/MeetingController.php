<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Facades\Pdf;

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

    /**
     * Download meetings PDF report for a specific group
     */
    public function downloadGroupMeetingsPdf(Group $group)
    {
        try {
            $supervisor = $this->currentSupervisor();
            
            // Verify supervisor has access to this group
            if (!$supervisor || $group->supervisor_id !== $supervisor->id) {
                abort(403, 'Unauthorized');
            }

            // Load group with all related information
            $group->load(['students', 'matchedAreaOfInterest', 'supervisor', 'advisor']);

            // Get all meetings for the group
            $meetings = Meeting::with(['attendances.groupStudent'])
                ->where('group_id', $group->id)
                ->orderBy('meeting_date', 'asc')
                ->get();

            // Get all group members for attendance tracking
            $groupMembers = $group->students->map(function ($student) {
                return [
                    'student_id' => $student->student_id,
                    'name' => $student->student_name,
                    'email' => $student->student_email,
                ];
            });

            // Prepare data for PDF
            $pdfData = [
                'universityName' => 'Premier University Chattogram',
                'departmentName' => 'Department of Computer Science & Engineering',
                'logoPath' => public_path('Picture1.png'),
                'supervisor' => [
                    'id' => $supervisor->id,
                    'name' => $supervisor->fullname,
                    'email' => $supervisor->email,
                    'designation' => $supervisor->designation,
                    'department' => $supervisor->department,
                ],
                'areaOfInterest' => $group->matchedAreaOfInterest ? [
                    'id' => $group->matchedAreaOfInterest->id,
                    'name' => $group->matchedAreaOfInterest->name,
                    'description' => $group->matchedAreaOfInterest->description
                ] : null,
                'studentIds' => $groupMembers->pluck('student_id')->implode(', '),
                'groupName' => $group->name,
                'meetings' => $meetings,
                'groupMembers' => $groupMembers,
                'generatedDate' => now()->format('F d, Y')
            ];

            // Generate PDF
            $pdf = Pdf::view('student.meetings-pdf', $pdfData)
                ->format('a4')
                ->margins(15, 15, 15, 15);

            $filename = 'meetings_report_' . str_replace([' ', '/'], '_', $group->name) . '_' . now()->format('Y_m_d') . '.pdf';

            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Failed to generate meetings PDF for supervisor:', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisor->id ?? null,
                'group_id' => $group->id ?? null
            ]);
            
            return redirect()->route('supervisor.meetings.index')->with('error', 'Failed to generate PDF report. Please try again or contact support.');
        }
    }
}
