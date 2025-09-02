<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AreaOfInterestController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Advisor\DashboardController as AdvisorDashboardController;
use App\Http\Controllers\Advisor\StudentController as AdvisorStudentController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\GroupController as SupervisorGroupController;
use App\Http\Controllers\Supervisor\MeetingController as SupervisorMeetingController;
use App\Http\Controllers\Supervisor\ReportController as SupervisorReportController;
use App\Http\Controllers\Teacher\ReportCommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Teacher routes
Route::middleware(['auth', 'teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

    // Supervisor panel (for teachers acting as supervisors)
    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/supervisor/groups', [SupervisorGroupController::class, 'index'])->name('supervisor.groups.index');
    Route::get('/supervisor/meetings', [SupervisorMeetingController::class, 'index'])->name('supervisor.meetings.index');
    Route::post('/supervisor/meetings', [SupervisorMeetingController::class, 'store'])->name('supervisor.meetings.store');
    Route::get('/supervisor/meetings/students', [SupervisorMeetingController::class, 'students'])->name('supervisor.meetings.students');
    Route::get('/supervisor/meetings/{meeting}', [SupervisorMeetingController::class, 'show'])->name('supervisor.meetings.show');
    Route::get('/supervisor/meetings/{meeting}/edit', [SupervisorMeetingController::class, 'edit'])->name('supervisor.meetings.edit');
    Route::put('/supervisor/meetings/{meeting}', [SupervisorMeetingController::class, 'update'])->name('supervisor.meetings.update');
    
    // Report Management routes
    Route::get('/supervisor/reports', [SupervisorReportController::class, 'index'])->name('supervisor.reports.index');
    Route::get('/supervisor/reports/create', [SupervisorReportController::class, 'create'])->name('supervisor.reports.create');
    Route::post('/supervisor/reports', [SupervisorReportController::class, 'store'])->name('supervisor.reports.store');
    Route::get('/supervisor/reports/{report}', [SupervisorReportController::class, 'show'])->name('supervisor.reports.show');
    Route::get('/supervisor/reports/{report}/edit', [SupervisorReportController::class, 'edit'])->name('supervisor.reports.edit');
    Route::put('/supervisor/reports/{report}', [SupervisorReportController::class, 'update'])->name('supervisor.reports.update');
    Route::delete('/supervisor/reports/{report}', [SupervisorReportController::class, 'destroy'])->name('supervisor.reports.destroy');
    
    // Teacher can comment on reports
    Route::post('/teacher/reports/{report}/comments', [ReportCommentController::class, 'store'])->name('teacher.reports.comments.store');
});

