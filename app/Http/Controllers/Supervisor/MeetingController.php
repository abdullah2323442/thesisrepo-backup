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

        // Get groups where user can manage meetings (main supervisor or co-supervisor with permission)
        $groups = Group::with(['students'])
            ->when($supervisor, function($q) use ($supervisor) {
                $q->where(function($query) use ($supervisor) {
                    $query->where('supervisor_id', $supervisor->id)
                          ->orWhere(function($subQuery) use ($supervisor) {
                              $subQuery->where('co_supervisor_id', $supervisor->id)
                                       ->where('co_supervisor_can_manage_meetings', true);
                          });
                });
            })
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
        if (!$supervisor || !$group->canSupervisorManageMeetings($supervisor->id)) {
            abort(403, 'Unauthorized - You do not have permission to manage meetings for this group');
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
        
        if (!$supervisor || !$meeting->group->canSupervisorManageMeetings($supervisor->id)) {
            abort(403, 'Unauthorized - You do not have permission to manage meetings for this group');
        }

        return view('supervisor.meetings.show', compact('meeting', 'supervisor'));
    }

    public function edit(Meeting $meeting)
    {
        $supervisor = $this->currentSupervisor();
        $meeting->load(['group.students', 'attendances.groupStudent']);
        
        if (!$supervisor || !$meeting->group->canSupervisorManageMeetings($supervisor->id)) {
            abort(403, 'Unauthorized - You do not have permission to manage meetings for this group');
        }

        return view('supervisor.meetings.edit', compact('meeting', 'supervisor'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $supervisor = $this->currentSupervisor();
        
        if (!$supervisor || !$meeting->group->canSupervisorManageMeetings($supervisor->id)) {
            abort(403, 'Unauthorized - You do not have permission to manage meetings for this group');
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
        if (!$supervisor || !$group->canSupervisorManageMeetings($supervisor->id)) {
            abort(403, 'Unauthorized - You do not have permission to manage meetings for this group');
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
            
            Log::info('PDF Download attempt:', [
                'supervisor' => $supervisor ? $supervisor->id : 'null',
                'group_id' => $group->id,
                'group_supervisor_id' => $group->supervisor_id
            ]);
            
            // Verify supervisor has access to this group (allow both main supervisor and co-supervisor)
            if (!$supervisor || !$group->canSupervisorManageMeetings($supervisor->id)) {
                Log::error('Unauthorized PDF access attempt:', [
                    'supervisor_id' => $supervisor->id ?? null,
                    'group_id' => $group->id,
                    'group_supervisor_id' => $group->supervisor_id,
                    'group_co_supervisor_id' => $group->co_supervisor_id ?? null
                ]);
                abort(403, 'Unauthorized - You do not have permission to access this group');
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
                    'designation' => $supervisor->designation ?? 'N/A',
                    'department' => $supervisor->department ?? 'N/A',
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

            Log::info('Generating PDF with data:', [
                'group_name' => $group->name,
                'meetings_count' => $meetings->count(),
                'students_count' => $groupMembers->count()
            ]);

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
                'group_id' => $group->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('supervisor.meetings.index')->with('error', 'Failed to generate PDF report: ' . $e->getMessage());
        }
    }
}
