@extends('layouts.student')

@section('page-title', 'View Annotations - ' . $submission->subject)
@section('page-description', 'View supervisor annotations and feedback')

@section('content')
<div class="container mx-auto max-w-7xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    @php
                        $createdByType = $annotationSession->created_by_type ?? 'supervisor';
                        $roleTitle = match($createdByType) {
                            'co_supervisor' => 'Co-Supervisor',
                            'panel_member' => 'Panel Member',
                            default => 'Supervisor'
                        };
                    @endphp
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $roleTitle }} Annotations</h1>
                    <div class="text-sm text-gray-600">
                        <span><strong>Report:</strong> {{ $report->project_title ?: 'Report for ' . $report->group->name }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Your Submission:</strong> {{ $submission->subject }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Annotated by:</strong> {{ $roleTitle }} {{ $annotationSession->supervisor->name }}</span>
                        <span class="mx-2">•</span>
                        <span><strong>Date:</strong> {{ $annotationSession->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('student.reports.submissions.annotations.download', [$report, $submission, $annotationSession]) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Annotated PDF
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

            <!-- Supervisor's General Feedback -->
            @if($annotationSession->general_comment)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800 mb-1">General Feedback from {{ $roleTitle }}:</p>
                        <p class="text-blue-700">{{ $annotationSession->general_comment }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- PDF Viewer -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">Viewing Mode: Read-Only</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                            {{ count($annotations) }} Annotations
                        </span>
                    </div>
                    <button id="toggleAnnotations" class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors">
                        Hide Annotations
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button id="zoomOut" class="px-2 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">-</button>
                    <span id="zoomLevel" class="text-sm font-medium">100%</span>
                    <button id="zoomIn" class="px-2 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">+</button>
                </div>
            </div>

            <!-- PDF Viewer Container -->
            <div class="border border-gray-300 rounded-lg overflow-hidden" style="height: 800px;">
                <div id="pdfContainer" class="relative w-full h-full overflow-auto bg-gray-100">
                    <canvas id="pdfCanvas" class="mx-auto block"></canvas>
                    <div id="annotationLayer" class="absolute top-0 left-0 w-full h-full pointer-events-none"></div>
                </div>
            </div>

            <!-- Page Navigation -->
            <div class="flex items-center justify-center gap-4 mt-4 p-4 bg-gray-50 rounded-lg">
                <button id="prevPage" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <span class="text-sm">
                    Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                </span>
                <button id="nextPage" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>

    <!-- Annotations Summary -->
    <div class="bg-white rounded-lg shadow-md mt-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Annotation Summary</h3>
            <div id="annotationsList" class="space-y-3">
                <!-- Annotations will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-700">Loading annotated PDF...</p>
    </div>
</div>

@endsection

@push('scripts')
<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// PDF Annotation Viewer (Read-only)
class PDFAnnotationViewer {
    constructor() {
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.scale = 1.0;
        this.annotations = @json($annotations);
        this.showAnnotations = true;
        
        this.init();
    }

    async init() {
        // Set PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        // Load PDF
        await this.loadPDF();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Populate annotations list
        this.updateAnnotationsList();
        
        // Hide loading overlay
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    async loadPDF() {
        try {
            const pdfUrl = '{{ route("student.reports.submissions.download", [$report, $submission]) }}';
            
            // Load with PDF.js for rendering
            this.pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
            this.totalPages = this.pdfDoc.numPages;
            
            // Update UI
            document.getElementById('totalPages').textContent = this.totalPages;
            
            // Render first page
            await this.renderPage(1);
            
        } catch (error) {
            console.error('Error loading PDF:', error);
            alert('Error loading PDF. Please try again.');
        }
    }

    async renderPage(pageNum) {
        const page = await this.pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: this.scale });
        
        const canvas = document.getElementById('pdfCanvas');
        const context = canvas.getContext('2d');
        
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        // Render PDF page
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        
        // Update annotation layer size
        const annotationLayer = document.getElementById('annotationLayer');
        annotationLayer.style.width = viewport.width + 'px';
        annotationLayer.style.height = viewport.height + 'px';
        
        // Re-render annotations for current page
        this.renderAnnotations();
        
        // Update page number
        this.currentPage = pageNum;
        document.getElementById('currentPage').textContent = pageNum;
        
        // Update navigation buttons
        document.getElementById('prevPage').disabled = pageNum <= 1;
        document.getElementById('nextPage').disabled = pageNum >= this.totalPages;
    }

    setupEventListeners() {
        // Navigation
        document.getElementById('prevPage').addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.renderPage(this.currentPage - 1);
            }
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.renderPage(this.currentPage + 1);
            }
        });

        // Zoom
        document.getElementById('zoomIn').addEventListener('click', () => {
            this.scale = Math.min(this.scale + 0.25, 3.0);
            this.renderPage(this.currentPage);
            document.getElementById('zoomLevel').textContent = Math.round(this.scale * 100) + '%';
        });

        document.getElementById('zoomOut').addEventListener('click', () => {
            this.scale = Math.max(this.scale - 0.25, 0.5);
            this.renderPage(this.currentPage);
            document.getElementById('zoomLevel').textContent = Math.round(this.scale * 100) + '%';
        });

        // Toggle annotations
        document.getElementById('toggleAnnotations').addEventListener('click', () => {
            this.showAnnotations = !this.showAnnotations;
            const btn = document.getElementById('toggleAnnotations');
            btn.textContent = this.showAnnotations ? 'Hide Annotations' : 'Show Annotations';
            btn.className = this.showAnnotations ? 
                'px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors' :
                'px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200 transition-colors';
            this.renderAnnotations();
        });
    }

    renderAnnotations() {
        const layer = document.getElementById('annotationLayer');
        layer.innerHTML = '';

        if (!this.showAnnotations) return;

        this.annotations
            .filter(ann => ann.page === this.currentPage)
            .forEach(annotation => {
                const element = document.createElement('div');
                element.style.position = 'absolute';
                element.style.pointerEvents = 'auto';
                element.style.cursor = 'pointer';

                if (annotation.type === 'comment') {
                    element.innerHTML = `
                        <div class="w-6 h-6 bg-yellow-400 rounded-full border-2 border-yellow-600 flex items-center justify-center text-xs font-bold hover:bg-yellow-300 transition-colors" 
                             title="Click to view comment">
                            💬
                        </div>
                    `;
                    element.style.left = annotation.position.x + 'px';
                    element.style.top = annotation.position.y + 'px';
                    
                    element.addEventListener('click', () => {
                        alert('Supervisor Comment:\n\n' + annotation.text);
                    });
                } else {
                    const left = Math.min(annotation.start.x, annotation.end.x);
                    const top = Math.min(annotation.start.y, annotation.end.y);
                    const width = Math.abs(annotation.end.x - annotation.start.x);
                    const height = Math.abs(annotation.end.y - annotation.start.y);

                    element.style.left = left + 'px';
                    element.style.top = top + 'px';
                    element.style.width = width + 'px';
                    element.style.height = height + 'px';
                    element.style.backgroundColor = annotation.color + '60';
                    element.style.pointerEvents = 'none';
                    
                    if (annotation.type === 'underline') {
                        element.style.borderBottom = '3px solid ' + annotation.color;
                        element.style.backgroundColor = 'transparent';
                    } else if (annotation.type === 'strikethrough') {
                        element.style.borderTop = '2px solid ' + annotation.color;
                        element.style.backgroundColor = 'transparent';
                        element.style.top = (top + height/2) + 'px';
                    }
                }

                layer.appendChild(element);
            });
    }

    updateAnnotationsList() {
        const container = document.getElementById('annotationsList');
        container.innerHTML = '';

        if (this.annotations.length === 0) {
            container.innerHTML = '<p class="text-gray-500 italic">No annotations found.</p>';
            return;
        }

        // Group annotations by page
        const annotationsByPage = {};
        this.annotations.forEach(annotation => {
            if (!annotationsByPage[annotation.page]) {
                annotationsByPage[annotation.page] = [];
            }
            annotationsByPage[annotation.page].push(annotation);
        });

        // Display annotations grouped by page
        Object.keys(annotationsByPage).sort((a, b) => parseInt(a) - parseInt(b)).forEach(page => {
            const pageHeader = document.createElement('div');
            pageHeader.className = 'font-semibold text-gray-800 mb-2 mt-4 first:mt-0';
            pageHeader.innerHTML = `Page ${page} (${annotationsByPage[page].length} annotations)`;
            container.appendChild(pageHeader);

            annotationsByPage[page].forEach(annotation => {
                const item = document.createElement('div');
                item.className = 'flex items-start gap-3 p-3 bg-gray-50 rounded-lg ml-4 cursor-pointer hover:bg-gray-100 transition-colors';
                
                const typeIcon = annotation.type === 'comment' ? '💬' : '🖍️';
                const typeText = annotation.type.charAt(0).toUpperCase() + annotation.type.slice(1);
                
                item.innerHTML = `
                    <div class="flex-shrink-0 text-lg">${typeIcon}</div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-700">${typeText}</span>
                            <div class="w-4 h-4 rounded" style="background-color: ${annotation.color}"></div>
                        </div>
                        ${annotation.text ? `<p class="text-sm text-gray-600">${annotation.text}</p>` : ''}
                        <p class="text-xs text-gray-400 mt-1">Click to navigate to this annotation</p>
                    </div>
                `;
                
                // Navigate to annotation when clicked
                item.addEventListener('click', () => {
                    if (this.currentPage !== parseInt(page)) {
                        this.renderPage(parseInt(page));
                    }
                });
                
                container.appendChild(item);
            });
        });
    }
}

// Initialize the viewer when the page loads
let viewer;
document.addEventListener('DOMContentLoaded', () => {
    viewer = new PDFAnnotationViewer();
});
</script>

<style>
#pdfContainer {
    position: relative;
}

#annotationLayer {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 5;
}

#pdfCanvas {
    display: block;
    margin: 0 auto;
}
</style>
@endpush