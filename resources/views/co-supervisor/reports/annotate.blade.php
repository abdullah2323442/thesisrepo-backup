@extends('layouts.co-supervisor')

@section('page-title', 'PDF Annotation Studio')
@section('page-description', 'Professional PDF annotation and feedback system')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20">
    <div class="container mx-auto max-w-7xl px-4 py-6">
        <!-- Professional Header -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 mb-8">
            <div class="px-8 py-6">
                <div class="flex justify-between items-start">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8l4 4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-2">
                                PDF Annotation Studio
                            </h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span><strong class="text-gray-800">{{ $submission->subject }}</strong></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $submission->student->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11h6m-6 4h6"></path>
                                    </svg>
                                    <span>{{ $report->project_title ?: 'Report for ' . $report->group->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        <circle cx="12" cy="12" r="1" fill="currentColor"></circle>
                                    </svg>
                                    <span>{{ $submission->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('co-supervisor.reports.show', $report) }}"
                            class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 12H7"></path>
                            </svg>
                            Back to Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <!-- Professional Annotation Interface -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/30 overflow-hidden">
            <!-- Advanced Toolbar -->
            <div class="bg-gradient-to-r from-slate-800 via-slate-700 to-slate-800 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2zm4 2h10v10H7V7z" opacity="0.3"/>
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7V7h10v6z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Annotation Tools</h3>
                        </div>
                        
                        <!-- Tool Palette -->
                        <div class="flex items-center gap-2 bg-white/10 rounded-xl p-2 backdrop-blur-sm">
                            <button id="highlight-btn" class="group relative px-4 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white rounded-lg hover:from-amber-600 hover:to-yellow-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h10m-7 4h4"></path>
                                    <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.5" opacity="0.3"></rect>
                                </svg>
                                <span class="font-medium">Highlight</span>
                                <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">H</div>
                            </button>
                            
                            <button id="comment-btn" class="group relative px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    <circle cx="8" cy="12" r="0.5" fill="currentColor"></circle>
                                    <circle cx="12" cy="12" r="0.5" fill="currentColor"></circle>
                                    <circle cx="16" cy="12" r="0.5" fill="currentColor"></circle>
                                </svg>
                                <span class="font-medium">Comment</span>
                                <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">C</div>
                            </button>
                            
                            <button id="underline-btn" class="group relative px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8v8a5 5 0 0010 0V8"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 21h14" stroke-width="2.5"></path>
                                </svg>
                                <span class="font-medium">Underline</span>
                                <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">U</div>
                            </button>
                            
                            <button id="strikethrough-btn" class="group relative px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-lg hover:from-red-600 hover:to-rose-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 12h-11"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5c2.5 0 4.5 1.5 4.5 3.5M12 17.5c-2.5 0-4.5-1.5-4.5-3.5" opacity="0.6"></path>
                                </svg>
                                <span class="font-medium">Strike</span>
                                <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">X</div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Action Controls -->
                    <div class="flex items-center gap-3">
                        <!-- History Controls -->
                        <div class="flex items-center gap-1 bg-white/10 rounded-lg p-1">
                            <button id="undo-btn" class="group px-3 py-2 text-white/80 hover:text-white hover:bg-white/10 rounded-md transition-all duration-200" title="Undo (Ctrl+Z)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </button>
                            <button id="redo-btn" class="group px-3 py-2 text-white/80 hover:text-white hover:bg-white/10 rounded-md transition-all duration-200" title="Redo (Ctrl+Y)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Zoom Controls -->
                        <div class="hidden md:flex items-center gap-2 bg-white/10 rounded-lg px-3 py-2">
                            <button id="zoom-out-btn" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded transition-all" title="Zoom Out (-)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span id="zoom-level" class="text-sm text-white/90 font-medium w-12 text-center">150%</span>
                            <button id="zoom-in-btn" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded transition-all" title="Zoom In (+)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Primary Actions -->
                        <div class="flex items-center gap-2">
                            <button id="save-btn" class="group px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl hover:from-emerald-700 hover:to-green-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
                                <svg class="w-4 h-4 mr-2 inline group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 3v4h4"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v6"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l3 3l3-3"></path>
                                </svg>
                                Save
                            </button>
                            <button id="send-feedback-btn" class="group px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
                                <svg class="w-4 h-4 mr-2 inline group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 2l-7 20-4-9-9-4 20-7z"></path>
                                </svg>
                                Send Feedback
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional PDF Viewer with Enhanced Sidebar -->
            <div class="grid grid-cols-12 h-full" style="height: 850px;">
                <!-- Enhanced Sidebar -->
                <aside class="col-span-3 bg-gradient-to-b from-slate-50 to-slate-100/50 border-r border-slate-200/60 h-full overflow-hidden flex flex-col">
                    <!-- Sidebar Header -->
                    <div class="px-6 py-5 border-b border-slate-200/60 bg-white/50 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-gradient-to-br from-slate-600 to-slate-700 rounded-md flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-slate-800">Document Pages</h4>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-full">
                                <span class="text-xs font-medium text-slate-600"><span id="currentPageDisplay">1</span></span>
                                <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                                <span class="text-xs text-slate-500"><span id="totalPagesDisplay">1</span></span>
                            </div>
                        </div>
                        
                        <!-- Enhanced Page Slider -->
                        <div class="relative">
                            <input id="pageSlider" type="range" min="1" max="1" value="1" 
                                class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer slider-thumb">
                            <div class="flex justify-between text-xs text-slate-500 mt-1">
                                <span>1</span>
                                <span id="totalPagesSlider">1</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Page Index with Enhanced Styling -->
                    <div id="pageIndex" class="flex-1 overflow-auto p-3 space-y-2">
                        <!-- Page index buttons populated by JS -->
                    </div>
                </aside>

                <!-- Enhanced PDF Container -->
                <div id="pdf-container" class="col-span-9 h-full bg-gradient-to-br from-slate-100 via-slate-50 to-white overflow-auto">
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center">
                            <div class="relative">
                                <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-6"></div>
                                <div class="absolute inset-0 w-16 h-16 border-4 border-transparent border-t-indigo-400 rounded-full animate-spin mx-auto" style="animation-delay: -0.15s; animation-duration: 1.5s;"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 mb-2">Loading PDF Document</h3>
                            <p class="text-slate-500">Preparing your annotation workspace...</p>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        
        <!-- Enhanced Feedback Section -->
        <div class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-white">Comprehensive Feedback</h4>
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Overall Feedback Message
                            </label>
                            <textarea id="feedback-message" rows="6" 
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/50 backdrop-blur-sm resize-none"
                                placeholder="Provide comprehensive feedback on the student's work. Include strengths, areas for improvement, and specific recommendations..."></textarea>
                            <div class="flex items-center justify-between mt-3">
                                <p class="text-xs text-slate-500">This message will be sent to all group members along with your annotations.</p>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Auto-saved as draft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles for Enhanced UI -->
<style>
.slider-thumb::-webkit-slider-thumb {
    appearance: none;
    height: 18px;
    width: 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B82F6, #6366F1);
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    transition: all 0.2s ease;
}

.slider-thumb::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

.slider-thumb::-moz-range-thumb {
    height: 18px;
    width: 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B82F6, #6366F1);
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(148, 163, 184, 0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #94A3B8, #64748B);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #64748B, #475569);
}

/* Smooth animations */
* {
    transition: all 0.2s ease;
}

/* Enhanced button hover effects */
button:hover {
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}
</style>

<!-- Enhanced Comment Modal -->
<div id="commentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="commentModalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Add Annotation Comment</h3>
                </div>
                <button id="commentModalClose" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Comment Text</label>
                    <textarea id="commentInput" rows="4"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50/50 resize-none"
                        placeholder="Enter your detailed comment or feedback for this section..."></textarea>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>This comment will be visible to students as a floating annotation</span>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex items-center justify-end gap-3">
            <button id="commentCancelBtn" class="px-6 py-2.5 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 font-medium">
                Cancel
            </button>
            <button id="commentSaveBtn" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Add Comment
            </button>
        </div>
    </div>
</div>

<!-- Professional Send Feedback Confirmation Modal -->
<div id="sendFeedbackModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0" id="sendFeedbackModalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 rounded-t-2xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Send Feedback to Students</h3>
                    <p class="text-blue-100 text-sm mt-1">Confirm delivery of your annotations and feedback</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="space-y-5">
                <!-- Confirmation Message -->
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Ready to send your feedback?</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Your annotations and feedback will be delivered to all group members. They will receive notifications and can immediately view your detailed feedback on their submission.
                        </p>
                    </div>
                </div>

                <!-- Feedback Summary -->
                <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-slate-600 to-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h5 class="font-semibold text-gray-800">Feedback Summary</h5>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span class="text-gray-600">Annotations: <span id="modalAnnotationCount" class="font-semibold text-gray-800">0</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-gray-600">Pages: <span id="modalPageCount" class="font-semibold text-gray-800">0</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            <span class="text-gray-600">Comments: <span id="modalCommentCount" class="font-semibold text-gray-800">0</span></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                            <span class="text-gray-600">Group Members: <span class="font-semibold text-gray-800">{{ $report->group->students->count() ?? 'Multiple' }}</span></span>
                        </div>
                    </div>
                </div>

                <!-- Feedback Message Preview -->
                <div id="feedbackMessagePreview" class="bg-blue-50 rounded-xl p-4 border border-blue-200 hidden">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h6 class="font-medium text-blue-800 mb-1">Your Feedback Message:</h6>
                            <p id="feedbackMessageText" class="text-blue-700 text-sm leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                <!-- No Message Warning -->
                <div id="noMessageWarning" class="bg-amber-50 rounded-xl p-4 border border-amber-200 hidden">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h6 class="font-medium text-amber-800 mb-1">No General Feedback Message</h6>
                            <p class="text-amber-700 text-sm">You haven't provided a general feedback message. Students will only see your annotations.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Impact -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h6 class="font-medium text-green-800 mb-1">What happens next?</h6>
                            <ul class="text-green-700 text-sm space-y-1">
                                <li class="flex items-center gap-2">
                                    <div class="w-1 h-1 bg-green-500 rounded-full"></div>
                                    <span>Students receive instant notifications</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1 h-1 bg-green-500 rounded-full"></div>
                                    <span>Annotations become visible in their dashboard</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1 h-1 bg-green-500 rounded-full"></div>
                                    <span>They can view and download annotated PDF</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex items-center justify-end gap-3">
            <button id="cancelSendFeedback" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 font-medium">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </button>
            <button id="confirmSendFeedback" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Send Feedback
            </button>
        </div>
    </div>
</div>

<!-- Enhanced Toast Container -->
<div id="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 hidden">
    <div id="toast-content" class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-3 rounded-xl shadow-2xl border border-white/10 backdrop-blur-sm flex items-center gap-3 transform transition-all duration-300 translate-y-2 opacity-0">
        <div id="toast-icon" class="w-5 h-5 flex-shrink-0"></div>
        <span id="toast-text" class="font-medium"></span>
    </div>
</div>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// PDF.js setup
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// PDF file URL - Updated for co-supervisor route
const pdfUrl = '{{ route('co-supervisor.reports.submissions.view', [$report, $submission]) }}';

// Annotation state
let currentTool = null;
let annotations = [];
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let scale = 1.5;
let canvas = null;
let ctx = null;
let wrapper = null;
let overlay = null; // HTML overlay layer for live comments
let pendingComment = null; // { x, y, page }
let pagesMap = new Map();
let undoStack = [];
let redoStack = [];
let pagesContainerRoot = null;
let pageIndexEl = null;
let pageSliderEl = null;
let currentVisiblePage = 1;
let pageObserver = null;
let autosaveTimer = null;
let autosaveDelay = 1500; // ms
let isSaving = false;
const DRAFT_KEY = 'annotator_draft_{{$report->id}}_{{$submission->id}}';
let isDirty = false;

// Initialize PDF viewer
async function initPDF() {
    try {
        // Load PDF
        pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
        
        // Create canvas
        canvas = document.createElement('canvas');
        canvas.id = 'pdf-canvas';
        canvas.style.cursor = 'crosshair';
        ctx = canvas.getContext('2d');
        
        // Replace loading content
        const container = document.getElementById('pdf-container');
        container.innerHTML = '';

        // Create multi-page container
        const pagesContainer = document.createElement('div');
        pagesContainer.id = 'pagesContainer';
        pagesContainer.className = 'w-full h-full space-y-6 p-4 snap-y snap-mandatory';
        container.appendChild(pagesContainer);

        // Build each page view
        pagesMap = new Map();
        for (let p = 1; p <= pdfDoc.numPages; p++) {
            const wrapperLocal = document.createElement('div');
            wrapperLocal.style.position = 'relative';
            wrapperLocal.style.width = 'fit-content';
            wrapperLocal.style.margin = '0 auto';
            wrapperLocal.id = `page-wrapper-${p}`;
            wrapperLocal.className = 'snap-start';

            const pageCanvas = document.createElement('canvas');
            pageCanvas.id = `pdf-canvas-${p}`;
            pageCanvas.style.cursor = 'crosshair';
            const pageCtx = pageCanvas.getContext('2d');

            const pageOverlay = document.createElement('div');
            pageOverlay.id = `annotation-overlay-${p}`;
            pageOverlay.style.position = 'absolute';
            pageOverlay.style.left = '0';
            pageOverlay.style.top = '0';
            pageOverlay.style.pointerEvents = 'none';

            // Annotation shapes canvas on top of PDF
            const annotationCanvas = document.createElement('canvas');
            annotationCanvas.id = `annotation-canvas-${p}`;
            annotationCanvas.style.position = 'absolute';
            annotationCanvas.style.left = '0';
            annotationCanvas.style.top = '0';
            annotationCanvas.style.pointerEvents = 'none';
            const annotationCtx = annotationCanvas.getContext('2d');

            // HTML comment boxes layer
            const boxesLayer = document.createElement('div');
            boxesLayer.id = `annotation-boxes-${p}`;
            boxesLayer.style.position = 'absolute';
            boxesLayer.style.left = '0';
            boxesLayer.style.top = '0';
            boxesLayer.style.pointerEvents = 'none';

            pageOverlay.appendChild(annotationCanvas);
            pageOverlay.appendChild(boxesLayer);

            wrapperLocal.appendChild(pageCanvas);
            wrapperLocal.appendChild(pageOverlay);
            pagesContainer.appendChild(wrapperLocal);

            pagesMap.set(p, { canvas: pageCanvas, ctx: pageCtx, overlay: pageOverlay, aCanvas: annotationCanvas, aCtx: annotationCtx, boxes: boxesLayer });

            // Render this page
            renderPageFor(p);

            // Add per-page event listeners
            addCanvasEventListenersFor(p, pageCanvas);
        }

        // Setup sidebar and observers
        setupSidebar();
        observeVisiblePages();
        
    } catch (error) {
        console.error('Error loading PDF:', error);
        document.getElementById('pdf-container').innerHTML = 
            '<div class="text-center text-red-600"><p>Error loading PDF. Please try again.</p></div>';
    }
}

// Render page
function renderPage(num) {
    pageRendering = true;
    
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        if (overlay) {
            overlay.style.width = viewport.width + 'px';
            overlay.style.height = viewport.height + 'px';
        }
        
        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        
        const renderTask = page.render(renderContext);
        
        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
            
            // Re-draw annotations for this page
            redrawAnnotations();
        });
    });
}

