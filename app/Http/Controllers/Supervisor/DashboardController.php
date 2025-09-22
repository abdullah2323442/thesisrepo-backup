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
        $coSupervisedGroups = collect();
        $panelMemberGroups = collect();
        
        if ($supervisor) {
            // Get groups where user is main supervisor
            $groups = Group::with(['students'])
                ->where('supervisor_id', $supervisor->id)
                ->orderBy('batch_number', 'desc')
                ->orderBy('name')
                ->get();
                
            // Get groups where user is co-supervisor
            $coSupervisedGroups = Group::with(['students', 'supervisor'])
                ->where('co_supervisor_id', $supervisor->id)
                ->orderBy('batch_number', 'desc')
                ->orderBy('name')
                ->get();
                
            // Get groups where user is panel member
            $panelMemberGroups = Group::with(['students', 'supervisor', 'coSupervisor'])
                ->whereHas('panelMembers', function($query) use ($supervisor) {
                    $query->where('supervisor_id', $supervisor->id);
                })
                ->orderBy('batch_number', 'desc')
                ->orderBy('name')
                ->get();
        }

        $stats = [
            'assigned_groups' => $groups->count(),
            'co_supervised_groups' => $coSupervisedGroups->count(),
            'panel_member_groups' => $panelMemberGroups->count(),
            'total_students' => $groups->flatMap->students->count() + 
                               $coSupervisedGroups->flatMap->students->count() + 
                               $panelMemberGroups->flatMap->students->count(),
        ];

        return view('supervisor.dashboard', compact('supervisor', 'groups', 'coSupervisedGroups', 'panelMemberGroups', 'stats'));
    }
}
