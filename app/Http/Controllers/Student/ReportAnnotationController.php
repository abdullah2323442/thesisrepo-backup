<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\StudentReportSubmission;
use App\Models\ReportAnnotationSession;
use App\Models\GroupStudent;
use Illuminate\Http\Request;

class ReportAnnotationController extends Controller
{
    /**
     * View a specific annotation session (read-only)
     */
    public function view(Report $report, StudentReportSubmission $submission, ReportAnnotationSession $session)
    {
        // Verify the student is part of the group for this report
        $isGroupMember = GroupStudent::where('group_id', $report->group_id)
            ->where(function ($query) {
                $query->where('student_id', auth()->user()->roll)
                      ->orWhere('student_id', auth()->user()->student_id);
            })
            ->exists();

        if (!$isGroupMember) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Verify the annotation session belongs to this submission
        if ($session->submission_id !== $submission->id) {
            abort(403, 'Unauthorized access to this annotation session.');
        }

        // Load relationships
        $report->load('group');
        $submission->load('student');
        $session->load('supervisor');

        return view('student.reports.annotation-view', compact('report', 'submission', 'session'));
    }

    /**
     * Download the annotated PDF
     */
    public function download(Report $report, StudentReportSubmission $submission, ReportAnnotationSession $session)
    {
        // Verify the student is part of the group for this report
        $isGroupMember = GroupStudent::where('group_id', $report->group_id)
            ->where(function ($query) {
                $query->where('student_id', auth()->user()->roll)
                      ->orWhere('student_id', auth()->user()->student_id);
            })
            ->exists();

        if (!$isGroupMember) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Verify the annotation session belongs to this submission
        if ($session->submission_id !== $submission->id) {
            abort(403, 'Unauthorized access to this annotation session.');
        }

        // Check if annotated file exists
        if (!$session->annotated_file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($session->annotated_file_path)) {
            return back()->with('error', 'Annotated file not found.');
        }

        $filename = "annotated_feedback_v{$session->version}_{$submission->original_filename}";
        
        return \Illuminate\Support\Facades\Storage::disk('public')->download($session->annotated_file_path, $filename);
    }

    /**
     * Show annotation history for a submission (student view)
     */
    public function history(Report $report, StudentReportSubmission $submission)
    {
        // Verify the student is part of the group for this report
        $isGroupMember = GroupStudent::where('group_id', $report->group_id)
            ->where(function ($query) {
                $query->where('student_id', auth()->user()->roll)
                      ->orWhere('student_id', auth()->user()->student_id);
            })
            ->exists();

        if (!$isGroupMember) {
            abort(403, 'Unauthorized access to this report.');
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Get annotation sessions for this submission
        $annotationSessions = ReportAnnotationSession::where('submission_id', $submission->id)
            ->with('supervisor')
            ->orderBy('version', 'desc')
            ->get();

        // Load relationships
        $report->load('group');
        $submission->load('student');

        return view('student.reports.annotations.history', compact('report', 'submission', 'annotationSessions'));
    }
}