// Multi-page page renderer using local view (no shared globals)
function renderPageFor(p) {
    const view = pagesMap.get(p);
    if (!view) return;
    pdfDoc.getPage(p).then(function(page) {
        const viewport = page.getViewport({ scale: scale });
        // Size base canvas
        view.canvas.height = viewport.height;
        view.canvas.width = viewport.width;
        // Size overlay container and annotation canvas/boxes
        view.overlay.style.width = viewport.width + 'px';
        view.overlay.style.height = viewport.height + 'px';
        view.aCanvas.width = viewport.width;
        view.aCanvas.height = viewport.height;
        view.boxes.style.width = viewport.width + 'px';
        view.boxes.style.height = viewport.height + 'px';
        
        // Render PDF page to base canvas
        return page.render({ canvasContext: view.ctx, viewport }).promise;
    }).then(function() {
        // Draw annotations for this page on the annotation canvas and boxes
        drawAnnotationsOnPage(p);
    }).catch(function(err) {
        console.error('Error rendering page', p, err);
    });
}

function drawAnnotationOn(ctxLocal, annotation) {
    ctxLocal.save();
    if (annotation.type === 'comment') {
        ctxLocal.fillStyle = annotation.color || '#3B82F6';
        ctxLocal.beginPath();
        ctxLocal.arc(annotation.x, annotation.y, 8, 0, 2 * Math.PI);
        ctxLocal.fill();
        ctxLocal.fillStyle = 'white';
        ctxLocal.font = '12px Arial';
        ctxLocal.textAlign = 'center';
        ctxLocal.fillText('💬', annotation.x, annotation.y + 4);
    } else if (annotation.type === 'highlight') {
        ctxLocal.globalAlpha = 0.3;
        ctxLocal.fillStyle = annotation.color || '#FEF08A';
        ctxLocal.fillRect(annotation.startX, annotation.startY, 
            annotation.endX - annotation.startX, 
            annotation.endY - annotation.startY);
    } else if (annotation.type === 'underline' || annotation.type === 'strikethrough') {
        ctxLocal.strokeStyle = annotation.color || (annotation.type === 'underline' ? '#10B981' : '#EF4444');
        ctxLocal.lineWidth = 2;
        ctxLocal.beginPath();
        const y1 = annotation.type === 'strikethrough' ? (annotation.startY + annotation.endY)/2 : annotation.startY;
        const y2 = annotation.type === 'strikethrough' ? (annotation.startY + annotation.endY)/2 : annotation.endY;
        ctxLocal.moveTo(annotation.startX, y1);
        ctxLocal.lineTo(annotation.endX, y2);
        ctxLocal.stroke();
    }
    ctxLocal.restore();
}

