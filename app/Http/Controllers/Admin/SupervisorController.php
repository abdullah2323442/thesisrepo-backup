<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Services\SupervisorApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{
    private SupervisorApiService $apiService;

    public function __construct(SupervisorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $supervisors = Supervisor::with('areasOfInterest')
            ->orderBy('fullname')
            ->paginate(15);

        $lastSync = Supervisor::whereNotNull('last_synced_at')
            ->orderBy('last_synced_at', 'desc')
            ->first();

        $stats = [
            'total_supervisors' => Supervisor::count(),
            'active_supervisors' => Supervisor::where('is_active', true)->count(),
            'total_slots' => Supervisor::sum('thesis_limit'),
            'last_sync' => $lastSync ? $lastSync->last_synced_at : null,
        ];

        $areasOfInterest = AreaOfInterest::where('is_active', true)->orderBy('name')->get();

        return view('admin.supervisors.index', compact('supervisors', 'stats', 'areasOfInterest'));
    }

    public function syncFromApi()
    {
        $result = $this->apiService->syncSupervisors();

        if ($result['success']) {
            $message = "Sync completed! Synced: {$result['synced']}, Updated: {$result['updated']}, Total: {$result['total']}";
            if (!empty($result['errors'])) {
                $message .= " (Some errors occurred - check logs)";
            }
            return redirect()->route('admin.supervisors.index')->with('success', $message);
        } else {
            return redirect()->route('admin.supervisors.index')->with('error', 'Sync failed: ' . $result['error']);
        }
    }

    public function edit(Supervisor $supervisor)
    {
        $areasOfInterest = AreaOfInterest::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.supervisors.edit', compact('supervisor', 'areasOfInterest'));
    }

    public function update(Request $request, Supervisor $supervisor)
    {
        $validator = Validator::make($request->all(), [
            'thesis_limit' => 'required|integer|min:0|max:20',
            'areas_of_interest' => 'array',
            'areas_of_interest.*' => 'exists:area_of_interests,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update supervisor
        $supervisor->update([
            'thesis_limit' => $request->thesis_limit,
            'is_active' => $request->has('is_active'),
        ]);

        // Sync areas of interest
        $supervisor->areasOfInterest()->sync($request->areas_of_interest ?? []);

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor updated successfully.');
    }

    public function bulkUpdateLimits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulk_thesis_limit' => 'required|integer|min:0|max:20',
            'apply_to' => 'required|in:all,active,selected',
            'supervisor_ids' => 'required_if:apply_to,selected|array',
            'supervisor_ids.*' => 'exists:supervisors,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = Supervisor::query();

        switch ($request->apply_to) {
            case 'active':
                $query->where('is_active', true);
                break;
            case 'selected':
                $query->whereIn('id', $request->supervisor_ids);
                break;
            // 'all' requires no additional filtering
        }

        $updated = $query->update(['thesis_limit' => $request->bulk_thesis_limit]);

        return redirect()->route('admin.supervisors.index')
            ->with('success', "Updated thesis limits for {$updated} supervisors.");
    }

    public function toggleStatus(Request $request, Supervisor $supervisor)
    {
        $supervisor->update(['is_active' => !$supervisor->is_active]);
        
        $status = $supervisor->is_active ? 'activated' : 'deactivated';
        
        // Preserve the current page
        $page = $request->get('page', 1);
        
        return redirect()->route('admin.supervisors.index', ['page' => $page])
            ->with('success', "Supervisor {$supervisor->fullname} has been {$status}.");
    }

    public function refreshFromApi(Request $request, Supervisor $supervisor)
    {
        $updated = $this->apiService->refreshSupervisor($supervisor->api_id);

        // Preserve the current page
        $page = $request->get('page', 1);

        if ($updated) {
            return redirect()->route('admin.supervisors.index', ['page' => $page])
                ->with('success', "Supervisor {$supervisor->fullname} data refreshed from API.");
        } else {
            return redirect()->route('admin.supervisors.index', ['page' => $page])
                ->with('error', "Failed to refresh supervisor data from API.");
        }
    }

    public function toggleAreaOfInterest(Request $request, Supervisor $supervisor)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:area_of_interests,id',
        ]);

        if ($validator->fails()) {
            // Preserve the current page even on error
            $page = $request->get('page', 1);
            return redirect()->route('admin.supervisors.index', ['page' => $page])
                ->with('error', 'Invalid area of interest.');
        }

        $areaId = $request->area_id;
        $area = AreaOfInterest::find($areaId);

        if ($supervisor->areasOfInterest()->where('area_of_interests.id', $areaId)->exists()) {
            // Remove the area
            $supervisor->areasOfInterest()->detach($areaId);
            $action = 'removed from';
        } else {
            // Add the area
            $supervisor->areasOfInterest()->attach($areaId);
            $action = 'assigned to';
        }

        // Preserve the current page
        $page = $request->get('page', 1);

        return redirect()->route('admin.supervisors.index', ['page' => $page])
            ->with('success', "Area '{$area->name}' has been {$action} {$supervisor->fullname}.");
    }
}
