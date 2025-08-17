<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user logged in via external API (student)
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            // Use external API data
            $userType = 'external';
            $userData = session('external_student_data');
        } else {
            // Use model data (local database)
            $userType = 'model';
            $userData = $user;
        }
        
        return view('student.dashboard', [
            'user' => $userData,
            'userType' => $userType
        ]);
    }
    
    /**
     * Fetch student data from external API (optional)
     */
    private function fetchStudentDataFromAPI($user)
    {
        try {
            $studentApiUrl = config('app.external_api_student_url');
            
            if (!$studentApiUrl) {
                return null;
            }
            
            $response = Http::timeout(30)->get($studentApiUrl, [
                'student_id' => $user->student_id ?? $user->id,
                // Add other required parameters
            ]);
            
            if ($response->successful()) {
                $studentData = $response->json();
                
                // Store in session for future use
                session(['external_student_data' => $studentData]);
                
                return $studentData;
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch student data from external API:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
        
        return null;
    }
}
