<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    /**
     * Display the public thesis repository
     */
    public function index(Request $request)
    {
        $query = Report::approved()
            ->final()
            ->with(['group.students', 'group.supervisor', 'approver', 'areaOfInterest'])
            ->latest('approved_at');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('project_title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('abstract_md', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by keywords
        if ($request->filled('keywords')) {
            $keywords = array_map('trim', explode(',', $request->keywords));
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('keywords', 'LIKE', "%{$keyword}%");
                }
            });
        }

        // Filter by year range
        if ($request->filled('year_from')) {
            $query->whereRaw("strftime('%Y', approved_at) >= ?", [$request->year_from]);
        }
        if ($request->filled('year_to')) {
            $query->whereRaw("strftime('%Y', approved_at) <= ?", [$request->year_to]);
        }

        // Filter by supervisor
        if ($request->filled('supervisor')) {
            $query->whereHas('group', function ($q) use ($request) {
                $q->where('supervisor_id', $request->supervisor);
            });
        }

        // Filter by area of interest
        if ($request->filled('area_of_interest')) {
            $query->where('area_of_interest_id', $request->area_of_interest);
        }

        // Sorting
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->oldest('approved_at');
                break;
            case 'title':
                $query->orderBy('project_title');
                break;
            default:
                $query->latest('approved_at');
        }

        $reports = $query->paginate(12)->withQueryString();

        // Get all active supervisors
        $supervisors = Supervisor::where('is_active', true)
            ->orderBy('name')
            ->get();

        $availableYears = Report::approved()
            ->final()
            ->selectRaw("strftime('%Y', approved_at) as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get all active areas of interest
        $areasOfInterest = AreaOfInterest::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get popular keywords
        $popularKeywords = $this->getPopularKeywords();

        return view('home', compact('reports', 'supervisors', 'availableYears', 'areasOfInterest', 'popularKeywords'));
    }

    /**
     * Show a specific approved report
     */
    public function show(Report $report)
    {
        // Only show approved final reports
        if (!$report->isApproved() || !$report->isFinal()) {
            abort(404);
        }

        $report->load(['group.students', 'approver', 'submissions']);

        return view('reports.show', compact('report'));
    }

    /**
     * View PDF in browser
     */
    public function viewPdf(Report $report)
    {
        // Only allow viewing approved final reports
        if (!$report->isApproved() || !$report->isFinal()) {
            abort(404);
        }

        // Get the latest PDF submission for this report
        $submission = $report->submissions()
            ->where('mime_type', 'application/pdf')
            ->latest()
            ->first();

        if (!$submission) {
            abort(404, 'PDF file not found');
        }

        // Check if file exists in public disk
        if (!Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'PDF file not found on disk');
        }

        $file = Storage::disk('public')->get($submission->file_path);
        
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $submission->original_filename . '"');
    }

    /**
     * Download PDF file
     */
    public function downloadPdf(Report $report)
    {
        // Only allow downloading approved final reports
        if (!$report->isApproved() || !$report->isFinal()) {
            abort(404);
        }

        // Get the latest PDF submission for this report
        $submission = $report->submissions()
            ->where('mime_type', 'application/pdf')
            ->latest()
            ->first();

        if (!$submission) {
            abort(404, 'PDF file not found');
        }

        // Check if file exists in public disk
        if (!Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'PDF file not found on disk');
        }

        return Storage::disk('public')->download($submission->file_path, $submission->original_filename);
    }

    
    /**
     * Get popular keywords from approved reports
     */
    private function getPopularKeywords()
    {
        $keywordCounts = [];
        
        $reports = Report::approved()
            ->final()
            ->whereNotNull('keywords')
            ->get(['keywords']);

        foreach ($reports as $report) {
            $keywords = json_decode($report->keywords, true);
            if (is_array($keywords)) {
                foreach ($keywords as $keyword) {
                    $keyword = trim(strtolower($keyword));
                    if (!empty($keyword)) {
                        $keywordCounts[$keyword] = ($keywordCounts[$keyword] ?? 0) + 1;
                    }
                }
            }
        }

        // Sort by count and get top 20
        arsort($keywordCounts);
        return array_slice($keywordCounts, 0, 20, true);
    }
}