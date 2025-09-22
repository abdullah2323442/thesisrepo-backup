<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\GroupStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of reports for the student's group
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get student ID
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            $userData = session('external_student_data');
            $studentId = $userData['Roll'] ?? $userData['roll'] ?? null;
        } else {
            $studentId = $user->roll ?? $user->student_id ?? null;
        }
        
        // Find the student's group
        $groupStudent = GroupStudent::where('student_id', $studentId)->first();
        
        if (!$groupStudent) {
            return view('student.reports.index', [
                'reports' => collect(),
                'hasGroup' => false,
                'message' => 'You are not assigned to any group yet.'
            ]);
        }
        
        // Get all reports for the group
        $reports = Report::where('group_id', $groupStudent->group_id)
            ->with(['creator', 'comments.teacher'])
            ->latest()
            ->get();
        
        return view('student.reports.index', [
            'reports' => $reports,
            'hasGroup' => true,
            'groupName' => $groupStudent->group->name
        ]);
    }
    
    /**
     * Display the specified report
     */
    public function show(Report $report)
    {
        $user = Auth::user();
        
        // Get student ID
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            $userData = session('external_student_data');
            $studentId = $userData['Roll'] ?? $userData['roll'] ?? null;
        } else {
            $studentId = $user->roll ?? $user->student_id ?? null;
        }
        
        // Verify the student belongs to the group
        $groupStudent = GroupStudent::where('student_id', $studentId)
            ->where('group_id', $report->group_id)
            ->first();
        
        if (!$groupStudent) {
            abort(403, 'You are not authorized to view this report.');
        }
        
        // Load relationships
        $report->load(['group.students', 'comments.teacher', 'creator', 'submissions' => function ($query) use ($user) {
            $query->where('student_id', $user->id);
        }]);
        
        // Get current student's submission for this report
        $currentSubmission = $report->submissions->first();
        
        // Get annotation sessions for the current submission if it exists
        $annotationSessions = collect();
        if ($currentSubmission) {
            $annotationSessions = \App\Models\ReportAnnotationSession::where('submission_id', $currentSubmission->id)
                ->with('supervisor')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Mark related notifications as read
        $user->unreadNotifications()
            ->where('data->report_id', $report->id)
            ->update(['read_at' => now()]);
        
        return view('student.reports.show', compact('report', 'currentSubmission', 'annotationSessions'));
    }
    
    /**
     * Display notifications
     */
    public function notifications()
    {
        $user = Auth::user();
        
        // Get all notifications
        $notifications = $user->notifications()->paginate(20);
        
        return view('student.notifications', compact('notifications'));
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        // Redirect to the appropriate page based on notification type
        $data = $notification->data;
        if (isset($data['report_id'])) {
            return redirect()->route('student.reports.show', $data['report_id']);
        }
        
        return back();
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read.');
    }
}