function drawAnnotationPreviewOn(ctxLocal, startX, startY, endX, endY) {
    ctxLocal.save();
    ctxLocal.globalAlpha = 0.5;
    ctxLocal.strokeStyle = getToolColor(currentTool);
    ctxLocal.lineWidth = 2;
    if (currentTool === 'highlight') {
        ctxLocal.fillStyle = getToolColor(currentTool);
        ctxLocal.fillRect(startX, startY, endX - startX, endY - startY);
    } else {
        ctxLocal.beginPath();
        ctxLocal.moveTo(startX, startY);
        ctxLocal.lineTo(endX, endY);
        ctxLocal.stroke();
    }
    ctxLocal.restore();
}

function drawAnnotationsOnPage(p) {
    const view = pagesMap.get(p);
    if (!view) return;
    const W = view.aCanvas.width;
    const H = view.aCanvas.height;

    // Clear annotation canvas and boxes
    view.aCtx.clearRect(0, 0, W, H);
    view.boxes.innerHTML = '';

    const anns = annotations.filter(ann => ann.page === p);

    // Draw shapes
    anns.filter(ann => ann.type !== 'comment').forEach(ann => {
        const apx = ann.xNorm !== undefined
            ? {
                type: ann.type,
                color: ann.color,
                x: (ann.xNorm || 0) * W,
                y: (ann.yNorm || 0) * H,
                startX: (ann.startXNorm || 0) * W,
                startY: (ann.startYNorm || 0) * H,
                endX: (ann.endXNorm || 0) * W,
                endY: (ann.endYNorm || 0) * H,
            }
            : ann; // fallback for absolute coords
        drawAnnotationOn(view.aCtx, apx);
    });

    // Draw comment boxes
    anns.filter(ann => ann.type === 'comment').forEach(a => {
        const hasNorm = a.xNorm !== undefined;
        const x = hasNorm ? a.xNorm * W : a.x;
        const y = hasNorm ? a.yNorm * H : a.y;
        const box = document.createElement('div');
        box.style.position = 'absolute';
        box.style.left = (x + 12) + 'px';
        box.style.top = (y - 10) + 'px';
        box.style.maxWidth = '260px';
        box.style.pointerEvents = 'auto';
        box.innerHTML = `
            <div class="bg-white/95 border border-blue-200 shadow-lg rounded-lg p-2 text-sm text-gray-800">
                <div class="flex items-start gap-2">
                    <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs">💬</div>
                    <div class="flex-1">${escapeHtml(a.comment || a.text || '')}</div>
                </div>
            </div>`;
        view.boxes.appendChild(box);

        // Draw the icon on the canvas
        drawAnnotationOn(view.aCtx, {
            type: 'comment', color: a.color,
            x, y
        });
    });
}

