<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\User;
use App\Models\Supervisor;
use App\Notifications\NewReportAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReportController extends Controller
{
    /**
     * Display a listing of reports
     */
    public function index()
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Get all groups supervised by this supervisor
        $groups = Group::where('supervisor_id', $supervisor->id)
            ->with(['reports' => function ($query) {
                $query->latest()->with('comments.teacher');
            }])
            ->get();

        return view('supervisor.reports.index', compact('groups'));
    }

    /**
     * Show the form for creating a new report
     */
    public function create()
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Get all groups supervised by this supervisor
        $groups = Group::where('supervisor_id', $supervisor->id)
            ->orderBy('name')
            ->get();

        if ($groups->isEmpty()) {
            return redirect()->route('supervisor.reports.index')
                ->with('error', 'You have no groups assigned to create reports for.');
        }

        return view('supervisor.reports.create', compact('groups'));
    }

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Validate the request
        $validated = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'type' => ['required', 'in:general,final'],
            'extra_input' => ['nullable', 'string', 'max:1000'],
            'supervisor_message' => ['nullable', 'string', 'max:2000'],
        ]);

        // Verify the group belongs to this supervisor and load relationships
        $group = Group::where('id', $validated['group_id'])
            ->where('supervisor_id', $supervisor->id)
            ->with('areasOfInterest')
            ->first();

        if (!$group) {
            return back()->with('error', 'You are not authorized to create reports for this group.');
        }

        // Load supervisor's areas of interest
        $supervisor->load('areasOfInterest');

        DB::beginTransaction();
        try {
            // Find the matched area of interest between group and supervisor
            $matchedAreaOfInterest = null;
            if ($group->areasOfInterest->isNotEmpty()) {
                // Get supervisor's areas of interest
                $supervisorAreas = $supervisor->areasOfInterest->pluck('id');
                
                // Find the first matching area of interest
                $matchedAreaOfInterest = $group->areasOfInterest
                    ->whereIn('id', $supervisorAreas)
                    ->first();
            }

            // Create the report
            $report = Report::create([
                'group_id' => $validated['group_id'],
                'area_of_interest_id' => $matchedAreaOfInterest ? $matchedAreaOfInterest->id : null,
                'type' => $validated['type'],
                'project_title' => null,
                'abstract_md' => null,
                'extra_input' => $validated['extra_input'] ?? null,
                'supervisor_message' => $validated['supervisor_message'] ?? null,
                'keywords' => null,
                'created_by' => auth()->id(),
                'status' => Report::STATUS_DRAFT,
            ]);

            // Get all students in the group
            $groupStudents = GroupStudent::where('group_id', $group->id)->get();
            
            Log::info('Group students found', [
                'group_id' => $group->id,
                'student_count' => $groupStudents->count(),
                'student_ids' => $groupStudents->pluck('student_id')->toArray()
            ]);

            // Find users with matching roll numbers or student_id
            $students = collect();
            foreach ($groupStudents as $groupStudent) {
                // Try to find user by roll or student_id
                $user = User::where('roll', $groupStudent->student_id)
                    ->orWhere('student_id', $groupStudent->student_id)
                    ->first();
                    
                if ($user) {
                    $students->push($user);
                    Log::info('Found user for notification', [
                        'student_id' => $groupStudent->student_id,
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ]);
                } else {
                    Log::warning('No user found for student', [
                        'student_id' => $groupStudent->student_id
                    ]);
                }
            }

            // Send notifications to students
            if ($students->isNotEmpty()) {
                // Load relationships for the report
                $report->load(['group', 'creator']);
                
                foreach ($students as $student) {
                    try {
                        $student->notify(new NewReportAssigned($report));
                        Log::info('Notification sent to student', [
                            'user_id' => $student->id,
                            'report_id' => $report->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send notification to student', [
                            'user_id' => $student->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                Log::warning('No students found to notify for group', [
                    'group_id' => $group->id,
                    'group_name' => $group->name
                ]);
            }

            DB::commit();

            Log::info('Report created successfully', [
                'report_id' => $report->id,
                'group_id' => $group->id,
                'type' => $report->type,
                'supervisor_id' => $supervisor->id,
                'notified_students' => $students->count(),
            ]);

            return redirect()->route('supervisor.reports.show', $report)
                ->with('success', 'Report created successfully and students have been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create report', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisor->id,
                'group_id' => $validated['group_id'],
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create report. Please try again.');
        }
    }

    /**
     * Display the specified report
     */
    public function show(Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Load relationships
        $report->load(['group.students', 'comments.teacher', 'creator', 'submissions.student']);

        return view('supervisor.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified report
     */
    public function edit(Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Get all groups supervised by this supervisor
        $groups = Group::where('supervisor_id', $supervisor->id)
            ->orderBy('name')
            ->get();

        // Load the report with its group
        $report->load('group');

        return view('supervisor.reports.edit', compact('report', 'groups'));
    }

    /**
     * Update the specified report
     */
    public function update(Request $request, Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Validate the request
        $validated = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'type' => ['required', 'in:general,final'],
            'extra_input' => ['nullable', 'string', 'max:1000'],
            'supervisor_message' => ['nullable', 'string', 'max:2000'],
        ]);

        // Verify the new group belongs to this supervisor
        $group = Group::where('id', $validated['group_id'])
            ->where('supervisor_id', $supervisor->id)
            ->first();

        if (!$group) {
            return back()->with('error', 'You are not authorized to assign reports to this group.');
        }

        DB::beginTransaction();
        try {
            // Update the report (only basic fields, not project details)
            $report->update([
                'group_id' => $validated['group_id'],
                'type' => $validated['type'],
                'extra_input' => $validated['extra_input'] ?? null,
                'supervisor_message' => $validated['supervisor_message'] ?? null,
            ]);

            DB::commit();

            Log::info('Report updated successfully', [
                'report_id' => $report->id,
                'group_id' => $group->id,
                'type' => $report->type,
                'supervisor_id' => $supervisor->id,
            ]);

            return redirect()->route('supervisor.reports.show', $report)
                ->with('success', 'Report updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update report. Please try again.');
        }
    }

    /**
     * Remove the specified report from storage
     */
    public function destroy(Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        DB::beginTransaction();
        try {
            $reportId = $report->id;
            $groupName = $report->group->name;
            
            // Delete the report (this will also delete related comments due to cascade)
            $report->delete();

            DB::commit();

            Log::info('Report deleted successfully', [
                'report_id' => $reportId,
                'group_name' => $groupName,
                'supervisor_id' => $supervisor->id,
            ]);

            return redirect()->route('supervisor.reports.index')
                ->with('success', 'Report deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return back()->with('error', 'Failed to delete report. Please try again.');
        }
    }

    /**
     * Show the finalize form for the report
     */
    public function showFinalize(Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Check if report can be approved
        if (!$report->canBeApproved()) {
            return redirect()->route('supervisor.reports.show', $report)
                ->with('error', 'This report cannot be approved. It must be a final report with student submissions.');
        }

        // Load relationships
        $report->load(['group.students', 'submissions.student']);

        return view('supervisor.reports.finalize', compact('report'));
    }

    /**
     * Finalize and approve the report
     */
    public function finalize(Request $request, Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Check if report can be approved
        if (!$report->canBeApproved()) {
            return redirect()->route('supervisor.reports.show', $report)
                ->with('error', 'This report cannot be approved. It must be a final report with student submissions.');
        }

        // Validate the request
        $validated = $request->validate([
            'project_title' => ['required', 'string', 'max:255'],
            'abstract_md' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            // Process keywords if provided
            if (!empty($validated['keywords'])) {
                $keywords = array_map('trim', explode(',', $validated['keywords']));
                $validated['keywords'] = json_encode($keywords);
            } else {
                $validated['keywords'] = null;
            }

            // Update the report with final details and approve it
            $report->update([
                'project_title' => $validated['project_title'],
                'abstract_md' => $validated['abstract_md'],
                'keywords' => $validated['keywords'],
                'status' => Report::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => auth()->user()->id,
            ]);

            DB::commit();

            Log::info('Report approved successfully', [
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
                'approved_at' => $report->approved_at,
            ]);

            return redirect()->route('supervisor.reports.show', $report)
                ->with('success', 'Report has been approved successfully and is now published.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return back()->withInput()
                ->with('error', 'Failed to approve report. Please try again.');
        }
    }

    /**
     * Mark report as under review
     */
    public function markUnderReview(Report $report)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Supervisor profile not found.');
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Check if report has submissions
        if (!$report->hasSubmissions()) {
            return redirect()->route('supervisor.reports.show', $report)
                ->with('error', 'Cannot mark report as under review without student submissions.');
        }

        DB::beginTransaction();
        try {
            $report->update([
                'status' => Report::STATUS_UNDER_REVIEW,
            ]);

            DB::commit();

            Log::info('Report marked as under review', [
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return redirect()->route('supervisor.reports.show', $report)
                ->with('success', 'Report has been marked as under review.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark report as under review', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return back()->with('error', 'Failed to update report status. Please try again.');
        }
    }
}