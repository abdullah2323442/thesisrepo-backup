<?php

namespace App\Http\Controllers\CoSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Report;
use App\Models\Meeting;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the co-supervisor dashboard
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get the supervisor record for this user
            $supervisor = Supervisor::where('email', $user->email)->first();
            
            if (!$supervisor) {
                return redirect()->route('dashboard')
                    ->with('error', 'You are not registered as a co-supervisor in the system.');
            }

            // Get groups where this supervisor is assigned as co-supervisor
            $groups = Group::where('co_supervisor_id', $supervisor->id)
                ->with(['students', 'supervisor', 'advisor', 'reports'])
                ->get();

            // Calculate statistics
            $totalGroups = $groups->count();
            $totalStudents = $groups->sum(function ($group) {
                return $group->students->count();
            });

            // Get recent reports from co-supervised groups
            $recentReports = Report::whereIn('group_id', $groups->pluck('id'))
                ->with(['group', 'group.students'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Get upcoming meetings for co-supervised groups
            $upcomingMeetings = Meeting::whereIn('group_id', $groups->pluck('id'))
                ->where('meeting_date', '>=', now()->toDateString())
                ->with(['group', 'group.students'])
                ->orderBy('meeting_date', 'asc')
                ->take(5)
                ->get();

            // Get pending reports (reports that need attention)
            $pendingReports = Report::whereIn('group_id', $groups->pluck('id'))
                ->where('status', 'draft')
                ->with(['group', 'group.students'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return view('co-supervisor.dashboard', compact(
                'supervisor',
                'groups',
                'totalGroups',
                'totalStudents',
                'recentReports',
                'upcomingMeetings',
                'pendingReports'
            ));

        } catch (\Exception $e) {
            Log::error('Co-supervisor dashboard error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load co-supervisor dashboard. Please try again.');
        }
    }
}