// Add page navigation (legacy, not used in multi-page mode but kept for reference)
function addPageNavigation(container) {
    const nav = document.createElement('div');
    nav.className = 'flex justify-center items-center gap-4 p-4 bg-gray-100';
    nav.innerHTML = `
        <button id="prev-page" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Previous</button>
        <span>Page <span id="page-num">${pageNum}</span> of <span id="page-count">${pdfDoc.numPages}</span></span>
        <button id="next-page" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Next</button>
    `;
    
    container.appendChild(nav);
    
    // Add event listeners
    document.getElementById('prev-page').addEventListener('click', onPrevPage);
    document.getElementById('next-page').addEventListener('click', onNextPage);
}

// Page navigation functions
function onPrevPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

function onNextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
    document.getElementById('page-num').textContent = num;
}

// Helpers for multi-page rendering
function setGlobalsForPage(p) {
    const view = pagesMap.get(p);
    if (!view) return;
    canvas = view.canvas;
    ctx = view.ctx;
    overlay = view.overlay;
    pageNum = p;
}

function redrawPage(p) {
    setGlobalsForPage(p);
    redrawAnnotations();
}

function addCanvasEventListenersFor(p, canvasEl) {
    let isDrawing = false;
    let startX, startY;

    canvasEl.addEventListener('mousedown', (e) => {
        if (!currentTool) return;
        setGlobalsForPage(p);
        isDrawing = true;
        const rect = canvasEl.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
        if (currentTool === 'comment') {
            showCommentModal(startX, startY);
            isDrawing = false;
        }
    });

    canvasEl.addEventListener('mousemove', (e) => {
        if (!isDrawing || currentTool === 'comment') return;
        const rect = canvasEl.getBoundingClientRect();
        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        // Redraw shapes for this page, then draw preview on annotation layer
        drawAnnotationsOnPage(p);
        const view = pagesMap.get(p);
        if (view) {
            drawAnnotationPreviewOn(view.aCtx, startX, startY, currentX, currentY);
        }
    });

    canvasEl.addEventListener('mouseup', (e) => {
        if (!isDrawing) return;
        setGlobalsForPage(p);
        isDrawing = false;
        const rect = canvasEl.getBoundingClientRect();
        const endX = e.clientX - rect.left;
        const endY = e.clientY - rect.top;
        if (currentTool !== 'comment') {
            addAnnotation(startX, startY, endX, endY);
        }
    });
}

