<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\StudentReportSubmission;
use App\Models\GroupStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReportSubmissionController extends Controller
{
    /**
     * Get the current student ID
     */
    private function getCurrentStudentId()
    {
        $user = Auth::user();
        
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            $userData = session('external_student_data');
            return $userData['Roll'] ?? $userData['roll'] ?? null;
        }
        
        return $user->roll ?? $user->student_id ?? null;
    }

    /**
     * Verify student belongs to the report's group
     */
    private function verifyStudentAccess(Report $report)
    {
        $studentId = $this->getCurrentStudentId();
        
        $groupStudent = GroupStudent::where('student_id', $studentId)
            ->where('group_id', $report->group_id)
            ->first();
        
        if (!$groupStudent) {
            abort(403, 'You are not authorized to access this report.');
        }
        
        return $studentId;
    }

    /**
     * Show the form for creating a new submission
     */
    public function create(Report $report)
    {
        $studentId = $this->verifyStudentAccess($report);
        
        // Check if student already has a submission for this report
        $existingSubmission = StudentReportSubmission::where('report_id', $report->id)
            ->where('student_id', Auth::id())
            ->first();
        
        if ($existingSubmission) {
            return redirect()->route('student.reports.submissions.show', [$report, $existingSubmission])
                ->with('info', 'You already have a submission for this report. You can edit it below.');
        }
        
        $report->load(['group', 'creator']);
        
        return view('student.reports.submissions.create', compact('report'));
    }

    /**
     * Store a newly created submission
     */
    public function store(Request $request, Report $report)
    {
        $studentId = $this->verifyStudentAccess($report);
        
        // Check if student already has a submission
        $existingSubmission = StudentReportSubmission::where('report_id', $report->id)
            ->where('student_id', Auth::id())
            ->first();
        
        if ($existingSubmission) {
            return redirect()->route('student.reports.submissions.edit', [$report, $existingSubmission])
                ->with('error', 'You already have a submission for this report. Please edit your existing submission.');
        }
        
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:pdf,ppt,pptx', 'max:20480'], // 20MB max for PPT files
        ]);
        
        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            // Generate unique filename with proper extension
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $filePath = 'student-submissions/' . $filename;
            
            // Store the file
            $file->storeAs('student-submissions', $filename, 'public');
            
            // Create submission record
            $submission = StudentReportSubmission::create([
                'report_id' => $report->id,
                'student_id' => Auth::id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'file_path' => $filePath,
                'original_filename' => $originalName,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
            ]);
            
            Log::info('Student submission created', [
                'submission_id' => $submission->id,
                'report_id' => $report->id,
                'student_id' => Auth::id(),
                'file_size' => $fileSize,
            ]);
            
            return redirect()->route('student.reports.show', $report)
                ->with('success', 'Your submission has been uploaded successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to create student submission', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'student_id' => Auth::id(),
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to upload your submission. Please try again.');
        }
    }

    /**
     * Display the specified submission
     */
    public function show(Report $report, StudentReportSubmission $submission)
    {
        $this->verifyStudentAccess($report);
        
        // Verify the submission belongs to the current student
        if ($submission->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this submission.');
        }
        
        $submission->load(['report.group', 'student']);
        
        return view('student.reports.submissions.show', compact('report', 'submission'));
    }

    /**
     * Show the form for editing the specified submission
     */
    public function edit(Report $report, StudentReportSubmission $submission)
    {
        $this->verifyStudentAccess($report);
        
        // Verify the submission belongs to the current student
        if ($submission->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to edit this submission.');
        }
        
        $submission->load(['report.group']);
        
        return view('student.reports.submissions.edit', compact('report', 'submission'));
    }

    /**
     * Update the specified submission
     */
    public function update(Request $request, Report $report, StudentReportSubmission $submission)
    {
        $this->verifyStudentAccess($report);
        
        // Verify the submission belongs to the current student
        if ($submission->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to update this submission.');
        }
        
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,ppt,pptx', 'max:20480'], // 20MB max for PPT files
        ]);
        
        try {
            $updateData = [
                'subject' => $validated['subject'],
                'description' => $validated['description'],
            ];
            
            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
                
                // Delete old file
                if (Storage::disk('public')->exists($submission->file_path)) {
                    Storage::disk('public')->delete($submission->file_path);
                }
                
                // Generate unique filename with proper extension
                $extension = $file->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;
                $filePath = 'student-submissions/' . $filename;
                
                // Store the new file
                $file->storeAs('student-submissions', $filename, 'public');
                
                $updateData = array_merge($updateData, [
                    'file_path' => $filePath,
                    'original_filename' => $originalName,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                ]);
            }
            
            $submission->update($updateData);
            
            Log::info('Student submission updated', [
                'submission_id' => $submission->id,
                'report_id' => $report->id,
                'student_id' => Auth::id(),
                'file_updated' => $request->hasFile('file'),
            ]);
            
            return redirect()->route('student.reports.show', $report)
                ->with('success', 'Your submission has been updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to update student submission', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id,
                'report_id' => $report->id,
                'student_id' => Auth::id(),
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to update your submission. Please try again.');
        }
    }

    /**
     * Remove the specified submission
     */
    public function destroy(Report $report, StudentReportSubmission $submission)
    {
        $this->verifyStudentAccess($report);
        
        // Verify the submission belongs to the current student
        if ($submission->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to delete this submission.');
        }
        
        try {
            $submissionId = $submission->id;
            
            // Delete the submission (file will be deleted automatically via model boot method)
            $submission->delete();
            
            Log::info('Student submission deleted', [
                'submission_id' => $submissionId,
                'report_id' => $report->id,
                'student_id' => Auth::id(),
            ]);
            
            return redirect()->route('student.reports.show', $report)
                ->with('success', 'Your submission has been deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete student submission', [
                'error' => $e->getMessage(),
                'submission_id' => $submission->id,
                'report_id' => $report->id,
                'student_id' => Auth::id(),
            ]);
            
            return back()->with('error', 'Failed to delete your submission. Please try again.');
        }
    }

    /**
     * Download the submission file
     */
    public function download(Report $report, StudentReportSubmission $submission)
    {
        $this->verifyStudentAccess($report);
        
        // Verify the submission belongs to the current student
        if ($submission->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to download this submission.');
        }
        
        if (!Storage::disk('public')->exists($submission->file_path)) {
            return back()->with('error', 'File not found.');
        }
        
        return Storage::disk('public')->download($submission->file_path, $submission->original_filename);
    }
}