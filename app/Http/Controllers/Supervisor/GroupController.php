<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Supervisor as SupervisorModel;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $supervisor = SupervisorModel::query()
            ->where('email', $user->email)
            ->orWhere('fullname', $user->name)
            ->first();

        $groups = Group::with(['students', 'areaOfInterest'])
            ->when($supervisor, fn($q) => $q->where('supervisor_id', $supervisor->id))
            ->orderBy('batch_number', 'desc')
            ->orderBy('name')
            ->paginate(12);

        return view('supervisor.groups.index', compact('supervisor', 'groups'));
    }
}
