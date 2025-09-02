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
                        <span class="px-3 py-1 text-sm font-semibold rounded {{ $report->type === 'final' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($report->type) }} Report
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
                    <a href="{{ route('supervisor.reports.edit', $report) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Report
                    </a>
                    <form action="{{ route('supervisor.reports.destroy', $report) }}" method="POST" class="inline-block" 
                          onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone and will also delete all comments.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Report
                        </button>
                    </form>
                    <a href="{{ route('supervisor.reports.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
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
                        <div class="prose max-w-none bg-gray-50 p-4 rounded-lg" id="abstract-content">
                            {!! \Illuminate\Support\Str::markdown($report->abstract_md) !!}
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
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
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

    <!-- Student Submissions Section -->
    @if($report->submissions->count() > 0)
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Student Submissions</h3>
                
                <div class="space-y-4">
                    @foreach($report->submissions as $submission)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-semibold text-gray-800">{{ $submission->subject }}</h4>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Submitted</span>
                                    </div>
                                    
                                    @if($submission->description)
                                        <p class="text-gray-600 text-sm mb-2">{{ $submission->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $submission->student->name }}
                                        </span>
                                        <span>{{ $submission->created_at->diffForHumans() }}</span>
                                        <span>{{ $submission->formatted_file_size }}</span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $submission->original_filename }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="ml-4">
                                    <a href="{{ asset('storage/' . $submission->file_path) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View PDF
                                    </a>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
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
@endsection