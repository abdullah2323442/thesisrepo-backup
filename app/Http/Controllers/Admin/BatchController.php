<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\BatchApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    private BatchApiService $batchApiService;

    public function __construct(BatchApiService $batchApiService)
    {
        $this->batchApiService = $batchApiService;
    }

    public function index()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $batches = Batch::orderBy('batch_number', 'desc')->paginate(15);
        $stats = Batch::getStats();

        return view('admin.batches.index', compact('batches', 'stats'));
    }

    public function syncFromApi()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $result = $this->batchApiService->syncBatches();

        if ($result['success']) {
            $message = "Sync completed! Synced: {$result['synced']}, Updated: {$result['updated']}, Total: {$result['total']}";
            if (!empty($result['errors'])) {
                $message .= " (Some errors occurred - check logs)";
            }
            return redirect()->route('admin.batches.index')->with('success', $message);
        } else {
            return redirect()->route('admin.batches.index')->with('error', 'Sync failed: ' . $result['error']);
        }
    }

    public function toggleStatus(Batch $batch)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $batch->update(['is_active' => !$batch->is_active]);
        
        $status = $batch->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.batches.index')
            ->with('success', "Batch {$batch->display_name} has been {$status}.");
    }

    public function bulkAction(Request $request)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate',
            'batch_ids' => 'required|array|min:1',
            'batch_ids.*' => 'exists:batches,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $action = $request->action;
        $batchIds = $request->batch_ids;
        $isActive = $action === 'activate';

        $updated = Batch::whereIn('id', $batchIds)->update(['is_active' => $isActive]);

        $actionText = $action === 'activate' ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.batches.index')
            ->with('success', "Successfully {$actionText} {$updated} batches.");
    }

    public function edit(Batch $batch)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('admin.batches.edit', compact('batch'));
    }

    public function update(Request $request, Batch $batch)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'batch_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $batch->update([
            'batch_name' => $request->batch_name ?: "Batch {$batch->batch_number}",
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.batches.index')
            ->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')
            ->with('success', "Batch {$batch->display_name} deleted successfully.");
    }

    public function compareWithApi()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $comparison = $this->batchApiService->compareBatches();

        if (!$comparison['success']) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Failed to compare with API: ' . $comparison['error']);
        }

        return view('admin.batches.compare', compact('comparison'));
    }

    public function activateAll()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $updated = Batch::where('is_active', false)->update(['is_active' => true]);

        return redirect()->route('admin.batches.index')
            ->with('success', "Activated {$updated} batches.");
    }

    public function deactivateAll()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $updated = Batch::where('is_active', true)->update(['is_active' => false]);

        return redirect()->route('admin.batches.index')
            ->with('success', "Deactivated {$updated} batches.");
    }
}
