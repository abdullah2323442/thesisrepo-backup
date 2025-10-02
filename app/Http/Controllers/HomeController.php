<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Models\GroupStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    /**
     * Display the public thesis repository with advanced search
     */
    public function index(Request $request)
    {
        // Validate and sanitize inputs
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'keywords' => 'nullable|string|max:500',
            'area_of_interest' => 'nullable|integer|exists:area_of_interests,id',
            'year_from' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'supervisor' => 'nullable|integer|exists:supervisors,id',
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'title'])],
        ]);

        // Build the query with optimized eager loading
        $query = Report::approved()
            ->final()
            ->with([
                'group.students',
                'group.supervisor',
                'approver',
                'areaOfInterest'
            ])
            ->latest('approved_at');

        // Advanced search functionality
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $searchTerms = $this->parseSearchTerms($searchTerm);
            
            $query->where(function ($q) use ($searchTerm, $searchTerms) {
                // Search in project title (highest priority)
                $q->where('project_title', 'LIKE', "%{$searchTerm}%")
                  // Search in abstract
                  ->orWhere('abstract_md', 'LIKE', "%{$searchTerm}%")
                  // Search in keywords JSON field
                  ->orWhere('keywords', 'LIKE', "%{$searchTerm}%")
                  // Search in extra input field
                  ->orWhere('extra_input', 'LIKE', "%{$searchTerm}%");
                
                // Search by author names (student names)
                $q->orWhereHas('group.students', function ($studentQuery) use ($searchTerm, $searchTerms) {
                    $studentQuery->where(function ($nameQuery) use ($searchTerm, $searchTerms) {
                        $nameQuery->where('student_name', 'LIKE', "%{$searchTerm}%")
                                  ->orWhere('student_id', 'LIKE', "%{$searchTerm}%");
                        
                        // Search for individual terms in student names
                        foreach ($searchTerms as $term) {
                            if (strlen($term) >= 3) {
                                $nameQuery->orWhere('student_name', 'LIKE', "%{$term}%");
                            }
                        }
                    });
                });
                
                // Search by supervisor name
                $q->orWhereHas('group.supervisor', function ($supervisorQuery) use ($searchTerm, $searchTerms) {
                    $supervisorQuery->where(function ($nameQuery) use ($searchTerm, $searchTerms) {
                        $nameQuery->where('fullname', 'LIKE', "%{$searchTerm}%")
                                  ->orWhere('name', 'LIKE', "%{$searchTerm}%");
                        
                        // Search for individual terms in supervisor names
                        foreach ($searchTerms as $term) {
                            if (strlen($term) >= 3) {
                                $nameQuery->orWhere('fullname', 'LIKE', "%{$term}%");
                            }
                        }
                    });
                });
            });
        }

        // Filter by keywords (comma-separated)
        if ($request->filled('keywords')) {
            $keywords = $this->parseKeywords($request->keywords);
            
            if (!empty($keywords)) {
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        // Search in keywords JSON field
                        $q->orWhere('keywords', 'LIKE', "%{$keyword}%")
                          // Also search in title and abstract for better results
                          ->orWhere('project_title', 'LIKE', "%{$keyword}%")
                          ->orWhere('abstract_md', 'LIKE', "%{$keyword}%");
                    }
                });
            }
        }

        // Filter by publication year
        if ($request->filled('year_from')) {
            $yearFrom = (int) $request->year_from;
            $query->whereRaw("CAST(strftime('%Y', approved_at) AS INTEGER) >= ?", [$yearFrom]);
        }

        // Filter by supervisor
        if ($request->filled('supervisor')) {
            $supervisorId = (int) $request->supervisor;
            $query->whereHas('group', function ($q) use ($supervisorId) {
                $q->where('supervisor_id', $supervisorId);
            });
        }

        // Filter by area of interest
        if ($request->filled('area_of_interest')) {
            $areaId = (int) $request->area_of_interest;
            $query->where('area_of_interest_id', $areaId);
        }

        // Sorting with validation
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->oldest('approved_at');
                break;
            case 'title':
                $query->orderBy('project_title', 'asc');
                break;
            case 'newest':
            default:
                $query->latest('approved_at');
                break;
        }

        // Paginate results with query string preservation
        $reports = $query->paginate(12)->withQueryString();

        // Cache static data for better performance (5 minutes cache)
        $supervisors = Cache::remember('active_supervisors', 300, function () {
            return Supervisor::where('is_active', true)
                ->orderBy('fullname')
                ->get(['id', 'fullname', 'name']);
        });

        $availableYears = Cache::remember('available_report_years', 300, function () {
            return Report::approved()
                ->final()
                ->selectRaw("CAST(strftime('%Y', approved_at) AS INTEGER) as year")
                ->distinct()
                ->orderByRaw('year DESC')
                ->pluck('year')
                ->filter();
        });

        $areasOfInterest = Cache::remember('active_areas_of_interest', 300, function () {
            return AreaOfInterest::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        // Get popular keywords (cached for 10 minutes)
        $popularKeywords = $this->getPopularKeywords();

        return view('home', compact(
            'reports',
            'supervisors',
            'availableYears',
            'areasOfInterest',
            'popularKeywords'
        ));
    }

    /**
     * Parse search terms into individual words for better matching
     */
    private function parseSearchTerms(string $searchTerm): array
    {
        // Remove special characters and split by spaces
        $searchTerm = preg_replace('/[^\w\s-]/u', ' ', $searchTerm);
        $terms = array_filter(array_map('trim', explode(' ', $searchTerm)));
        
        // Remove very short terms (less than 2 characters)
        return array_filter($terms, function($term) {
            return strlen($term) >= 2;
        });
    }

    /**
     * Parse and sanitize keywords from comma-separated string
     */
    private function parseKeywords(string $keywordsString): array
    {
        $keywords = array_map('trim', explode(',', $keywordsString));
        
        // Filter out empty keywords and sanitize
        $keywords = array_filter($keywords, function($keyword) {
            return !empty($keyword) && strlen($keyword) >= 2;
        });
        
        // Limit to 10 keywords to prevent abuse
        return array_slice($keywords, 0, 10);
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
     * Get popular keywords from approved reports with caching
     */
    private function getPopularKeywords()
    {
        return Cache::remember('popular_keywords', 600, function () {
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
                        if (!empty($keyword) && strlen($keyword) >= 2) {
                            $keywordCounts[$keyword] = ($keywordCounts[$keyword] ?? 0) + 1;
                        }
                    }
                }
            }

            // Sort by count and get top 20
            arsort($keywordCounts);
            return array_slice($keywordCounts, 0, 20, true);
        });
    }
}