// Tool selection
document.getElementById('highlight-btn').addEventListener('click', () => selectTool('highlight'));
document.getElementById('comment-btn').addEventListener('click', () => selectTool('comment'));
document.getElementById('underline-btn').addEventListener('click', () => selectTool('underline'));
document.getElementById('strikethrough-btn').addEventListener('click', () => selectTool('strikethrough'));

// Comment modal handlers
function escapeHtml(str) {
    return String(str).replace(/[&<>'"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[s]));
}

document.getElementById('commentSaveBtn').addEventListener('click', commitComment);

document.getElementById('commentCancelBtn').addEventListener('click', hideCommentModal);

document.getElementById('commentModalClose').addEventListener('click', hideCommentModal);

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        hideCommentModal();
    }
});

// Undo/Redo handlers
function undo() {
    if (annotations.length === 0) return;
    const last = annotations.pop();
    redoStack.push(last);
    redrawPage(last.page);
}

function redo() {
    if (redoStack.length === 0) return;
    const ann = redoStack.pop();
    annotations.push(ann);
    redrawPage(ann.page);
}

document.getElementById('undo-btn').addEventListener('click', undo);

document.getElementById('redo-btn').addEventListener('click', redo);

// Zoom handlers
function applyZoom(newScale) {
    scale = Math.min(3.0, Math.max(0.5, newScale));
    const zl = document.getElementById('zoom-level');
    if (zl) zl.textContent = Math.round(scale * 100) + '%';
    // Re-render all pages
    for (let p = 1; p <= pdfDoc.numPages; p++) {
        renderPageFor(p);
    }
}

document.getElementById('zoom-in-btn').addEventListener('click', () => applyZoom(scale + 0.1));

document.getElementById('zoom-out-btn').addEventListener('click', () => applyZoom(scale - 0.1));

// Autosave utilities
function showToast(message, kind = 'info') {
    const t = document.getElementById('toast');
    const c = document.getElementById('toast-content');
    const icon = document.getElementById('toast-icon');
    const text = document.getElementById('toast-text');
    
    if (!t || !c) return;
    
    // Set icon based on kind
    let iconSvg = '';
    let bgClass = '';
    
    switch(kind) {
        case 'success':
            iconSvg = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            bgClass = 'bg-gradient-to-r from-emerald-600 to-green-600';
            break;
        case 'error':
            iconSvg = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            bgClass = 'bg-gradient-to-r from-red-600 to-rose-600';
            break;
        default:
            iconSvg = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            bgClass = 'bg-gradient-to-r from-slate-700 to-slate-800';
    }
    
    icon.innerHTML = iconSvg;
    text.textContent = message;
    c.className = `${bgClass} text-white px-6 py-3 rounded-xl shadow-2xl border border-white/10 backdrop-blur-sm flex items-center gap-3 transform transition-all duration-300`;
    
    t.classList.remove('hidden');
    
    // Animate in
    setTimeout(() => {
        c.classList.remove('translate-y-2', 'opacity-0');
        c.classList.add('translate-y-0', 'opacity-100');
    }, 10);
    
    // Animate out
    setTimeout(() => {
        c.classList.remove('translate-y-0', 'opacity-100');
        c.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => t.classList.add('hidden'), 300);
    }, 2500);
}

function scheduleAutoSave() {
    if (autosaveTimer) clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(() => saveDraftLocal(), autosaveDelay);
}

function saveDraftLocal() {
    try {
        const payload = {
            annotations,
            feedback_message: document.getElementById('feedback-message').value
        };
        localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
        showToast('Draft saved');
    } catch (e) {
        console.error('Draft save error', e);
        showToast('Draft save error', 'error');
    }
}

// Keyboard shortcuts
window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
        e.preventDefault();
        undo();
    } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
        e.preventDefault();
        redo();
    } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        document.getElementById('save-btn').click();
    } else if (e.key.toLowerCase() === 'h') {
        selectTool('highlight');
    } else if (e.key.toLowerCase() === 'c') {
        selectTool('comment');
    } else if (e.key.toLowerCase() === 'u') {
        selectTool('underline');
    } else if (e.key.toLowerCase() === 'x') {
        selectTool('strikethrough');
    } else if (e.key === '+') {
        applyZoom(scale + 0.1);
    } else if (e.key === '-') {
        applyZoom(scale - 0.1);
    }
});

function selectTool(tool) {
    currentTool = tool;
    
    // Update button states
    document.querySelectorAll('[id$="-btn"]').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-offset-2', 'ring-blue-500');
    });
    
    document.getElementById(tool + '-btn').classList.add('ring-2', 'ring-offset-2', 'ring-blue-500');
    
    // Update cursor
    canvas.style.cursor = tool === 'comment' ? 'pointer' : 'crosshair';
}

// Canvas event listeners
function addCanvasEventListeners() {
    let isDrawing = false;
    let startX, startY;
    
    canvas.addEventListener('mousedown', (e) => {
        if (!currentTool) return;
        
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
        
        if (currentTool === 'comment') {
            showCommentModal(startX, startY);
            isDrawing = false;
        }
    });
    
    canvas.addEventListener('mousemove', (e) => {
        if (!isDrawing || currentTool === 'comment') return;
        
        const rect = canvas.getBoundingClientRect();
        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        
        // Show preview of annotation
        redrawAnnotations();
        drawAnnotationPreview(startX, startY, currentX, currentY);
    });
    
    canvas.addEventListener('mouseup', (e) => {
        if (!isDrawing) return;
        
        isDrawing = false;
        const rect = canvas.getBoundingClientRect();
        const endX = e.clientX - rect.left;
        const endY = e.clientY - rect.top;
        
        if (currentTool !== 'comment') {
            addAnnotation(startX, startY, endX, endY);
        }
    });
}

