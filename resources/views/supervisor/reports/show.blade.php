@extends('layouts.supervisor')

@section('page-title', 'Report Details')
@section('page-description', 'View report details and comments')

@section('content')
<div class="container mx-auto max-w-5xl">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded {{ $report->type === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($report->type) }} Report
                        </span>
                        <span class="px-3 py-1 text-sm font-semibold rounded {{ $report->status_badge_color }}">
                            {{ $report->formatted_status }}
                        </span>
                        <h1 class="text-2xl font-bold text-gray-800">
                            {{ $report->project_title ?: 'Report for ' . $report->group->name }}
                        </h1>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span>Group: <strong>{{ $report->group->name }}</strong></span>
                        <span class="mx-2">•</span>
                        <span>Created by: {{ $report->creator->name }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ $report->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- <a href="{{ route('supervisor.reports.edit', $report) }}"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit Report
                    </a> -->
                    <form action="{{ route('supervisor.reports.destroy', $report) }}" method="POST" class="inline-block"
                        onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone and will also delete all comments.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Delete Report
                        </button>
                    </form>
                    <a href="{{ route('supervisor.reports.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Reports
                    </a>
                </div>
            </div>

            <!-- Report Content -->
            @if($report->isFinal())
            <!-- Abstract -->
            @if($report->abstract_md)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Abstract</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    {!! nl2br(e($report->abstract_md)) !!}
                </div>
            </div>
            @endif

            <!-- Keywords -->
            @if($report->keywords)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Keywords</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach(json_decode($report->keywords, true) ?? [] as $keyword)
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        {{ $keyword }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

            <!-- Supervisor Message -->
            @if($report->supervisor_message)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Message to Students</h3>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800 mb-1">Instructions for Students:</p>
                            <p class="text-blue-700">{{ $report->supervisor_message }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Notes -->
            @if($report->extra_input)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Additional Notes</h3>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <p class="text-gray-700">{{ $report->extra_input }}</p>
                </div>
            </div>
            @endif

            <!-- Group Members -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Group Members</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($report->group->students as $student)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-800">{{ $student->student_name }}</p>
                            <p class="text-sm text-gray-600">ID: {{ $student->student_id }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Report Approval Actions -->
    @if($report->canBeApproved())
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Report Approval</h3>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                        </path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-green-800 mb-1">Ready for Approval</h4>
                        <p class="text-sm text-green-700 mb-3">
                            This final report has student submissions and is ready for your approval. Once approved, it
                            will be published in the thesis repository.
                        </p>
                        <div class="flex items-center gap-3">
                            @if($report->hasSubmissions() && !$report->isUnderReview())
                            <form action="{{ route('supervisor.reports.under-review', $report) }}" method="POST"
                                class="inline-block">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    Mark Under Review
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('supervisor.reports.finalize', $report) }}"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                    </path>
                                </svg>
                                Approve & Finalize Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif($report->isApproved())
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Report Status</h3>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                        </path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-green-800 mb-1">Report Approved</h4>
                        <p class="text-sm text-green-700">
                            This report was approved on {{ $report->approved_at->format('M d, Y h:i A') }} and is now
                            published in the thesis repository.
                        </p>
                        @if($report->approver)
                        <p class="text-xs text-green-600 mt-1">
                            Approved by: {{ $report->approver->name }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Student Submissions Section -->
    @if($report->submissions->count() > 0)
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Student Submissions</h3>

            <div class="space-y-4">
                @foreach($report->submissions as $submission)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start gap-3 flex-1">
                            <!-- File Type Icon -->
                            <div class="flex-shrink-0 mt-1">
                                @if($submission->isPdf())
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                @elseif($submission->isPowerPoint())
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                            clip-rule="evenodd"></path>
                                        <path
                                            d="M8 8a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H9a1 1 0 01-1-1V8zM8 11a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H9a1 1 0 01-1-1v-1z">
                                        </path>
                                    </svg>
                                </div>
                                @else
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                @endif
                            </div>

                            <!-- Submission Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="font-semibold text-gray-800 truncate">{{ $submission->subject }}</h4>
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full flex-shrink-0">Submitted</span>
                                </div>

                                @if($submission->description)
                                <p class="text-gray-600 text-sm mb-3">{{ $submission->description }}</p>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        <span class="font-medium">{{ $submission->student->name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $submission->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span>{{ $submission->file_type }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4">
                                            </path>
                                        </svg>
                                        <span>{{ $submission->formatted_file_size }}</span>
                                    </div>
                                </div>

                                <div class="mt-2 text-xs text-gray-400">
                                    <span class="font-mono">{{ $submission->original_filename }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                            @if($submission->isPdf())
                            <!-- Beautiful PDF View Button -->
                            <a href="{{ route('supervisor.reports.submissions.view', [$report, $submission]) }}"
                                target="_blank"
                                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl shadow-md hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                View PDF
                            </a>

                            <!-- Annotate PDF Button -->
                            <a href="{{ route('supervisor.reports.submissions.annotate', [$report, $submission]) }}"
                                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                                title="Annotate this PDF">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Annotate
                            </a>

                            <!-- Annotation History Button -->
                            <a href="{{ route('supervisor.reports.submissions.annotations.history', [$report, $submission]) }}"
                                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors"
                                title="View annotation history">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                History
                            </a>

                            <!-- PDF Download Button -->
                            <a href="{{ route('supervisor.reports.submissions.download', [$report, $submission]) }}"
                                class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors"
                                title="Download PDF file">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Download
                            </a>
                            @elseif($submission->isPowerPoint())
                            <!-- PowerPoint Download Button -->
                            <a href="{{ route('supervisor.reports.submissions.download', [$report, $submission]) }}"
                                class="inline-flex items-center px-3 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition-colors"
                                title="Download PowerPoint presentation">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Download PPT
                            </a>
                            @else
                            <!-- Generic File Download Button -->
                            <a href="{{ route('supervisor.reports.submissions.download', [$report, $submission]) }}"
                                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
                                title="Download file">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Download
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Comments Section -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Comments & Feedback</h3>

            <!-- Add Comment Form -->
            <form action="{{ route('teacher.reports.comments.store', $report) }}" method="POST" class="mb-6">
                @csrf
                <div class="mb-3">
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                        Add a Comment
                    </label>
                    <textarea name="body" id="body" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('body') border-red-500 @enderror"
                        placeholder="Enter your feedback or comments for the students...">{{ old('body') }}</textarea>
                    @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Post Comment
                </button>
            </form>

            <!-- Comments List -->
            @if($report->comments->count() > 0)
            <div class="space-y-4">
                @foreach($report->comments as $comment)
                <div class="border-l-4 border-blue-500 pl-4 py-3">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="font-medium text-gray-800">{{ $comment->teacher->name }}</span>
                            <span class="text-sm text-gray-600 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p class="text-gray-700">{{ $comment->body }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 italic">No comments yet. Be the first to provide feedback!</p>
            @endif
        </div>
    </div>
</div>

<script>
function openPowerPointOnline(fileUrl, filename) {
    // Try multiple approaches to view PowerPoint files

    // Option 1: Try Microsoft Office Online Viewer
    const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;

    // Option 2: Try Google Docs Viewer as fallback
    const googleViewerUrl = `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;

    // Create a modal to show viewing options
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">View PowerPoint Presentation</h3>
            <p class="text-gray-600 mb-4">Choose how you'd like to view "${filename}":</p>
            <div class="space-y-3">
                <button onclick="window.open('${officeViewerUrl}', '_blank'); closeModal()" 
                        class="w-full px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition-colors">
                    View with Microsoft Office Online
                </button>
                <button onclick="window.open('${googleViewerUrl}', '_blank'); closeModal()" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    View with Google Docs Viewer
                </button>
                <button onclick="window.open('${fileUrl}', '_blank'); closeModal()" 
                        class="w-full px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    Download File
                </button>
            </div>
            <button onclick="closeModal()" 
                    class="mt-4 w-full px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">
                Cancel
            </button>
        </div>
    `;

    document.body.appendChild(modal);

    function closeModal() {
        document.body.removeChild(modal);
    }

    // Make closeModal available globally for the modal buttons
    window.closeModal = closeModal;
}

// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });
});
</script>
@endsection