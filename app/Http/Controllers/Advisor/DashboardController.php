<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Services\StudentApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private StudentApiService $studentApiService;

    public function __construct(StudentApiService $studentApiService)
    {
        $this->studentApiService = $studentApiService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Find the supervisor record for this user
        $supervisor = Supervisor::where('email', $user->email)->first();
        
        if (!$supervisor) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'You are not registered as a supervisor in the system.');
        }

        // Get advisor statistics
        $stats = $this->studentApiService->getAdvisorStats($supervisor->api_id);
        
        if (!$stats['success']) {
            $stats = [
                'total_students' => 0,
                'male_students' => 0,
                'female_students' => 0,
                'batches_count' => 0,
                'batches' => [],
                'no_active_batches' => isset($stats['no_active_batches']) && $stats['no_active_batches']
            ];
        }

        return view('advisor.dashboard', compact('user', 'supervisor', 'stats'));
    }
}