// Student routes
Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
        ->middleware('throttle:external_api_student_dashboard')
        ->name('student.dashboard');
    Route::get('/student/meetings', [StudentDashboardController::class, 'meetings'])
        ->name('student.meetings.index');
    Route::get('/student/meetings/pdf', [StudentDashboardController::class, 'downloadMeetingsPdf'])
        ->name('student.meetings.pdf');
    
    // Report routes
    Route::get('/student/reports', [\App\Http\Controllers\Student\ReportController::class, 'index'])
        ->name('student.reports.index');
    Route::get('/student/reports/{report}', [\App\Http\Controllers\Student\ReportController::class, 'show'])
        ->name('student.reports.show');
    
    // Report Submission routes
    Route::get('/student/reports/{report}/submissions/create', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'create'])
        ->name('student.reports.submissions.create');
    Route::post('/student/reports/{report}/submissions', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'store'])
        ->name('student.reports.submissions.store');
    Route::get('/student/reports/{report}/submissions/{submission}', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'show'])
        ->name('student.reports.submissions.show');
    Route::get('/student/reports/{report}/submissions/{submission}/edit', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'edit'])
        ->name('student.reports.submissions.edit');
    Route::put('/student/reports/{report}/submissions/{submission}', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'update'])
        ->name('student.reports.submissions.update');
    Route::delete('/student/reports/{report}/submissions/{submission}', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'destroy'])
        ->name('student.reports.submissions.destroy');
    Route::get('/student/reports/{report}/submissions/{submission}/download', [\App\Http\Controllers\Student\ReportSubmissionController::class, 'download'])
        ->name('student.reports.submissions.download');
    
    // Notification routes
    Route::get('/student/notifications', [\App\Http\Controllers\Student\ReportController::class, 'notifications'])
        ->name('student.notifications');
    Route::post('/student/notifications/{id}/mark-read', [\App\Http\Controllers\Student\ReportController::class, 'markNotificationAsRead'])
        ->name('student.notifications.mark-read');
    Route::post('/student/notifications/mark-all-read', [\App\Http\Controllers\Student\ReportController::class, 'markAllNotificationsAsRead'])
        ->name('student.notifications.mark-all-read');
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Area of Interest Management
    Route::get('/admin/areas-of-interest', [AreaOfInterestController::class, 'index'])->name('admin.areas-of-interest.index');
    Route::get('/admin/areas-of-interest/create', [AreaOfInterestController::class, 'create'])->name('admin.areas-of-interest.create');
    Route::post('/admin/areas-of-interest/bulk', [AreaOfInterestController::class, 'storeBulk'])->name('admin.areas-of-interest.store-bulk');
    Route::post('/admin/areas-of-interest', [AreaOfInterestController::class, 'store'])->name('admin.areas-of-interest.store');
    Route::get('/admin/areas-of-interest/{areaOfInterest}/edit', [AreaOfInterestController::class, 'edit'])->name('admin.areas-of-interest.edit');
    Route::put('/admin/areas-of-interest/{areaOfInterest}', [AreaOfInterestController::class, 'update'])->name('admin.areas-of-interest.update');
    Route::delete('/admin/areas-of-interest/{areaOfInterest}', [AreaOfInterestController::class, 'destroy'])->name('admin.areas-of-interest.destroy');
    
    // Supervisor Management
    Route::get('/admin/supervisors', [SupervisorController::class, 'index'])->name('admin.supervisors.index');
    Route::post('/admin/supervisors/sync', [SupervisorController::class, 'syncFromApi'])
        ->middleware('throttle:external_api_admin_supervisors_sync')
        ->name('admin.supervisors.sync');
    Route::get('/admin/supervisors/{supervisor}/edit', [SupervisorController::class, 'edit'])->name('admin.supervisors.edit');
    Route::put('/admin/supervisors/{supervisor}', [SupervisorController::class, 'update'])->name('admin.supervisors.update');
    Route::post('/admin/supervisors/bulk-limits', [SupervisorController::class, 'bulkUpdateLimits'])->name('admin.supervisors.bulk-limits');
    Route::post('/admin/supervisors/{supervisor}/toggle', [SupervisorController::class, 'toggleStatus'])->name('admin.supervisors.toggle');
    Route::post('/admin/supervisors/{supervisor}/toggle-area', [SupervisorController::class, 'toggleAreaOfInterest'])->name('admin.supervisors.toggle-area');
    Route::post('/admin/supervisors/{supervisor}/refresh', [SupervisorController::class, 'refreshFromApi'])
        ->middleware('throttle:external_api_admin_supervisors_refresh')
        ->name('admin.supervisors.refresh');
    
    // Batch Management
    Route::get('/admin/batches', [BatchController::class, 'index'])->name('admin.batches.index');
    Route::post('/admin/batches/sync', [BatchController::class, 'syncFromApi'])
        ->middleware('throttle:external_api_admin_batches_sync')
        ->name('admin.batches.sync');
    Route::post('/admin/batches/{batch}/toggle', [BatchController::class, 'toggleStatus'])->name('admin.batches.toggle');
    Route::post('/admin/batches/bulk-action', [BatchController::class, 'bulkAction'])->name('admin.batches.bulk-action');
    Route::get('/admin/batches/{batch}/edit', [BatchController::class, 'edit'])->name('admin.batches.edit');
    Route::put('/admin/batches/{batch}', [BatchController::class, 'update'])->name('admin.batches.update');
    Route::delete('/admin/batches/{batch}', [BatchController::class, 'destroy'])->name('admin.batches.destroy');
    Route::get('/admin/batches/compare', [BatchController::class, 'compareWithApi'])
        ->middleware('throttle:external_api_admin_batches_compare')
        ->name('admin.batches.compare');
    Route::post('/admin/batches/activate-all', [BatchController::class, 'activateAll'])->name('admin.batches.activate-all');
    Route::post('/admin/batches/deactivate-all', [BatchController::class, 'deactivateAll'])->name('admin.batches.deactivate-all');
    
    // Group Management
    Route::get('/admin/groups', [\App\Http\Controllers\Admin\GroupManagementController::class, 'index'])->name('admin.groups.index');
    Route::post('/admin/groups/assign-student', [\App\Http\Controllers\Admin\GroupManagementController::class, 'assignStudent'])->name('admin.groups.assign-student');
    Route::post('/admin/groups/remove-student', [\App\Http\Controllers\Admin\GroupManagementController::class, 'removeStudent'])->name('admin.groups.remove-student');
    Route::post('/admin/groups/assign-area-of-interest', [\App\Http\Controllers\Admin\GroupManagementController::class, 'assignAreaOfInterest'])->name('admin.groups.assign-area-of-interest');
    Route::post('/admin/groups/assign-supervisor', [\App\Http\Controllers\Admin\GroupManagementController::class, 'assignSupervisor'])->name('admin.groups.assign-supervisor');
    Route::post('/admin/groups/unassign-supervisor', [\App\Http\Controllers\Admin\GroupManagementController::class, 'unassignSupervisor'])->name('admin.groups.unassign-supervisor');
    Route::get('/admin/groups/available-supervisors', [\App\Http\Controllers\Admin\GroupManagementController::class, 'getAvailableSupervisors'])->name('admin.groups.available-supervisors');
