<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get dashboard statistics
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('login_type', 'student')->count(),
            'total_teachers' => User::where('login_type', 'teacher')->count(),
            'total_admins' => User::whereJsonContains('type_id', '1')->count(),
        ];

        return view('admin.dashboard', compact('user', 'stats'));
    }
}
