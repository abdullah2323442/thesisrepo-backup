<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Redirect to the appropriate dashboard based on user type
     */
    public function index()
    {
        $userType = Session::get('user_type');
        
        // Check session for user type
        if ($userType === 'teacher') {
            return redirect()->route('teacher.dashboard');
        } elseif ($userType === 'student') {
            return redirect()->route('student.dashboard');
        }
        
        // Fallback: Check if user has specific roles
        $user = Auth::user();
        
        if ($user) {
            // Check if user is a teacher (based on your User model structure)
            if ($user->roll === null || $user->roll === '') {
                // Teachers typically don't have roll numbers
                return redirect()->route('teacher.dashboard');
            } else {
                // Students have roll numbers
                return redirect()->route('student.dashboard');
            }
        }
        
        // Default fallback to login if no user type is determined
        return redirect()->route('login');
    }
}