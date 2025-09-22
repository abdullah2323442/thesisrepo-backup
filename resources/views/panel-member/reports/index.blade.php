@extends('layouts.panel-member')

@section('page-title', 'Report Management')
@section('page-description', 'Evaluate and provide feedback on student reports')

@section('content')
<div class="space-y-6">
    <!-- Role Information Banner -->
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-orange-800">Panel Member Report Access</h3>
                <p class="text-sm text-orange-700 mt-1">
                    You can evaluate reports and provide expert feedback from your area of expertise. 
                    <strong>Only the main supervisor can create, edit, delete, or approve final reports.</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Student Reports</h3>
                <div class="text-sm text-gray-600">
                    {{ $reports->total() }} reports from {{ $groups->count() }} panel member groups
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($reports->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Reports Available</h3>
                    <p class="text-gray-600">No reports have been created for your panel member groups yet.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($reports as $report)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            {{ $report->project_title ?: 'Report #' . $report->id }}
                                        </h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($report->status === 'approved') bg-green-100 text-green-800
                                            @elseif($report->status === 'under_review') bg-yellow-100 text-yellow-800
                                            @elseif($report->status === 'draft') bg-gray-100 text-gray-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div>
                                            <p><span class="font-medium">Group:</span> {{ $report->group->name }}</p>
                                            <p><span class="font-medium">Students:</span> {{ $report->group->students->count() }}</p>
                                            <p><span class="font-medium">Main Supervisor:</span> {{ $report->group->supervisor ? $report->group->supervisor->fullname : 'Not assigned' }}</p>
                                        </div>
                                        <div>
                                            <p><span class="font-medium">Created:</span> {{ $report->created_at->format('M d, Y') }}</p>
                                            <p><span class="font-medium">Submissions:</span> {{ $report->submissions->count() }}</p>
                                            @if($report->group->coSupervisor)
                                                <p><span class="font-medium">Co-Supervisor:</span> {{ $report->group->coSupervisor->fullname }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if($report->description)
                                        <p class="text-sm text-gray-700 mt-2">{{ Str::limit($report->description, 150) }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 ml-4">
                                    <a href="{{ route('panel-member.reports.show', $report) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Evaluate
                                    </a>

                                    @if($report->submissions->count() > 0)
                                        @php $latestSubmission = $report->submissions->sortByDesc('created_at')->first(); @endphp
                                        <a href="{{ route('panel-member.reports.submissions.annotate', [$report, $latestSubmission]) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8l4 4"></path>
                                            </svg>
                                            Annotate
                                        </a>
                                    @endif

                                    @if($report->status === 'draft')
                                        <button onclick="markUnderReview({{ $report->id }})" 
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            Mark Under Review
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Panel Member Groups Summary -->
    @if($groups->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Your Panel Member Groups</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($groups as $group)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $group->name }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><span class="font-medium">Batch:</span> {{ $group->batch_number }}</p>
                                <p><span class="font-medium">Students:</span> {{ $group->students->count() }}</p>
                                <p><span class="font-medium">Main Supervisor:</span> {{ $group->supervisor ? $group->supervisor->fullname : 'Not assigned' }}</p>
                                @if($group->coSupervisor)
                                    <p><span class="font-medium">Co-Supervisor:</span> {{ $group->coSupervisor->fullname }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
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