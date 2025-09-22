<?php

namespace App\Http\Controllers\CoSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Report;
use App\Models\StudentReportSubmission;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        // Get groups where user is co-supervisor
        $groups = Group::where('co_supervisor_id', $supervisor->id)
            ->with(['students', 'supervisor', 'advisor'])
            ->get();

        // Get reports for co-supervised groups
        $reports = Report::whereIn('group_id', $groups->pluck('id'))
            ->with(['group', 'group.students', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('co-supervisor.reports.index', compact('supervisor', 'reports', 'groups'));
    }

    public function show(Report $report)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        $group = $report->group;

        // Verify co-supervisor has access to this report
        if ($group->co_supervisor_id !== $supervisor->id) {
            return redirect()->route('co-supervisor.reports.index')
                ->with('error', 'You do not have access to this report.');
        }

        $report->load([
            'group.students',
            'group.supervisor',
            'group.advisor',
            'submissions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }
        ]);

        return view('co-supervisor.reports.show', compact('report', 'supervisor'));
    }

    /**
     * Co-supervisors cannot create reports - only main supervisors can
     */
    public function create()
    {
        return redirect()->route('co-supervisor.reports.index')
            ->with('error', 'Only main supervisors can create reports. Please contact the main supervisor for this group.');
    }

    /**
     * Co-supervisors cannot edit reports - only main supervisors can
     */
    public function edit(Report $report)
    {
        return redirect()->route('co-supervisor.reports.show', $report)
            ->with('error', 'Only main supervisors can edit reports. You can provide feedback through comments.');
    }

    /**
     * Co-supervisors cannot delete reports - only main supervisors can
     */
    public function destroy(Report $report)
    {
        return redirect()->route('co-supervisor.reports.index')
            ->with('error', 'Only main supervisors can delete reports.');
    }

    /**
     * Co-supervisors cannot finalize reports - only main supervisors can approve
     */
    public function showFinalize(Report $report)
    {
        return redirect()->route('co-supervisor.reports.show', $report)
            ->with('error', 'Only main supervisors can approve final projects. You can provide feedback through comments.');
    }

    /**
     * Co-supervisors can mark reports as under review
     */
    public function markUnderReview(Request $request, Report $report)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return response()->json(['error' => 'Supervisor not found'], 404);
        }

        $group = $report->group;

        // Verify co-supervisor has access to this report
        if ($group->co_supervisor_id !== $supervisor->id) {
            return response()->json(['error' => 'You do not have access to this report'], 403);
        }

        try {
            $report->update([
                'status' => 'under_review',
                'reviewed_at' => now(),
                'reviewed_by' => $supervisor->id,
                'reviewed_by_type' => 'co_supervisor'
            ]);

            Log::info('Co-supervisor marked report under review', [
                'report_id' => $report->id,
                'group_id' => $group->id,
                'co_supervisor_id' => $supervisor->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report marked as under review successfully.',
                'status' => $report->status
            ]);

        } catch (\Exception $e) {
            Log::error('Co-supervisor failed to mark report under review', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'co_supervisor_id' => $supervisor->id
            ]);

            return response()->json(['error' => 'Failed to update report status'], 500);
        }
    }

    /**
     * View report submission (same as supervisor)
     */
    public function viewSubmission(Report $report, StudentReportSubmission $submission)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        $group = $report->group;

        // Verify co-supervisor has access to this report
        if ($group->co_supervisor_id !== $supervisor->id) {
            return redirect()->route('co-supervisor.reports.index')
                ->with('error', 'You do not have access to this report.');
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($submission->file_path)) {
            return back()->with('error', 'File not found.');
        }

        $file = Storage::disk('public')->get($submission->file_path);
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $submission->original_filename . '"');
    }

    /**
     * Download report submission (same as supervisor)
     */
    public function downloadSubmission(Report $report, StudentReportSubmission $submission)
    {
        $user = Auth::user();
        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('co-supervisor.dashboard')
                ->with('error', 'You are not registered as a co-supervisor in the system.');
        }

        $group = $report->group;

        // Verify co-supervisor has access to this report
        if ($group->co_supervisor_id !== $supervisor->id) {
            return redirect()->route('co-supervisor.reports.index')
                ->with('error', 'You do not have access to this report.');
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        Log::info('Co-supervisor downloading student submission', [
            'supervisor_id' => $supervisor->id,
            'report_id' => $report->id,
            'submission_id' => $submission->id,
            'student_id' => $submission->student_id,
            'filename' => $submission->original_filename,
        ]);

        return Storage::disk('public')->download($submission->file_path, $submission->original_filename);
    }
}