// Add annotation
function addAnnotation(startX, startY, endX, endY) {
    const view = pagesMap.get(pageNum);
    const W = view?.aCanvas?.width || 1;
    const H = view?.aCanvas?.height || 1;
    const annotation = {
        id: Date.now(),
        type: currentTool,
        page: pageNum,
        startXNorm: startX / W,
        startYNorm: startY / H,
        endXNorm: endX / W,
        endYNorm: endY / H,
        color: getToolColor(currentTool)
    };
    annotations.push(annotation);
    redoStack = [];
    drawAnnotationsOnPage(annotation.page);
    updatePageAnnotationCounts();
    isDirty = true;
    scheduleAutoSave();
}

// Add comment
function showCommentModal(x, y) {
    pendingComment = { x, y, page: pageNum };
    const modal = document.getElementById('commentModal');
    const modalContent = document.getElementById('commentModalContent');
    const input = document.getElementById('commentInput');
    
    input.value = '';
    modal.classList.remove('hidden');
    
    // Animate modal appearance
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
        input.focus();
    }, 10);
}

function hideCommentModal() {
    const modal = document.getElementById('commentModal');
    const modalContent = document.getElementById('commentModalContent');
    
    // Animate modal disappearance
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        pendingComment = null;
    }, 300);
}

function commitComment() {
    const text = document.getElementById('commentInput').value.trim();
    if (!pendingComment || !text) {
        hideCommentModal();
        return;
    }
    const view = pagesMap.get(pendingComment.page);
    const W = view?.aCanvas?.width || 1;
    const H = view?.aCanvas?.height || 1;
    const annotation = {
        id: Date.now(),
        type: 'comment',
        page: pendingComment.page,
        xNorm: pendingComment.x / W,
        yNorm: pendingComment.y / H,
        comment: text,
        color: '#3B82F6'
    };
    annotations.push(annotation);
    redoStack = [];
    hideCommentModal();
    drawAnnotationsOnPage(annotation.page);
    updatePageAnnotationCounts();
    isDirty = true;
    scheduleAutoSave();
}

// Get tool color
function getToolColor(tool) {
    const colors = {
        highlight: '#FEF08A',
        underline: '#10B981',
        strikethrough: '#EF4444',
        comment: '#3B82F6'
    };
    return colors[tool] || '#000000';
}

// Draw annotation preview
function drawAnnotationPreview(startX, startY, endX, endY) {
    ctx.save();
    ctx.globalAlpha = 0.5;
    ctx.strokeStyle = getToolColor(currentTool);
    ctx.lineWidth = 2;
    
    if (currentTool === 'highlight') {
        ctx.fillStyle = getToolColor(currentTool);
        ctx.fillRect(startX, startY, endX - startX, endY - startY);
    } else {
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(endX, endY);
        ctx.stroke();
    }
    
    ctx.restore();
}

// Redraw all annotations
function redrawAnnotations() {
    // Re-render the page first
    if (pdfDoc) {
        pdfDoc.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({scale: scale});
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            page.render(renderContext).promise.then(function() {
                // Draw annotations for current page
                annotations.filter(ann => ann.page === pageNum).forEach(drawAnnotation);

                // Render live comment boxes in overlay
                if (overlay) {
                    overlay.innerHTML = '';
                    annotations
                        .filter(ann => ann.page === pageNum && ann.type === 'comment')
                        .forEach(a => {
                            const box = document.createElement('div');
                            box.style.position = 'absolute';
                            box.style.left = (a.x + 12) + 'px';
                            box.style.top = (a.y - 10) + 'px';
                            box.style.maxWidth = '260px';
                            box.style.pointerEvents = 'auto';
                            box.innerHTML = `
                                <div class="bg-white/95 border border-blue-200 shadow-lg rounded-lg p-2 text-sm text-gray-800">
                                    <div class="flex items-start gap-2">
                                        <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs">💬</div>
                                        <div class="flex-1">${escapeHtml(a.comment)}</div>
                                    </div>
                                </div>
                            `;
                            overlay.appendChild(box);
                        });
                }
            });
        });
    }
}

// Draw single annotation
function drawAnnotation(annotation) {
    ctx.save();
    
    if (annotation.type === 'comment') {
        // Draw comment icon
        ctx.fillStyle = annotation.color;
        ctx.beginPath();
        ctx.arc(annotation.x, annotation.y, 8, 0, 2 * Math.PI);
        ctx.fill();
        
        // Draw comment number or icon
        ctx.fillStyle = 'white';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('💬', annotation.x, annotation.y + 4);
        
    } else if (annotation.type === 'highlight') {
        ctx.globalAlpha = 0.3;
        ctx.fillStyle = annotation.color;
        ctx.fillRect(annotation.startX, annotation.startY, 
                    annotation.endX - annotation.startX, 
                    annotation.endY - annotation.startY);
        
    } else {
        ctx.strokeStyle = annotation.color;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(annotation.startX, annotation.startY);
        ctx.lineTo(annotation.endX, annotation.endY);
        ctx.stroke();
    }
    
    ctx.restore();
}

