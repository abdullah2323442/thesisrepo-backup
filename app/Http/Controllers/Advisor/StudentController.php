<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Services\StudentApiService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private StudentApiService $studentApiService;

    public function __construct(StudentApiService $studentApiService)
    {
        $this->studentApiService = $studentApiService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Find the supervisor record for this user
        $supervisor = Supervisor::where('email', $user->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not registered as a supervisor in the system.');
        }

        // Get filter parameters
        $selectedBatch = $request->get('batch');
        $search = $request->get('search');
        
        // Get all students for this advisor
        $result = $this->studentApiService->getStudentsByAdvisor($supervisor->api_id);
        
        if (!$result['success']) {
            return view('advisor.students.index', [
                'students' => collect(),
                'batches' => [],
                'selectedBatch' => $selectedBatch,
                'search' => $search,
                'supervisor' => $supervisor,
                'error' => $result['error'],
                'no_active_batches' => isset($result['no_active_batches']) && $result['no_active_batches']
            ]);
        }

        $students = collect($result['students']);
        $availableBatches = $result['batches'];

        // Apply filters
        if ($selectedBatch) {
            $students = $students->where('batch_name', $selectedBatch);
        }

        if ($search) {
            $students = $students->filter(function ($student) use ($search) {
                return stripos($student['name'], $search) !== false ||
                       stripos($student['roll'], $search) !== false;
            });
        }

        // Sort by roll number
        $students = $students->sortBy('roll');

        return view('advisor.students.index', [
            'students' => $students,
            'batches' => $availableBatches,
            'selectedBatch' => $selectedBatch,
            'search' => $search,
            'supervisor' => $supervisor,
            'totalStudents' => $result['total']
        ]);
    }

    public function show(Request $request, $studentId)
    {
        $user = auth()->user();
        
        // Find the supervisor record for this user
        $supervisor = Supervisor::where('email', $user->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not registered as a supervisor in the system.');
        }

        // Get all students for this advisor
        $result = $this->studentApiService->getStudentsByAdvisor($supervisor->api_id);
        
        if (!$result['success']) {
            return redirect()->route('advisor.students.index')
                ->with('error', 'Failed to fetch student data: ' . $result['error']);
        }

        // Find the specific student
        $student = collect($result['students'])->firstWhere('id', $studentId);
        
        if (!$student) {
            return redirect()->route('advisor.students.index')
                ->with('error', 'Student not found or you are not their advisor.');
        }

        return view('advisor.students.show', [
            'student' => $student,
            'supervisor' => $supervisor
        ]);
    }

    public function refreshData()
    {
        $user = auth()->user();
        
        // Find the supervisor record for this user
        $supervisor = Supervisor::where('email', $user->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not registered as a supervisor in the system.');
        }

        // Clear cache to force fresh data
        $this->studentApiService->clearCache();
        
        return redirect()->route('advisor.students.index')
            ->with('success', 'Student data refreshed successfully.');
    }
}
