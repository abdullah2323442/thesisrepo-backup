<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AreaOfInterestController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Advisor\DashboardController as AdvisorDashboardController;
use App\Http\Controllers\Advisor\StudentController as AdvisorStudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Teacher routes
Route::middleware(['auth', 'teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
});

// Student routes
Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
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
    Route::post('/admin/supervisors/sync', [SupervisorController::class, 'syncFromApi'])->name('admin.supervisors.sync');
    Route::get('/admin/supervisors/{supervisor}/edit', [SupervisorController::class, 'edit'])->name('admin.supervisors.edit');
    Route::put('/admin/supervisors/{supervisor}', [SupervisorController::class, 'update'])->name('admin.supervisors.update');
    Route::post('/admin/supervisors/bulk-limits', [SupervisorController::class, 'bulkUpdateLimits'])->name('admin.supervisors.bulk-limits');
    Route::post('/admin/supervisors/{supervisor}/toggle', [SupervisorController::class, 'toggleStatus'])->name('admin.supervisors.toggle');
    Route::post('/admin/supervisors/{supervisor}/refresh', [SupervisorController::class, 'refreshFromApi'])->name('admin.supervisors.refresh');
    
    // Batch Management
    Route::get('/admin/batches', [BatchController::class, 'index'])->name('admin.batches.index');
    Route::post('/admin/batches/sync', [BatchController::class, 'syncFromApi'])->name('admin.batches.sync');
    Route::post('/admin/batches/{batch}/toggle', [BatchController::class, 'toggleStatus'])->name('admin.batches.toggle');
    Route::post('/admin/batches/bulk-action', [BatchController::class, 'bulkAction'])->name('admin.batches.bulk-action');
    Route::get('/admin/batches/{batch}/edit', [BatchController::class, 'edit'])->name('admin.batches.edit');
    Route::put('/admin/batches/{batch}', [BatchController::class, 'update'])->name('admin.batches.update');
    Route::delete('/admin/batches/{batch}', [BatchController::class, 'destroy'])->name('admin.batches.destroy');
    Route::get('/admin/batches/compare', [BatchController::class, 'compareWithApi'])->name('admin.batches.compare');
    Route::post('/admin/batches/activate-all', [BatchController::class, 'activateAll'])->name('admin.batches.activate-all');
    Route::post('/admin/batches/deactivate-all', [BatchController::class, 'deactivateAll'])->name('admin.batches.deactivate-all');
});

// Advisor routes
Route::middleware(['auth', 'advisor'])->group(function () {
    Route::get('/advisor/dashboard', [AdvisorDashboardController::class, 'index'])->name('advisor.dashboard');
    Route::get('/advisor/students', [AdvisorStudentController::class, 'index'])->name('advisor.students.index');
    Route::get('/advisor/students/{student}', [AdvisorStudentController::class, 'show'])->name('advisor.students.show');
    Route::post('/advisor/students/refresh', [AdvisorStudentController::class, 'refreshData'])->name('advisor.students.refresh');
    
    // Group Management routes
    Route::get('/advisor/groups', [\App\Http\Controllers\Advisor\GroupController::class, 'index'])->name('advisor.groups.index');
    Route::post('/advisor/groups/create', [\App\Http\Controllers\Advisor\GroupController::class, 'createGroups'])->name('advisor.groups.create');
    Route::post('/advisor/groups/add', [\App\Http\Controllers\Advisor\GroupController::class, 'addGroup'])->name('advisor.groups.add');
    Route::post('/advisor/groups/assign-student', [\App\Http\Controllers\Advisor\GroupController::class, 'assignStudent'])->name('advisor.groups.assign-student');
    Route::post('/advisor/groups/assign-area-of-interest', [\App\Http\Controllers\Advisor\GroupController::class, 'assignAreaOfInterest'])->name('advisor.groups.assign-area-of-interest');
    Route::post('/advisor/groups/remove-student', [\App\Http\Controllers\Advisor\GroupController::class, 'removeStudent'])->name('advisor.groups.remove-student');
    Route::post('/advisor/groups/upload-excel', [\App\Http\Controllers\Advisor\GroupController::class, 'uploadExcel'])->name('advisor.groups.upload-excel');
    Route::get('/advisor/groups/download-template', [\App\Http\Controllers\Advisor\GroupController::class, 'downloadTemplate'])->name('advisor.groups.download-template');
    
    // Supervisor Assignment routes
    Route::get('/advisor/supervisor-assignment', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'index'])->name('advisor.supervisor-assignment.index');
    Route::post('/advisor/supervisor-assignment/assign-manual', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'assignManual'])->name('advisor.supervisor-assignment.assign-manual');
    Route::post('/advisor/supervisor-assignment/unassign', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'unassign'])->name('advisor.supervisor-assignment.unassign');
    Route::post('/advisor/supervisor-assignment/run-lottery', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'runLottery'])->name('advisor.supervisor-assignment.run-lottery');
    Route::get('/advisor/supervisor-assignment/available-supervisors', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'getAvailableSupervisors'])->name('advisor.supervisor-assignment.available-supervisors');
    Route::get('/advisor/supervisor-assignment/preview-lottery', [\App\Http\Controllers\Advisor\SupervisorAssignmentController::class, 'previewLottery'])->name('advisor.supervisor-assignment.preview-lottery');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