// Sidebar helpers
function setupSidebar() {
    pagesContainerRoot = document.getElementById('pagesContainer');
    pageIndexEl = document.getElementById('pageIndex');
    pageSliderEl = document.getElementById('pageSlider');

    // Build enhanced page index buttons
    pageIndexEl.innerHTML = '';
    for (let p = 1; p <= pdfDoc.numPages; p++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'group w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/60 hover:shadow-sm transition-all duration-200 text-sm border border-transparent hover:border-slate-200/50';
        btn.innerHTML = `
            <div class="w-8 h-8 bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg flex items-center justify-center text-xs font-semibold text-slate-600 group-hover:from-blue-100 group-hover:to-indigo-100 group-hover:text-blue-600 transition-all duration-200">
                ${p}
            </div>
            <div class="flex-1 text-left">
                <div class="font-medium text-slate-700 group-hover:text-slate-900">Page ${p}</div>
                <div class="text-xs text-slate-500" id="page-${p}-annotations">No annotations</div>
            </div>
            <div class="w-2 h-2 bg-slate-300 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
        `;
        btn.setAttribute('data-page-index', String(p));
        btn.addEventListener('click', () => scrollToPage(p));
        pageIndexEl.appendChild(btn);
    }

    // Update slider display
    document.getElementById('totalPagesSlider').textContent = String(pdfDoc.numPages);

    // Slider config
    document.getElementById('totalPagesDisplay').textContent = String(pdfDoc.numPages);
    document.getElementById('currentPageDisplay').textContent = '1';
    pageSliderEl.max = String(pdfDoc.numPages);
    pageSliderEl.value = '1';
    pageSliderEl.addEventListener('input', (e) => {
        const v = parseInt(e.target.value, 10) || 1;
        scrollToPage(v);
    });
    
    // Also listen for change event to ensure updates
    pageSliderEl.addEventListener('change', (e) => {
        const v = parseInt(e.target.value, 10) || 1;
        scrollToPage(v);
    });

    // Highlight first page
    highlightPageIndex(1);
}

function scrollToPage(p) {
    const el = document.getElementById(`page-wrapper-${p}`);
    if (el && pagesContainerRoot) {
        // Update current page tracking
        currentVisiblePage = p;
        
        // Update displays immediately
        document.getElementById('currentPageDisplay').textContent = String(p);
        if (pageSliderEl) pageSliderEl.value = String(p);
        
        // Highlight the page in sidebar
        highlightPageIndex(p);
        
        // Scroll to the page
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function observeVisiblePages() {
    const root = document.getElementById('pagesContainer');
    if (!root) return;
    if (pageObserver) {
        pageObserver.disconnect();
    }
    pageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                const p = parseInt(id.replace('page-wrapper-', ''));
                
                // Only update if this is a different page (avoid duplicate updates)
                if (p !== currentVisiblePage) {
                    currentVisiblePage = p;
                    document.getElementById('currentPageDisplay').textContent = String(p);
                    if (pageSliderEl) pageSliderEl.value = String(p);
                    highlightPageIndex(p);
                }
            }
        });
    }, { root, threshold: 0.6 });

    for (let p = 1; p <= pdfDoc.numPages; p++) {
        const el = document.getElementById(`page-wrapper-${p}`);
        if (el) pageObserver.observe(el);
    }
}

function highlightPageIndex(p) {
    // Update current page tracking
    currentVisiblePage = p;
    
    // Update page displays
    document.getElementById('currentPageDisplay').textContent = String(p);
    if (pageSliderEl) pageSliderEl.value = String(p);
    
    // Reset all page buttons to default state
    document.querySelectorAll('[data-page-index]').forEach(el => {
        // Remove active classes
        el.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white', 'shadow-lg', 'border-blue-200');
        el.classList.add('border-transparent');
        
        // Reset page number circle
        const circle = el.querySelector('div:first-child');
        if (circle) {
            circle.classList.remove('from-white/20', 'to-white/10', 'text-white');
            circle.classList.add('from-slate-100', 'to-slate-200', 'text-slate-600');
        }
        
        // Reset text colors
        const pageTitle = el.querySelector('.font-medium');
        if (pageTitle) {
            pageTitle.classList.remove('text-white');
            pageTitle.classList.add('text-slate-700');
        }
        
        const annotationCount = el.querySelector('.text-xs');
        if (annotationCount) {
            annotationCount.classList.remove('text-blue-100');
            annotationCount.classList.add('text-slate-500');
        }
    });
    
    // Apply active state to current page
    const active = document.querySelector(`[data-page-index="${p}"]`);
    if (active) {
        active.classList.remove('border-transparent');
        active.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white', 'shadow-lg', 'border-blue-200');
        
        // Update the page number circle
        const circle = active.querySelector('div:first-child');
        if (circle) {
            circle.classList.remove('from-slate-100', 'to-slate-200', 'text-slate-600');
            circle.classList.add('from-white/20', 'to-white/10', 'text-white');
        }
        
        // Update text colors
        const pageTitle = active.querySelector('.font-medium');
        if (pageTitle) {
            pageTitle.classList.remove('text-slate-700');
            pageTitle.classList.add('text-white');
        }
        
        const annotationCount = active.querySelector('.text-xs');
        if (annotationCount) {
            annotationCount.classList.remove('text-slate-500');
            annotationCount.classList.add('text-blue-100');
        }
    }
    
    // Update annotation counts
    updatePageAnnotationCounts();
}

function updatePageAnnotationCounts() {
    for (let p = 1; p <= pdfDoc.numPages; p++) {
        const count = annotations.filter(ann => ann.page === p).length;
        const countEl = document.getElementById(`page-${p}-annotations`);
        if (countEl) {
            if (count === 0) {
                countEl.textContent = 'No annotations';
            } else {
                countEl.textContent = `${count} annotation${count > 1 ? 's' : ''}`;
            }
        }
    }
}

let currentSessionId = null;
let isSent = false;

