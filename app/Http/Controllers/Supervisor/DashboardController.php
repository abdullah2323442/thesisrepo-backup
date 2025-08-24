<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Attempt to find matching Supervisor record by email or name
        $supervisor = SupervisorModel::query()
            ->where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->first();

        $groups = collect();
        if ($supervisor) {
            $groups = Group::with(['students'])
                ->where('supervisor_id', $supervisor->id)
                ->orderBy('batch_number', 'desc')
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'assigned_groups' => $groups->count(),
            'total_students' => $groups->flatMap->students->count(),
        ];

        return view('supervisor.dashboard', compact('supervisor', 'groups', 'stats'));
    }
}
