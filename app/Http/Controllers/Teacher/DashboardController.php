<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessionUser = Session::get('user', []);
        
        // Determine if we're using model data or session data
        $userType = $user && $user->isTeacher() ? 'model' : 'session';
        
        // If using model, ensure we have the correct TypeIds
        if ($userType === 'model') {
            $userData = $user;
            // Make sure typeIds are properly accessible
            $userData->typeIds = $user->typeIds;
        } else {
            $userData = $sessionUser;
        }

        return view('teacher.dashboard', [
            'user' => $userData,
            'userType' => $userType
        ]);
    }
}