// Save annotations as draft
document.getElementById('save-btn').addEventListener('click', async () => {
    if (isSaving) return;
    isSaving = true;
    
    const saveBtn = document.getElementById('save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = `
        <svg class="w-4 h-4 mr-2 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Saving...
    `;
    saveBtn.disabled = true;
    
    try {
        const response = await fetch('{{ route('co-supervisor.reports.submissions.annotations.store', [$report, $submission]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                annotations: annotations,
                feedback_message: document.getElementById('feedback-message').value
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentSessionId = result.session_id;
            isSent = result.is_sent || false;
            
            // Clear draft and dirty flag
            localStorage.removeItem(DRAFT_KEY);
            isDirty = false;
            
            // Update UI based on sent status
            updateSendFeedbackButton();
            
            showToast(result.message, 'success');
        } else {
            showToast(result.message || 'Save failed', 'error');
        }
    } catch (error) {
        console.error('Error saving annotations:', error);
        showToast('Save error', 'error');
    } finally {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        isSaving = false;
    }
});

// Send feedback to students
document.getElementById('send-feedback-btn').addEventListener('click', async () => {
    if (!currentSessionId) {
        showToast('Please save annotations first', 'error');
        return;
    }
    
    if (isSent) {
        showToast('Feedback has already been sent to students', 'error');
        return;
    }
    
    // Show professional confirmation modal
    showSendFeedbackModal();
});

// Professional Send Feedback Modal Functions
function showSendFeedbackModal() {
    const modal = document.getElementById('sendFeedbackModal');
    const modalContent = document.getElementById('sendFeedbackModalContent');
    
    // Update modal content with current data
    updateModalContent();
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Animate modal appearance
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function hideSendFeedbackModal() {
    const modal = document.getElementById('sendFeedbackModal');
    const modalContent = document.getElementById('sendFeedbackModalContent');
    
    // Animate modal disappearance
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function updateModalContent() {
    const message = document.getElementById('feedback-message').value.trim();
    
    // Update annotation counts
    const totalAnnotations = annotations.length;
    const uniquePages = new Set(annotations.map(ann => ann.page)).size;
    const commentCount = annotations.filter(ann => ann.type === 'comment').length;
    
    document.getElementById('modalAnnotationCount').textContent = totalAnnotations;
    document.getElementById('modalPageCount').textContent = uniquePages;
    document.getElementById('modalCommentCount').textContent = commentCount;
    
    // Show/hide feedback message preview
    const feedbackPreview = document.getElementById('feedbackMessagePreview');
    const noMessageWarning = document.getElementById('noMessageWarning');
    const feedbackText = document.getElementById('feedbackMessageText');
    
    if (message) {
        feedbackText.textContent = message;
        feedbackPreview.classList.remove('hidden');
        noMessageWarning.classList.add('hidden');
    } else {
        feedbackPreview.classList.add('hidden');
        noMessageWarning.classList.remove('hidden');
    }
}

async function confirmSendFeedback() {
    hideSendFeedbackModal();
    
    const sendBtn = document.getElementById('send-feedback-btn');
    const originalText = sendBtn.innerHTML;
    sendBtn.innerHTML = `
        <svg class="w-4 h-4 mr-2 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Sending...
    `;
    sendBtn.disabled = true;
    
    try {
        const response = await fetch(`/co-supervisor/reports/{{ $report->id }}/submissions/{{ $submission->id }}/annotations/${currentSessionId}/send-feedback`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            isSent = true;
            updateSendFeedbackButton();
            showToast(result.message, 'success');
            
            // Redirect to report after successful send
            setTimeout(() => {
                window.location.href = '{{ route('co-supervisor.reports.show', $report) }}';
            }, 2000);
        } else {
            showToast(result.message || 'Failed to send feedback', 'error');
        }
    } catch (error) {
        console.error('Error sending feedback:', error);
        showToast('Error sending feedback', 'error');
    } finally {
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
    }
}

// Modal event listeners
document.getElementById('cancelSendFeedback').addEventListener('click', hideSendFeedbackModal);
document.getElementById('confirmSendFeedback').addEventListener('click', confirmSendFeedback);

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('sendFeedbackModal');
    if (e.target === modal) {
        hideSendFeedbackModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('sendFeedbackModal');
        if (!modal.classList.contains('hidden')) {
            hideSendFeedbackModal();
        }
    }
});

// Update send feedback button based on status
function updateSendFeedbackButton() {
    const sendBtn = document.getElementById('send-feedback-btn');
    
    if (isSent) {
        sendBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Feedback Sent
        `;
        sendBtn.className = 'group px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl cursor-not-allowed opacity-75 font-medium';
        sendBtn.disabled = true;
    } else if (currentSessionId) {
        sendBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2 inline group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Send Feedback
        `;
        sendBtn.className = 'group px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium';
        sendBtn.disabled = false;
    } else {
        sendBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Save First
        `;
        sendBtn.className = 'group px-6 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl cursor-not-allowed opacity-75 font-medium';
        sendBtn.disabled = true;
    }
}

// Initialize when page loads
function loadDraftIfAny() {
    try {
        const raw = localStorage.getItem(DRAFT_KEY);
        if (!raw) return;
        const payload = JSON.parse(raw);
        if (Array.isArray(payload.annotations)) {
            annotations = payload.annotations;
            // redraw all pages annotations
            for (let p = 1; p <= pdfDoc.numPages; p++) {
                drawAnnotationsOnPage(p);
            }
        }
        if (typeof payload.feedback_message === 'string') {
            document.getElementById('feedback-message').value = payload.feedback_message;
        }
        showToast('Draft loaded');
    } catch (e) {
        console.warn('Failed to load draft', e);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await initPDF();
    loadDraftIfAny();
});

// Warn before leaving if there are unsaved changes
window.addEventListener('beforeunload', (e) => {
    if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endsection