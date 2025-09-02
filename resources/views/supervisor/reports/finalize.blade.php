@extends('layouts.supervisor')

@section('page-title', 'Finalize Report')
@section('page-description', 'Review and approve the final report')

@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Finalize Report</h2>
                <p class="text-gray-600">
                    Review and approve the final report for: <strong>{{ $report->group->name }}</strong>
                </p>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Student Submissions Summary -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">Student Submissions Summary</h3>
                <div class="space-y-2">
                    @foreach($report->submissions as $submission)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-blue-700">
                                <strong>{{ $submission->student->name }}:</strong> {{ $submission->subject }}
                            </span>
                            <span class="text-blue-600">{{ $submission->created_at->format('M d, Y') }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-blue-600 mt-2">
                    Total submissions: {{ $report->submissions->count() }}
                </p>
            </div>

            <form action="{{ route('supervisor.reports.finalize.store', $report) }}" method="POST">
                @csrf

                <!-- Project Title -->
                <div class="mb-6">
                    <label for="project_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Project Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="project_title" id="project_title" required
                           value="{{ old('project_title', $report->project_title) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('project_title') border-red-500 @enderror"
                           placeholder="Enter the final project title">
                    @error('project_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Abstract -->
                <div class="mb-6">
                    <label for="abstract_md" class="block text-sm font-medium text-gray-700 mb-2">
                        Abstract <span class="text-red-500">*</span>
                    </label>
                    <textarea name="abstract_md" id="abstract_md" rows="10" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('abstract_md') border-red-500 @enderror"
                              placeholder="Enter the final project abstract...">{{ old('abstract_md', $report->abstract_md) }}</textarea>
                    @error('abstract_md')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">This will be the abstract that appears in the public thesis repository.</p>
                </div>

                <!-- Keywords -->
                <div class="mb-6">
                    <label for="keywords" class="block text-sm font-medium text-gray-700 mb-2">
                        Keywords
                    </label>
                    @php
                        $keywordsString = '';
                        if ($report->keywords) {
                            $keywordsArray = json_decode($report->keywords, true);
                            if (is_array($keywordsArray)) {
                                $keywordsString = implode(', ', $keywordsArray);
                            }
                        }
                    @endphp
                    <input type="text" name="keywords" id="keywords" 
                           value="{{ old('keywords', $keywordsString) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('keywords') border-red-500 @enderror"
                           placeholder="Enter keywords separated by commas (e.g., machine learning, AI, neural networks)">
                    @error('keywords')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">These keywords will help others find this thesis in searches.</p>
                </div>

                <!-- Approval Confirmation -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800 mb-1">Important Notice</h4>
                            <p class="text-sm text-yellow-700">
                                By approving this report, you confirm that:
                            </p>
                            <ul class="text-sm text-yellow-700 mt-2 list-disc list-inside space-y-1">
                                <li>The student work meets the required standards</li>
                                <li>The project title and abstract are accurate and final</li>
                                <li>The report will be published in the public thesis repository</li>
                                <li>Students will be notified of the approval</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('supervisor.reports.show', $report) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        Approve & Publish Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection