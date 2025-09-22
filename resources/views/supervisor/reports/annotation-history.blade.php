@extends('layouts.supervisor')

@section('page-title', 'Annotation History - ' . $submission->subject)
@section('page-description', 'View annotation history for this submission')

@section('content')
<div class="container mx-auto max-w-5xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Annotation History</h1>
                    <div class="text-sm text-gray-600">
                        <span><strong>Report:</strong> {{ $report->project_title ?: 'Report for ' . $report->group->name }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Student:</strong> {{ $submission->student->name }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Submission:</strong> {{ $submission->subject }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Total Sessions:</strong> {{ $annotationSessions->count() }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('supervisor.reports.submissions.annotate', [$report, $submission]) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        New Annotation
                    </a>
                    <a href="{{ route('supervisor.reports.show', $report) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Annotation Sessions -->
    @if($annotationSessions->count() > 0)
        <div class="space-y-6">
            @foreach($annotationSessions as $session)
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Version {{ $session->version }}
                                </h3>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                    {{ $session->created_at->format('M d, Y') }}
                                </span>
                                @if($loop->first)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                    Latest
                                </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600">
                                <span><strong>Created:</strong> {{ $session->created_at->format('M d, Y h:i A') }}</span>
                                <span class="mx-2">•</span>
                                <span><strong>Annotations:</strong> {{ count($session->annotations_json ?? []) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($session->is_sent)
                                <span class="text-sm text-gray-500">Students notified</span>
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @if($session->sent_at)
                                    <span class="text-xs text-gray-400">{{ $session->sent_at->format('M d, Y h:i A') }}</span>
                                @endif
                            @else
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                                    Draft - Not Sent
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- General Comment -->
                    @if($session->message)
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mb-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800 mb-1">General Feedback:</p>
                                <p class="text-blue-700">{{ $session->message }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Annotation Summary -->
                    @php
                        $annotations = $session->annotations_json ?? [];
                        $annotationsByType = [];
                        foreach ($annotations as $annotation) {
                            $type = $annotation['type'] ?? 'unknown';
                            if (!isset($annotationsByType[$type])) {
                                $annotationsByType[$type] = 0;
                            }
                            $annotationsByType[$type]++;
                        }
                    @endphp

                    @if(count($annotations) > 0)
                    <!-- Annotation Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        @foreach($annotationsByType as $type => $count)
                        <div class="bg-gray-50 p-3 rounded-lg text-center">
                            <div class="text-2xl mb-1">
                                @if($type === 'comment') 💬
                                @elseif($type === 'highlight') 🖍️
                                @elseif($type === 'underline') ↳
                                @elseif($type === 'strikethrough') ↗
                                @else 📝
                                @endif
                            </div>
                            <div class="text-sm font-medium text-gray-700">{{ ucfirst($type) }}</div>
                            <div class="text-lg font-bold text-gray-900">{{ $count }}</div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Professional Page Overview -->
                    @php
                        // Group annotations by page
                        $pageAnnotations = [];
                        foreach ($annotations as $annotation) {
                            $page = $annotation['page'] ?? 1;
                            if (!isset($pageAnnotations[$page])) {
                                $pageAnnotations[$page] = [
                                    'total' => 0,
                                    'types' => [],
                                    'comments' => []
                                ];
                            }
                            $pageAnnotations[$page]['total']++;
                            
                            $type = $annotation['type'] ?? 'unknown';
                            if (!isset($pageAnnotations[$page]['types'][$type])) {
                                $pageAnnotations[$page]['types'][$type] = 0;
                            }
                            $pageAnnotations[$page]['types'][$type]++;
                            
                            // Collect comments for preview
                            if ($type === 'comment' && !empty($annotation['comment'])) {
                                $pageAnnotations[$page]['comments'][] = $annotation['comment'];
                            }
                        }
                        ksort($pageAnnotations); // Sort by page number
                    @endphp

                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 mb-6 border border-emerald-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-emerald-900 mb-1">📄 Your Annotation Coverage</h4>
                                <p class="text-sm text-emerald-700">Visual map of your feedback across the student's document</p>
                            </div>
                            <div class="bg-white rounded-lg px-3 py-2 shadow-sm border border-emerald-200">
                                <span class="text-sm font-semibold text-emerald-800">{{ count($pageAnnotations) }} Pages Annotated</span>
                            </div>
                        </div>

                        <!-- Page Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                            @foreach($pageAnnotations as $pageNum => $pageData)
                                <div class="group relative">
                                    <!-- Page Card -->
                                    <div class="bg-white rounded-lg border-2 border-emerald-200 hover:border-emerald-400 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md transform hover:-translate-y-1"
                                         onclick="jumpToPage{{ $session->id }}({{ $pageNum }})">
                                        <!-- Page Header -->
                                        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-3 py-2 rounded-t-lg">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold">Page {{ $pageNum }}</span>
                                                <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-bold">{{ $pageData['total'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Page Content -->
                                        <div class="p-3">
                                            <!-- Annotation Type Icons -->
                                            <div class="flex flex-wrap gap-1 mb-2">
                                                @foreach($pageData['types'] as $type => $count)
                                                    <div class="flex items-center bg-gray-100 rounded-full px-2 py-1">
                                                        <span class="text-xs mr-1">
                                                            @if($type === 'comment') 💬
                                                            @elseif($type === 'highlight') 🖍️
                                                            @elseif($type === 'underline') ↳
                                                            @elseif($type === 'strikethrough') ↗
                                                            @else 📝
                                                            @endif
                                                        </span>
                                                        <span class="text-xs font-semibold text-gray-700">{{ $count }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <!-- Comment Preview -->
                                            @if(count($pageData['comments']) > 0)
                                                <div class="text-xs text-gray-600 line-clamp-2">
                                                    "{{ Str::limit($pageData['comments'][0], 40) }}"
                                                    @if(count($pageData['comments']) > 1)
                                                        <span class="text-emerald-600 font-medium">+{{ count($pageData['comments']) - 1 }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 italic">Visual annotations only</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Hover Indicator -->
                                        <div class="absolute inset-0 bg-emerald-500/10 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>
                                    </div>
                                    
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                                        <div class="bg-gray-900 text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap shadow-lg">
                                            Click to jump to Page {{ $pageNum }}
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Legend -->
                        <div class="mt-6 pt-4 border-t border-emerald-200">
                            <div class="flex flex-wrap items-center gap-4 text-xs text-emerald-700">
                                <div class="flex items-center gap-1">
                                    <div class="w-3 h-3 bg-gradient-to-r from-emerald-600 to-teal-600 rounded"></div>
                                    <span>Page with your feedback</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm">💬</span>
                                    <span>Comments</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm">🖍️</span>
                                    <span>Highlights</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm">↳</span>
                                    <span>Underlines</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm">↗</span>
                                    <span>Strikethrough</span>
                                </div>
                                <div class="flex items-center gap-1 ml-auto">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.121 2.122"></path>
                                    </svg>
                                    <span class="font-medium">Click any page to jump directly</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Show PDF Button -->
                    <div class="text-center mb-4">
                        <button onclick="togglePDFViewer{{ $session->id }}()" id="showPdfBtn{{ $session->id }}" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Show Annotated PDF
                        </button>
                    </div>

                    <!-- PDF Viewer with Annotations (Hidden by default) -->
                    <div id="pdfViewer{{ $session->id }}" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hidden">
                        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-800">Annotated PDF Preview</h4>
                            <div class="flex items-center gap-2">
                                <button onclick="toggleAnnotations{{ $session->id }}()" id="toggleBtn{{ $session->id }}" 
                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors">
                                    Hide Annotations
                                </button>
                                <button onclick="togglePDFViewer{{ $session->id }}()" 
                                    class="px-3 py-1 bg-red-100 text-red-700 rounded text-sm hover:bg-red-200 transition-colors">
                                    Close PDF
                                </button>
                                <span class="text-xs text-gray-500">Page <span id="currentPage{{ $session->id }}">1</span> of <span id="totalPages{{ $session->id }}">1</span></span>
                            </div>
                        </div>
                        <div class="relative bg-white" style="height: 600px;">
                            <canvas id="pdfCanvas{{ $session->id }}" class="mx-auto block"></canvas>
                            <div id="annotationLayer{{ $session->id }}" class="absolute top-0 left-0 w-full h-full pointer-events-none"></div>
                        </div>
                        <div class="bg-gray-100 px-4 py-3 border-t border-gray-200 flex items-center justify-center gap-4">
                            <button onclick="prevPage{{ $session->id }}()" id="prevBtn{{ $session->id }}" 
                                class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors disabled:opacity-50">
                                Previous
                            </button>
                            <button onclick="nextPage{{ $session->id }}()" id="nextBtn{{ $session->id }}" 
                                class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors disabled:opacity-50">
                                Next
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>No annotations in this session</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- No Annotations -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No Annotations Yet</h3>
                <p class="text-gray-600 mb-4">You haven't created any annotation sessions for this submission yet.</p>
                <a href="{{ route('supervisor.reports.submissions.annotate', [$report, $submission]) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Create First Annotation
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Beautiful Comment Modal -->
<div id="commentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 transform transition-all duration-300 scale-95 opacity-0" id="commentModalContent">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Supervisor Comment</h3>
                        <p class="text-blue-100 text-sm">Feedback on this section</p>
                    </div>
                </div>
                <button onclick="hideCommentModal()" class="w-8 h-8 flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p id="commentText" class="text-gray-800 leading-relaxed whitespace-pre-wrap"></p>
                    </div>
                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Added during annotation session</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex items-center justify-end">
            <button onclick="hideCommentModal()" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                Close
            </button>
        </div>
    </div>
</div>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// PDF.js setup
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// PDF Annotation Viewers for each session
const pdfViewers = {};

class PDFAnnotationHistoryViewer {
    constructor(sessionId, annotations) {
        this.sessionId = sessionId;
        this.annotations = annotations;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.scale = 1.2;
        this.showAnnotations = true;
        this.canvas = document.getElementById(`pdfCanvas${sessionId}`);
        this.ctx = this.canvas.getContext('2d');
        this.annotationLayer = document.getElementById(`annotationLayer${sessionId}`);
        
        this.init();
    }

    async init() {
        try {
            // Load PDF
            this.pdfDoc = await pdfjsLib.getDocument('{{ route('supervisor.reports.submissions.view', [$report, $submission]) }}').promise;
            
            // Update total pages
            document.getElementById(`totalPages${this.sessionId}`).textContent = this.pdfDoc.numPages;
            
            // Render first page
            await this.renderPage(1);
            
            // Update navigation buttons
            this.updateNavButtons();
            
        } catch (error) {
            console.error('Error loading PDF:', error);
            this.canvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>Error loading PDF</p></div>';
        }
    }

    async renderPage(pageNum) {
        if (!this.pdfDoc) return;
        
        try {
            const page = await this.pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: this.scale });
            
            // Set canvas dimensions
            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;
            
            // Update annotation layer size
            this.annotationLayer.style.width = viewport.width + 'px';
            this.annotationLayer.style.height = viewport.height + 'px';
            
            // Render PDF page
            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };
            
            await page.render(renderContext).promise;
            
            // Update current page
            this.currentPage = pageNum;
            document.getElementById(`currentPage${this.sessionId}`).textContent = pageNum;
            
            // Render annotations
            this.renderAnnotations();
            
        } catch (error) {
            console.error('Error rendering page:', error);
        }
    }

    renderAnnotations() {
        this.annotationLayer.innerHTML = '';
        
        if (!this.showAnnotations) return;
        
        // Filter annotations for current page
        const pageAnnotations = this.annotations.filter(ann => ann.page === this.currentPage);
        
        pageAnnotations.forEach(annotation => {
            const element = document.createElement('div');
            element.style.position = 'absolute';
            
            if (annotation.type === 'comment') {
                // Handle normalized coordinates
                const x = annotation.xNorm ? annotation.xNorm * this.canvas.width : annotation.x;
                const y = annotation.yNorm ? annotation.yNorm * this.canvas.height : annotation.y;
                
                element.innerHTML = `
                    <div class="relative">
                        <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs cursor-pointer hover:bg-blue-600 transition-colors" 
                             onclick="showComment${this.sessionId}('${this.escapeHtml(annotation.comment || annotation.text || '')}')">
                            💬
                        </div>
                    </div>
                `;
                element.style.left = (x + 12) + 'px';
                element.style.top = (y - 10) + 'px';
                element.style.pointerEvents = 'auto';
                
            } else {
                // Handle other annotation types (highlight, underline, strikethrough)
                const startX = annotation.startXNorm ? annotation.startXNorm * this.canvas.width : annotation.startX;
                const startY = annotation.startYNorm ? annotation.startYNorm * this.canvas.height : annotation.startY;
                const endX = annotation.endXNorm ? annotation.endXNorm * this.canvas.width : annotation.endX;
                const endY = annotation.endYNorm ? annotation.endYNorm * this.canvas.height : annotation.endY;
                
                const left = Math.min(startX, endX);
                const top = Math.min(startY, endY);
                const width = Math.abs(endX - startX);
                const height = Math.abs(endY - startY);
                
                element.style.left = left + 'px';
                element.style.top = top + 'px';
                element.style.width = width + 'px';
                element.style.height = height + 'px';
                element.style.pointerEvents = 'none';
                
                if (annotation.type === 'highlight') {
                    element.style.backgroundColor = (annotation.color || '#FEF08A') + '60';
                } else if (annotation.type === 'underline') {
                    element.style.borderBottom = `3px solid ${annotation.color || '#10B981'}`;
                    element.style.backgroundColor = 'transparent';
                } else if (annotation.type === 'strikethrough') {
                    element.style.borderTop = `2px solid ${annotation.color || '#EF4444'}`;
                    element.style.backgroundColor = 'transparent';
                    element.style.top = (top + height/2) + 'px';
                    element.style.height = '2px';
                }
            }
            
            this.annotationLayer.appendChild(element);
        });
    }

    updateNavButtons() {
        const prevBtn = document.getElementById(`prevBtn${this.sessionId}`);
        const nextBtn = document.getElementById(`nextBtn${this.sessionId}`);
        
        if (prevBtn) prevBtn.disabled = this.currentPage <= 1;
        if (nextBtn) nextBtn.disabled = this.currentPage >= this.pdfDoc.numPages;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global functions for each session
@foreach($annotationSessions as $session)
@if(count($session->annotations_json ?? []) > 0)

// Session {{ $session->id }} functions
function togglePDFViewer{{ $session->id }}() {
    const pdfViewer = document.getElementById('pdfViewer{{ $session->id }}');
    const showBtn = document.getElementById('showPdfBtn{{ $session->id }}');
    
    if (pdfViewer.classList.contains('hidden')) {
        // Show PDF viewer
        pdfViewer.classList.remove('hidden');
        showBtn.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
            </svg>
            Hide Annotated PDF
        `;
        showBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        showBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        
        // Initialize PDF viewer if not already done
        if (!pdfViewers[{{ $session->id }}]) {
            pdfViewers[{{ $session->id }}] = new PDFAnnotationHistoryViewer({{ $session->id }}, @json($session->annotations_json));
        }
    } else {
        // Hide PDF viewer
        pdfViewer.classList.add('hidden');
        showBtn.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Show Annotated PDF
        `;
        showBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        showBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
    }
}

function toggleAnnotations{{ $session->id }}() {
    const viewer = pdfViewers[{{ $session->id }}];
    if (viewer) {
        viewer.showAnnotations = !viewer.showAnnotations;
        const btn = document.getElementById('toggleBtn{{ $session->id }}');
        btn.textContent = viewer.showAnnotations ? 'Hide Annotations' : 'Show Annotations';
        btn.className = viewer.showAnnotations ? 
            'px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors' :
            'px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200 transition-colors';
        viewer.renderAnnotations();
    }
}

function prevPage{{ $session->id }}() {
    const viewer = pdfViewers[{{ $session->id }}];
    if (viewer && viewer.currentPage > 1) {
        viewer.renderPage(viewer.currentPage - 1);
        viewer.updateNavButtons();
    }
}

function nextPage{{ $session->id }}() {
    const viewer = pdfViewers[{{ $session->id }}];
    if (viewer && viewer.currentPage < viewer.pdfDoc.numPages) {
        viewer.renderPage(viewer.currentPage + 1);
        viewer.updateNavButtons();
    }
}

function showComment{{ $session->id }}(comment) {
    showCommentModal(comment);
}

function jumpToPage{{ $session->id }}(pageNum) {
    // First, show the PDF viewer if it's hidden
    const pdfViewer = document.getElementById('pdfViewer{{ $session->id }}');
    const showBtn = document.getElementById('showPdfBtn{{ $session->id }}');
    
    if (pdfViewer.classList.contains('hidden')) {
        togglePDFViewer{{ $session->id }}();
        
        // Wait for PDF to load, then jump to page
        setTimeout(() => {
            const viewer = pdfViewers[{{ $session->id }}];
            if (viewer && viewer.pdfDoc) {
                viewer.renderPage(pageNum);
                viewer.updateNavButtons();
                
                // Scroll to PDF viewer
                pdfViewer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 1000);
    } else {
        // PDF is already visible, just jump to page
        const viewer = pdfViewers[{{ $session->id }}];
        if (viewer && viewer.pdfDoc) {
            viewer.renderPage(pageNum);
            viewer.updateNavButtons();
            
            // Scroll to PDF viewer
            pdfViewer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

@endif
@endforeach

// Comment Modal Functions
function showCommentModal(comment) {
    const modal = document.getElementById('commentModal');
    const modalContent = document.getElementById('commentModalContent');
    const commentText = document.getElementById('commentText');
    
    // Set comment text
    commentText.textContent = comment || 'No comment text available.';
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Animate modal appearance
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
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
    }, 300);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('commentModal');
    if (e.target === modal) {
        hideCommentModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideCommentModal();
    }
});

// Initialize viewers when page loads (removed auto-initialization)
document.addEventListener('DOMContentLoaded', function() {
    // PDF viewers will be initialized when user clicks "Show PDF" button
    console.log('Annotation history page loaded. PDF viewers will be initialized on demand.');
});
</script>

<style>
#annotationLayer{{ $session->id ?? '' }} {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
}

.annotation-comment {
    max-width: 250px;
    word-wrap: break-word;
}
</style>
@endsection