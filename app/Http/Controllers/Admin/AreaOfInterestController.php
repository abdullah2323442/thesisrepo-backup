<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaOfInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaOfInterestController extends Controller
{
    public function index()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $areasOfInterest = AreaOfInterest::orderBy('name')->paginate(15);
        
        return view('admin.areas-of-interest.index', compact('areasOfInterest'));
    }

    public function create()
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('admin.areas-of-interest.create');
    }

    public function store(Request $request)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:area_of_interests,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        AreaOfInterest::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.areas-of-interest.index')
            ->with('success', 'Area of Interest created successfully.');
    }

    public function storeBulk(Request $request)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'bulk_areas' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $areas = explode("\n", $request->bulk_areas);
        $created = 0;
        $skipped = 0;

        foreach ($areas as $area) {
            $area = trim($area);
            if (empty($area)) continue;

            // Check if area already exists
            if (AreaOfInterest::where('name', $area)->exists()) {
                $skipped++;
                continue;
            }

            AreaOfInterest::create([
                'name' => $area,
                'description' => null,
                'is_active' => true,
            ]);
            $created++;
        }

        $message = "Bulk creation completed. Created: {$created}";
        if ($skipped > 0) {
            $message .= ", Skipped (already exists): {$skipped}";
        }

        return redirect()->route('admin.areas-of-interest.index')
            ->with('success', $message);
    }

    public function edit(AreaOfInterest $areaOfInterest)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('admin.areas-of-interest.edit', compact('areaOfInterest'));
    }

    public function update(Request $request, AreaOfInterest $areaOfInterest)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:area_of_interests,name,' . $areaOfInterest->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $areaOfInterest->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.areas-of-interest.index')
            ->with('success', 'Area of Interest updated successfully.');
    }

    public function destroy(AreaOfInterest $areaOfInterest)
    {
        // Ensure only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $areaOfInterest->delete();

        return redirect()->route('admin.areas-of-interest.index')
            ->with('success', 'Area of Interest deleted successfully.');
    }
}
