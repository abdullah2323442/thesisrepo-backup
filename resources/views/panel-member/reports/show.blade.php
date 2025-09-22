@extends('layouts.panel-member')

@section('page-title', 'Report Evaluation')
@section('page-description', 'Evaluate report and provide expert feedback')

@section('content')
<div class="space-y-6">
    <!-- Back Navigation -->
    <div class="flex items-center gap-4">
        <a href="{{ route('panel-member.reports.index') }}" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Reports
        </a>
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ $report->project_title ?: 'Report #' . $report->id }}
                    </h1>
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <span><strong>Group:</strong> {{ $report->group->name }}</span>
                        <span><strong>Created:</strong> {{ $report->created_at->format('M d, Y') }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($report->status === 'approved') bg-green-100 text-green-800
                            @elseif($report->status === 'under_review') bg-yellow-100 text-yellow-800
                            @elseif($report->status === 'draft') bg-gray-100 text-gray-800
                            @else bg-blue-100 text-blue-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    @if($report->status === 'draft')
                        <button onclick="markUnderReview({{ $report->id }})" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Mark Under Review
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Report Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Group Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Group Information</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Group:</span>
                                <span class="text-gray-900">{{ $report->group->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Batch:</span>
                                <span class="text-gray-900">{{ $report->group->batch_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Main Supervisor:</span>
                                <span class="text-gray-900">{{ $report->group->supervisor ? $report->group->supervisor->fullname : 'Not assigned' }}</span>
                            </div>
                            @if($report->group->coSupervisor)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Co-Supervisor:</span>
                                    <span class="text-gray-900">{{ $report->group->coSupervisor->fullname }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Advisor:</span>
                                <span class="text-gray-900">{{ $report->group->advisor ? $report->group->advisor->name : 'Not assigned' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Students -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Students</h3>
                        <div class="space-y-2">
                            @foreach($report->group->students as $student)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="font-medium text-gray-900">{{ $student->student_name }}</div>
                                    <div class="text-sm text-gray-600">Roll: {{ $student->student_id }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Report Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Report Details</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            @if($report->project_title)
                                <div>
                                    <span class="font-medium text-gray-700">Project Title:</span>
                                    <p class="text-gray-900 mt-1">{{ $report->project_title }}</p>
                                </div>
                            @endif
                            
                            @if($report->description)
                                <div>
                                    <span class="font-medium text-gray-700">Description:</span>
                                    <p class="text-gray-900 mt-1">{{ $report->description }}</p>
                                </div>
                            @endif
                            
                            <div>
                                <span class="font-medium text-gray-700">Due Date:</span>
                                <p class="text-gray-900 mt-1">{{ $report->due_date ? $report->due_date->format('M d, Y') : 'Not set' }}</p>
                            </div>
                            
                            <div>
                                <span class="font-medium text-gray-700">Submissions:</span>
                                <p class="text-gray-900 mt-1">{{ $report->submissions->count() }} submission(s)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Member Role Notice -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-orange-800">Panel Member Evaluation</h4>
                                <p class="text-sm text-orange-700 mt-1">
                                    Provide expert evaluation and feedback from your area of expertise. Your input helps ensure quality and academic standards.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Members List -->
                    @if($report->group->panelMembers->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Panel Members</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="space-y-2">
                                    @foreach($report->group->panelMembers as $panelMember)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-900">{{ $panelMember->supervisor->fullname }}</span>
                                            @if($panelMember->supervisor_id === $supervisor->id)
                                                <span class="text-xs px-2 py-1 bg-orange-100 text-orange-800 rounded-full font-medium">You</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Student Submissions -->
    @if($report->submissions->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Submissions</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($report->submissions as $submission)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $submission->original_filename }}</h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span>Submitted: {{ $submission->created_at->format('M d, Y h:i A') }}</span>
                                        @if($submission->file_size)
                                            <span class="ml-4">Size: {{ number_format($submission->file_size / 1024, 2) }} KB</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('panel-member.reports.submissions.view', [$report, $submission]) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('panel-member.reports.submissions.download', [$report, $submission]) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Submissions</h3>
            </div>
            <div class="p-6">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Submissions Yet</h3>
                    <p class="text-gray-600">Students haven't submitted any files for this report yet.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Comments Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Expert Evaluation & Feedback</h3>
        </div>
        <div class="p-6">
            @if($report->comments->count() > 0)
                <div class="space-y-4 mb-6">
                    @foreach($report->comments as $comment)
                        <div class="border-l-4 border-orange-400 pl-4 py-2">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium text-gray-900">{{ $comment->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $comment->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <p class="text-gray-700">{{ $comment->comment }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Add Comment Form -->
            <form action="{{ route('teacher.reports.comments.store', $report) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Expert Evaluation</label>
                    <textarea name="comment" id="comment" rows="4" required
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500"
                              placeholder="Provide your expert evaluation and recommendations from your area of expertise..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Add Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div id="error-message" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
@endif
@endsection

@push('scripts')
<script>
function markUnderReview(reportId) {
    if (!confirm('Mark this report as under review?')) {
        return;
    }

    fetch(`/panel-member/reports/${reportId}/under-review`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Reload page to update status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.error || 'Failed to update report status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating the report', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Auto-hide messages
setTimeout(() => {
    const successMsg = document.getElementById('success-message');
    const errorMsg = document.getElementById('error-message');
    if (successMsg) successMsg.remove();
    if (errorMsg) errorMsg.remove();
}, 3000);
</script>
@endpush