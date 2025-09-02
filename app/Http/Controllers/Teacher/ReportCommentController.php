<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportComment;
use App\Models\GroupStudent;
use App\Models\User;
use App\Models\Supervisor;
use App\Notifications\NewReportComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReportCommentController extends Controller
{
    /**
     * Store a new comment on a report
     */
    public function store(Request $request, Report $report)
    {
        // Validate the request
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // Check if the teacher is the supervisor of the group
        $supervisor = Supervisor::where('email', auth()->user()->email)->first();
        
        if (!$supervisor || $report->group->supervisor_id !== $supervisor->id) {
            return back()->with('error', 'You are not authorized to comment on this report.');
        }

        DB::beginTransaction();
        try {
            // Create the comment
            $comment = ReportComment::create([
                'report_id' => $report->id,
                'teacher_id' => auth()->id(),
                'body' => $validated['body'],
            ]);

            // Get all students in the group
            $groupStudents = GroupStudent::where('group_id', $report->group_id)->get();
            
            Log::info('Finding students for comment notification', [
                'report_id' => $report->id,
                'group_id' => $report->group_id,
                'student_count' => $groupStudents->count()
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
                } else {
                    Log::warning('No user found for student in comment notification', [
                        'student_id' => $groupStudent->student_id
                    ]);
                }
            }

            // Send notifications to students
            if ($students->isNotEmpty()) {
                // Load relationship for the comment
                $comment->load(['report', 'teacher']);
                
                foreach ($students as $student) {
                    try {
                        $student->notify(new NewReportComment($comment));
                        Log::info('Comment notification sent to student', [
                            'user_id' => $student->id,
                            'comment_id' => $comment->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send comment notification to student', [
                            'user_id' => $student->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info('Report comment created', [
                'comment_id' => $comment->id,
                'report_id' => $report->id,
                'teacher_id' => auth()->id(),
                'notified_students' => $students->count(),
            ]);

            return back()->with('success', 'Comment added successfully and students have been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create report comment', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'teacher_id' => auth()->id(),
            ]);

            return back()->withInput()
                ->with('error', 'Failed to add comment. Please try again.');
        }
    }
}