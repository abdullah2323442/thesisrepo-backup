<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\StudentReportSubmission;
use App\Models\ReportAnnotationSession;
use App\Models\GroupStudent;
use App\Models\User;
use App\Models\Supervisor;
use App\Notifications\NewReportAnnotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportAnnotationController extends Controller
{
    /**
     * Show the annotation interface for a submission
     */
    public function annotate(Report $report, StudentReportSubmission $submission)
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

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            abort(403, 'Unauthorized access to this submission.');
        }

        // Check if file exists and is a PDF
        if (!Storage::disk('public')->exists($submission->file_path) || !$submission->isPdf()) {
            return back()->with('error', 'PDF file not found or invalid format.');
        }

        // Get existing annotation sessions for this submission
        $annotationSessions = ReportAnnotationSession::where('submission_id', $submission->id)
            ->with('supervisor')
            ->orderBy('version', 'desc')
            ->get();

        // Load relationships
        $report->load('group');
        $submission->load('student');

        return view('supervisor.reports.annotate', compact('report', 'submission', 'annotationSessions'));
    }

    /**
     * Store a new annotation session (without sending notifications)
     */
    public function store(Request $request, Report $report, StudentReportSubmission $submission)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return response()->json(['error' => 'Supervisor profile not found.'], 403);
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            return response()->json(['error' => 'Unauthorized access to this report.'], 403);
        }

        // Verify the submission belongs to this report
        if ($submission->report_id !== $report->id) {
            return response()->json(['error' => 'Unauthorized access to this submission.'], 403);
        }

        // Validate the request (JSON payload from annotation UI)
        $validated = $request->validate([
            'feedback_message' => ['nullable', 'string', 'max:2000'],
            'annotations' => ['required', 'array'],
        ]);

        DB::beginTransaction();
        try {
            // Get the next version number
            $version = ReportAnnotationSession::getNextVersionForSubmission($submission->id);

            // Create the annotation session (without notifications)
            $annotationSession = ReportAnnotationSession::create([
                'report_id' => $report->id,
                'submission_id' => $submission->id,
                'supervisor_id' => auth()->id(),
                'version' => $version,
                'message' => $validated['feedback_message'] ?? null,
                'annotations_json' => $validated['annotations'],
                'annotated_file_path' => null,
                'is_sent' => false, // Mark as not sent yet
            ]);

            DB::commit();

            Log::info('Annotation session saved as draft', [
                'annotation_session_id' => $annotationSession->id,
                'report_id' => $report->id,
                'submission_id' => $submission->id,
                'supervisor_id' => $supervisor->id,
                'version' => $version,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Annotations saved as draft. Click "Send Feedback" to notify students.',
                'session_id' => $annotationSession->id,
                'version' => $version,
                'is_sent' => false
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create annotation session', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'submission_id' => $submission->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save annotations. Please try again.'
            ], 500);
        }
    }

    /**
     * Send feedback notification to students
     */
    public function sendFeedback(Request $request, Report $report, StudentReportSubmission $submission, ReportAnnotationSession $annotationSession)
    {
        // Get the supervisor record for the authenticated user
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor) {
            return response()->json(['error' => 'Supervisor profile not found.'], 403);
        }

        // Verify the report belongs to a group supervised by this supervisor
        if ($report->group->supervisor_id !== $supervisor->id) {
            return response()->json(['error' => 'Unauthorized access to this report.'], 403);
        }

        // Verify the annotation session belongs to this supervisor
        if ($annotationSession->supervisor_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized access to this annotation session.'], 403);
        }

        // Check if already sent
        if ($annotationSession->is_sent) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback has already been sent to students.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get all students in the group for notifications
            $groupStudents = GroupStudent::where('group_id', $report->group_id)->get();
            
            Log::info('Finding students for annotation notification', [
                'annotation_session_id' => $annotationSession->id,
                'report_id' => $report->id,
                'group_id' => $report->group_id,
                'student_count' => $groupStudents->count()
            ]);

            // Find users with matching roll numbers or student_id
            $students = collect();
            foreach ($groupStudents as $groupStudent) {
                $user = User::where('roll', $groupStudent->student_id)
                    ->orWhere('student_id', $groupStudent->student_id)
                    ->first();
                    
                if ($user) {
                    $students->push($user);
                } else {
                    Log::warning('No user found for student in annotation notification', [
                        'student_id' => $groupStudent->student_id
                    ]);
                }
            }

            // Send notifications to students
            if ($students->isNotEmpty()) {
                // Load relationships for the annotation session
                $annotationSession->load(['report', 'submission', 'supervisor']);
                
                foreach ($students as $student) {
                    try {
                        $student->notify(new NewReportAnnotation($annotationSession));
                        Log::info('Annotation notification sent to student', [
                            'user_id' => $student->id,
                            'annotation_session_id' => $annotationSession->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send annotation notification to student', [
                            'user_id' => $student->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Mark as sent
            $annotationSession->update([
                'is_sent' => true,
                'sent_at' => now()
            ]);

            DB::commit();

            Log::info('Annotation feedback sent successfully', [
                'annotation_session_id' => $annotationSession->id,
                'notified_students' => $students->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback sent successfully! Students have been notified.',
                'is_sent' => true,
                'sent_at' => $annotationSession->fresh()->sent_at->format('M d, Y h:i A')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to send annotation feedback', [
                'error' => $e->getMessage(),
                'annotation_session_id' => $annotationSession->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send feedback. Please try again.'
            ], 500);
        }
    }

    /**
     * Show annotation history for a submission
     */
    public function history(Report $report, StudentReportSubmission $submission)
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

        return view('supervisor.reports.annotation-history', compact('report', 'submission', 'annotationSessions'));
    }
}