Route::post('/admin/groups/create', [\App\Http\Controllers\Admin\GroupManagementController::class, 'createGroup'])->name('admin.groups.create');
    // Performance Monitoring
    Route::get('/admin/performance', [\App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('admin.performance.index');
    Route::get('/admin/performance/metrics', [\App\Http\Controllers\Admin\PerformanceController::class, 'metrics'])->name('admin.performance.metrics');
    Route::get('/admin/performance/health', [\App\Http\Controllers\Admin\PerformanceController::class, 'health'])->name('admin.performance.health');
    Route::get('/admin/performance/database', [\App\Http\Controllers\Admin\PerformanceController::class, 'database'])->name('admin.performance.database');
    Route::get('/admin/performance/api', [\App\Http\Controllers\Admin\PerformanceController::class, 'api'])->name('admin.performance.api');
    Route::get('/admin/performance/security', [\App\Http\Controllers\Admin\PerformanceController::class, 'security'])->name('admin.performance.security');
    Route::post('/admin/performance/clear-cache', [\App\Http\Controllers\Admin\PerformanceController::class, 'clearCache'])->name('admin.performance.clear-cache');
    Route::get('/admin/performance/export', [\App\Http\Controllers\Admin\PerformanceController::class, 'export'])->name('admin.performance.export');
    Route::get('/admin/performance/test', [\App\Http\Controllers\Admin\PerformanceController::class, 'test'])->name('admin.performance.test');
});

