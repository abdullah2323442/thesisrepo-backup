<?php

namespace App\Http\Controllers\PanelMember;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupPanelMember;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $supervisor = SupervisorModel::where('email', $user->email)->first();

        if (!$supervisor) {
            return redirect()->route('panel-member.dashboard')
                ->with('error', 'You are not registered as a panel member in the system.');
        }

        // Get groups where user is panel member
        $panelMemberRecords = GroupPanelMember::where('supervisor_id', $supervisor->id)
            ->with(['group.students', 'group.supervisor', 'group.coSupervisor', 'group.advisor'])
            ->get();

        $groups = $panelMemberRecords->pluck('group');

        return view('panel-member.groups.index', compact('supervisor', 'groups', 'panelMemberRecords'));
    }
}