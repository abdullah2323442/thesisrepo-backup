@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
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

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('info') }}
        </div>
    @endif
    <!-- Report Header -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded {{ $report->type === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($report->type) }} Report
                        </span>
                        <h1 class="text-2xl font-bold text-gray-800">
                            {{ $report->project_title ?: 'Report for ' . $report->group->name }}
                        </h1>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span>Created by: {{ $report->creator->name }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ $report->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                <a href="{{ route('student.reports.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Reports
                </a>
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
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Instructions from Supervisor</h3>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800 mb-1">What you need to do:</p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
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

    <!-- Student Submission Section -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Your Submission</h3>
                @if(!$currentSubmission)
                    <a href="{{ route('student.reports.submissions.create', $report) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Submit Report
                    </a>
                @endif
            </div>

            @if($currentSubmission)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 mb-1">{{ $currentSubmission->subject }}</h4>
                            @if($currentSubmission->description)
                                <p class="text-gray-600 text-sm mb-2">{{ $currentSubmission->description }}</p>
                            @endif
                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                <span>Submitted {{ $currentSubmission->created_at->diffForHumans() }}</span>
                                <span>{{ $currentSubmission->formatted_file_size }}</span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    PDF
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <a href="{{ route('student.reports.submissions.download', [$report, $currentSubmission]) }}" 
                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                            <a href="{{ route('student.reports.submissions.edit', [$report, $currentSubmission]) }}" 
                               class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('student.reports.submissions.destroy', [$report, $currentSubmission]) }}" 
                                  method="POST" class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete your submission? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Submission Yet</h4>
                    <p class="text-gray-600 mb-4">You haven't submitted your report yet. Click the button above to submit your work.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Comments Section -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Supervisor Comments & Feedback</h3>

            @if($report->comments->count() > 0)
                <div class="space-y-4">
                    @foreach($report->comments as $comment)
                        <div class="border-l-4 border-blue-500 pl-4 py-3 bg-gray-50 rounded">
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
                <p class="text-gray-500 italic">No comments from supervisor yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection