<?php

namespace App\Http\Controllers\PanelMember;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupPanelMember;
use App\Models\Report;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the panel member dashboard
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get the supervisor record for this user
            $supervisor = Supervisor::where('email', $user->email)->first();
            
            if (!$supervisor) {
                return redirect()->route('dashboard')
                    ->with('error', 'You are not registered as a panel member in the system.');
            }

            // Get groups where this supervisor is assigned as panel member
            $panelMemberRecords = GroupPanelMember::where('supervisor_id', $supervisor->id)
                ->with(['group.students', 'group.supervisor', 'group.coSupervisor', 'group.advisor', 'group.reports'])
                ->get();

            $groups = $panelMemberRecords->pluck('group');

            // Calculate statistics
            $totalGroups = $groups->count();
            $totalStudents = $groups->sum(function ($group) {
                return $group->students->count();
            });

            // Get recent reports from panel member groups
            $recentReports = Report::whereIn('group_id', $groups->pluck('id'))
                ->with(['group', 'group.students'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Get pending reports (reports that need attention)
            $pendingReports = Report::whereIn('group_id', $groups->pluck('id'))
                ->where('status', 'draft')
                ->with(['group', 'group.students'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return view('panel-member.dashboard', compact(
                'supervisor',
                'groups',
                'panelMemberRecords',
                'totalGroups',
                'totalStudents',
                'recentReports',
                'pendingReports'
            ));

        } catch (\Exception $e) {
            Log::error('Panel member dashboard error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load panel member dashboard. Please try again.');
        }
    }
}