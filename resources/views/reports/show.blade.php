<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report->project_title }} - {{ config('app.name', 'Thesis Repository') }}</title>
    
    <!-- Meta Tags for SEO -->
    <meta name="description" content="{{ Str::limit(strip_tags($report->abstract_md), 160) }}">
    <meta name="keywords" content="{{ implode(', ', json_decode($report->keywords, true) ?? []) }}">
    <meta name="author" content="{{ $report->group->students->pluck('student_name')->implode(', ') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $report->project_title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($report->abstract_md), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
                        {{ config('app.name', 'Thesis Repository') }}
                    </a>
                </div>
                
                @auth
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Dashboard
                        </a>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" 
                           class="text-gray-600 hover:text-gray-900 transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">Thesis Report</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Report Header -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-8">
                <!-- Title and Status -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                            Published Thesis
                        </span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                            {{ $report->approved_at->format('Y') }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                        {{ $report->project_title }}
                    </h1>
                </div>

                <!-- Authors -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Authors</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($report->group->students as $student)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $student->student_name }}</p>
                                    <p class="text-sm text-gray-600">Student ID: {{ $student->student_id }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Supervisor -->
                @if($report->approver)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Supervisor</h3>
                        <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                            <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-800">{{ $report->approver->name }}</p>
                                <p class="text-sm text-gray-600">Thesis Supervisor</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Publication Info -->
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-green-800 mb-1">Published Thesis</h4>
                            <p class="text-sm text-green-700">
                                This thesis was approved and published on {{ $report->approved_at->format('F d, Y') }}.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- PDF Access Section -->
                @php
                    $pdfSubmission = $report->submissions()->where('mime_type', 'application/pdf')->latest()->first();
                @endphp
                
                @if($pdfSubmission)
                <div class="mb-6 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-red-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">Full Thesis Document</h4>
                                <p class="text-sm text-gray-600">
                                    {{ $pdfSubmission->original_filename }} 
                                    <span class="text-gray-500">({{ $pdfSubmission->formatted_file_size }})</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('reports.pdf.view', $report) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View PDF
                            </a>
                            <a href="{{ route('reports.pdf.download', $report) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800 mb-1">PDF Not Available</h4>
                            <p class="text-sm text-yellow-700">
                                The full thesis document is currently not available for viewing or download.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Abstract -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Abstract</h2>
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    {!! nl2br(e($report->abstract_md)) !!}
                </div>
            </div>
        </div>

        <!-- Keywords -->
        @if($report->keywords)
            <div class="bg-white rounded-lg shadow-md mb-8">
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Keywords</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach(json_decode($report->keywords, true) ?? [] as $keyword)
                            <a href="{{ route('home', ['keywords' => $keyword]) }}" 
                               class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium hover:bg-blue-200 transition-colors">
                                {{ $keyword }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Citation -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">How to Cite</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 font-mono leading-relaxed">
                        {{ $report->group->students->pluck('student_name')->implode(', ') }}. 
                        ({{ $report->approved_at->format('Y') }}). 
                        <em>{{ $report->project_title }}</em>. 
                        [Undergraduate Thesis]. 
                        {{ config('app.name', 'University') }}.
                    </p>
                    <button onclick="copyToClipboard()" 
                            class="mt-3 inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Citation
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Reports -->
        @php
            $relatedReports = \App\Models\Report::approved()
                ->final()
                ->where('id', '!=', $report->id)
                ->where(function($query) use ($report) {
                    if ($report->keywords) {
                        $keywords = json_decode($report->keywords, true) ?? [];
                        foreach ($keywords as $keyword) {
                            $query->orWhere('keywords', 'LIKE', "%{$keyword}%");
                        }
                    }
                })
                ->with(['group.students'])
                ->limit(3)
                ->get();
        @endphp

        @if($relatedReports->count() > 0)
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Thesis Reports</h2>
                    <div class="space-y-4">
                        @foreach($relatedReports as $relatedReport)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <h3 class="font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('reports.show', $relatedReport) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $relatedReport->project_title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    By {{ $relatedReport->group->students->pluck('student_name')->implode(', ') }}
                                </p>
                                <p class="text-sm text-gray-700">
                                    {{ Str::limit(strip_tags($relatedReport->abstract_md), 120) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Thesis Repository') }}. All rights reserved.</p>
                <p class="mt-2 text-sm">A digital repository for academic thesis and research projects.</p>
            </div>
        </div>
    </footer>

    <script>
        function copyToClipboard() {
            const citation = `{{ $report->group->students->pluck('student_name')->implode(', ') }}. ({{ $report->approved_at->format('Y') }}). {{ $report->project_title }}. [Undergraduate Thesis]. {{ config('app.name', 'University') }}.`;
            
            navigator.clipboard.writeText(citation).then(function() {
                // Show success message
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
                button.classList.add('bg-green-600');
                button.classList.remove('bg-blue-600');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600');
                    button.classList.add('bg-blue-600');
                }, 2000);
            });
        }
    </script>
</body>
</html>