// Advisor routes
Route::middleware(['auth', 'advisor'])->group(function () {
    Route::get('/advisor/dashboard', [AdvisorDashboardController::class, 'index'])
        ->middleware('throttle:external_api_advisor_dashboard')
        ->name('advisor.dashboard');
    Route::get('/advisor/students', [AdvisorStudentController::class, 'index'])
        ->middleware('throttle:external_api_advisor_students')
        ->name('advisor.students.index');
    Route::get('/advisor/students/{student}', [AdvisorStudentController::class, 'show'])
        ->middleware('throttle:external_api_advisor_students_show')
        ->name('advisor.students.show');
    Route::post('/advisor/students/refresh', [AdvisorStudentController::class, 'refreshData'])
        ->middleware('throttle:external_api_advisor_students_refresh')
        ->name('advisor.students.refresh');
    
    // Group Management routes
    Route::get('/advisor/groups', [\App\Http\Controllers\Advisor\GroupController::class, 'index'])
        ->middleware('throttle:external_api_advisor_groups')
        ->name('advisor.groups.index');
    Route::post('/advisor/groups/create', [\App\Http\Controllers\Advisor\GroupController::class, 'createGroups'])
        ->middleware('throttle:external_api_advisor_groups')
        ->name('advisor.groups.create');
    Route::post('/advisor/groups/add', [\App\Http\Controllers\Advisor\GroupController::class, 'addGroup'])->name('advisor.groups.add');
    Route::post('/advisor/groups/assign-student', [\App\Http\Controllers\Advisor\GroupController::class, 'assignStudent'])
        ->middleware('throttle:external_api_advisor_groups')
        ->name('advisor.groups.assign-student');
    Route::post('/advisor/groups/assign-area-of-interest', [\App\Http\Controllers\Advisor\GroupController::class, 'assignAreaOfInterest'])->name('advisor.groups.assign-area-of-interest');
    Route::post('/advisor/groups/unassign-all-areas-of-interest', [\App\Http\Controllers\Advisor\GroupController::class, 'unassignAllAreasOfInterest'])->name('advisor.groups.unassign-all-areas-of-interest');
    Route::post('/advisor/groups/remove-all-groups', [\App\Http\Controllers\Advisor\GroupController::class, 'removeAllGroups'])->name('advisor.groups.remove-all-groups');
    Route::post('/advisor/groups/remove-student', [\App\Http\Controllers\Advisor\GroupController::class, 'removeStudent'])->name('advisor.groups.remove-student');
    Route::post('/advisor/groups/upload-excel', [\App\Http\Controllers\Advisor\GroupController::class, 'uploadExcel'])
        ->middleware('throttle:external_api_advisor_groups')
        ->name('advisor.groups.upload-excel');
    Route::get('/advisor/groups/download-template', [\App\Http\Controllers\Advisor\GroupController::class, 'downloadTemplate'])
        ->middleware('throttle:external_api_advisor_groups')
        ->name('advisor.groups.download-template');
    
    // Supervisor Assignment routes
    Route::get('/advisor/supervisor-assignment', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'index'])->name('advisor.supervisor-assignment.index');
    Route::post('/advisor/supervisor-assignment/assign-manual', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'assignManual'])->name('advisor.supervisor-assignment.assign-manual');
    Route::post('/advisor/supervisor-assignment/unassign', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'unassign'])->name('advisor.supervisor-assignment.unassign');
    Route::post('/advisor/supervisor-assignment/unassign-all', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'unassignAll'])->name('advisor.supervisor-assignment.unassign-all');
    Route::post('/advisor/supervisor-assignment/run-lottery', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'runLottery'])->name('advisor.supervisor-assignment.run-lottery');
    Route::get('/advisor/supervisor-assignment/available-supervisors', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'getAvailableSupervisors'])
        ->middleware('throttle:external_api_advisor_available_supervisors')
        ->name('advisor.supervisor-assignment.available-supervisors');
    Route::get('/advisor/supervisor-assignment/preview-lottery', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'previewLottery'])->name('advisor.supervisor-assignment.preview-lottery');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API routes for notifications (accessible by authenticated users)
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
});

// Debug route (remove in production)
Route::get('/debug/notifications', [\App\Http\Controllers\DebugController::class, 'checkNotifications']);

require __DIR__.'/auth.php';
    // Group deletion routes
    Route::delete('/admin/groups/{group}', [\App\Http\Controllers\Admin\GroupManagementController::class, 'deleteGroup'])->name('admin.groups.delete');
    Route::post('/admin/groups/bulk-delete', [\App\Http\Controllers\Admin\GroupManagementController::class, 'bulkDeleteGroups'])->name('admin.groups.bulk-delete');
