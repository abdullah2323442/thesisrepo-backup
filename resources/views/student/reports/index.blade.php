@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Thesis Reports</h1>

    @if(!$hasGroup)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            <p>{{ $message }}</p>
        </div>
    @else
        <div class="mb-4">
            <p class="text-gray-600">Group: <strong>{{ $groupName }}</strong></p>
        </div>

        @if($reports->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Reports Yet</h3>
                <p class="text-gray-600">Your supervisor hasn't created any reports for your group yet.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($reports as $report)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-3 py-1 text-sm font-semibold rounded {{ $report->type === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($report->type) }} Report
                                    </span>
                                    @if($report->project_title)
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $report->project_title }}</h3>
                                    @endif
                                </div>
                                
                                @if($report->abstract_md && $report->type === 'final')
                                    <p class="text-gray-600 mb-2">{{ \Str::limit(strip_tags($report->abstract_md), 150) }}</p>
                                @endif
                                
                                @if($report->extra_input)
                                    <div class="bg-yellow-50 p-2 rounded mb-2">
                                        <p class="text-sm text-gray-700">Note: {{ \Str::limit($report->extra_input, 100) }}</p>
                                    </div>
                                @endif
                                
                                <div class="flex items-center gap-4 text-sm text-gray-500 mt-2">
                                    <span>Created {{ $report->created_at->diffForHumans() }}</span>
                                    <span>By {{ $report->creator->name }}</span>
                                    @if($report->comments->count() > 0)
                                        <span class="text-blue-600">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                            {{ $report->comments->count() }} {{ Str::plural('comment', $report->comments->count()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('student.reports.show', $report) }}" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection