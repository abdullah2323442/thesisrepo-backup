@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Submission Details</h2>
                    <p class="text-gray-600">
                        For report: <strong>{{ $report->project_title ?: 'Report for ' . $report->group->name }}</strong>
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('student.reports.submissions.annotations.history', [$report, $submission]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        View Annotations
                    </a>
                    <a href="{{ route('student.reports.submissions.edit', [$report, $submission]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Submission
                    </a>
                    <a href="{{ route('student.reports.show', $report) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Report
                    </a>
                </div>
            </div>

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

            <!-- Submission Information -->
            <div class="space-y-6">
                <!-- Subject -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Subject</h3>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $submission->subject }}</p>
                </div>

                <!-- Description -->
                @if($submission->description)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $submission->description }}</p>
                    </div>
                @endif

                <!-- File Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Submitted File</h3>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @php
                                    $extension = pathinfo($submission->original_filename, PATHINFO_EXTENSION);
                                    $isPdf = strtolower($extension) === 'pdf';
                                    $isPpt = in_array(strtolower($extension), ['ppt', 'pptx']);
                                    
                                    if ($isPdf) {
                                        $fileType = 'PDF Document';
                                        $iconColor = 'text-red-600';
                                        $bgColor = 'bg-red-100';
                                    } elseif ($isPpt) {
                                        $fileType = 'PowerPoint Presentation';
                                        $iconColor = 'text-orange-600';
                                        $bgColor = 'bg-orange-100';
                                    } else {
                                        $fileType = 'Document';
                                        $iconColor = 'text-gray-600';
                                        $bgColor = 'bg-gray-100';
                                    }
                                @endphp
                                
                                <div class="w-10 h-10 {{ $bgColor }} rounded-lg flex items-center justify-center mr-3">
                                    @if($isPdf)
                                        <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                                        </svg>
                                    @elseif($isPpt)
                                        <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $submission->original_filename }}</p>
                                    <p class="text-sm text-gray-600">{{ $submission->formatted_file_size }} • {{ $fileType }}</p>
                                </div>
                            </div>
                            <a href="{{ route('student.reports.submissions.download', [$report, $submission]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Submission Metadata -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Submission Details</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Submitted By</p>
                                <p class="text-gray-600">{{ $submission->student->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Submission Date</p>
                                <p class="text-gray-600">{{ $submission->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @if($submission->updated_at != $submission->created_at)
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Last Updated</p>
                                    <p class="text-gray-600">{{ $submission->updated_at->format('M d, Y h:i A') }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-700">Report Type</p>
                                <p class="text-gray-600">{{ ucfirst($report->type) }} Report</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <form action="{{ route('student.reports.submissions.destroy', [$report, $submission]) }}" 
                          method="POST" class="inline-block"
                          onsubmit="return confirm('Are you sure you want to delete this submission? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection