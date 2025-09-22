@extends('layouts.co-supervisor')

@section('page-title', 'Annotation History')
@section('page-description', 'View annotation history for this submission')

@section('content')
<div class="space-y-6">
    <!-- Back Navigation -->
    <div class="flex items-center gap-4">
        <a href="{{ route('co-supervisor.reports.show', $report) }}" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Report
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Annotation History</h1>
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span><strong>File:</strong> {{ $submission->original_filename }}</span>
                <span><strong>Student:</strong> {{ $submission->student->name }}</span>
                <span><strong>Submitted:</strong> {{ $submission->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Annotation Sessions -->
    @if($annotationSessions->count() > 0)
        <div class="space-y-4">
            @foreach($annotationSessions as $session)
                <div class="bg-white rounded-lg shadow border-l-4 {{ $session->created_by_type === 'co_supervisor' ? 'border-indigo-500' : 'border-blue-500' }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Version {{ $session->version }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $session->created_by_type === 'co_supervisor' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $session->created_by_type === 'co_supervisor' ? 'Co-Supervisor' : 'Main Supervisor' }}
                                    </span>
                                    @if($session->is_sent)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Sent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Draft
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span><strong>By:</strong> {{ $session->supervisor->name }}</span>
                                    <span class="ml-4"><strong>Created:</strong> {{ $session->created_at->format('M d, Y h:i A') }}</span>
                                    @if($session->is_sent && $session->sent_at)
                                        <span class="ml-4"><strong>Sent:</strong> {{ $session->sent_at->format('M d, Y h:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($session->message)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Feedback Message:</h4>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-gray-700">{{ $session->message }}</p>
                                </div>
                            </div>
                        @endif

                        @if($session->annotations_json && count($session->annotations_json) > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Annotations:</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @php
                                        $annotationCounts = collect($session->annotations_json)->groupBy('type')->map->count();
                                    @endphp
                                    @foreach($annotationCounts as $type => $count)
                                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                                            <div class="text-lg font-semibold text-gray-900">{{ $count }}</div>
                                            <div class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}{{ $count > 1 ? 's' : '' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <a href="{{ route('co-supervisor.reports.submissions.annotate', [$report, $submission]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                View/Edit Annotations
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Annotations Yet</h3>
                    <p class="text-gray-600 mb-4">No annotations have been created for this submission yet.</p>
                    <a href="{{ route('co-supervisor.reports.submissions.annotate', [$report, $submission]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Start Annotating
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection