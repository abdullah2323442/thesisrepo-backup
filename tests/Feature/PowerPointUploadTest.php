<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Report;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\StudentReportSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PowerPointUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $group;
    protected $report;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a student user
        $this->student = User::factory()->create([
            'login_type' => 'student',
            'roll' => '2021001',
        ]);

        // Create a group
        $this->group = Group::factory()->create([
            'name' => 'Group 1',
            'batch_number' => 2021,
        ]);

        // Add student to group
        GroupStudent::create([
            'group_id' => $this->group->id,
            'student_id' => $this->student->roll,
        ]);

        // Create a report
        $this->report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Report',
        ]);

        Storage::fake('public');
    }

    public function test_student_can_upload_powerpoint_file()
    {
        // Create a fake PowerPoint file
        $file = UploadedFile::fake()->create('presentation.pptx', 1024, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'My PowerPoint Presentation',
                'description' => 'This is a test PowerPoint submission',
                'file' => $file,
            ]);

        $response->assertRedirect(route('student.reports.show', $this->report));
        $response->assertSessionHas('success');

        // Verify submission was created
        $this->assertDatabaseHas('student_report_submissions', [
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'My PowerPoint Presentation',
            'original_filename' => 'presentation.pptx',
        ]);

        // Verify file was stored
        $submission = StudentReportSubmission::where('report_id', $this->report->id)->first();
        Storage::disk('public')->assertExists($submission->file_path);
    }

    public function test_student_can_upload_ppt_file()
    {
        // Create a fake PPT file
        $file = UploadedFile::fake()->create('presentation.ppt', 1024, 'application/vnd.ms-powerpoint');

        $response = $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'My PPT Presentation',
                'description' => 'This is a test PPT submission',
                'file' => $file,
            ]);

        $response->assertRedirect(route('student.reports.show', $this->report));
        $response->assertSessionHas('success');

        // Verify submission was created
        $this->assertDatabaseHas('student_report_submissions', [
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'My PPT Presentation',
            'original_filename' => 'presentation.ppt',
        ]);
    }

    public function test_student_can_still_upload_pdf_file()
    {
        // Create a fake PDF file
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'My PDF Document',
                'description' => 'This is a test PDF submission',
                'file' => $file,
            ]);

        $response->assertRedirect(route('student.reports.show', $this->report));
        $response->assertSessionHas('success');

        // Verify submission was created
        $this->assertDatabaseHas('student_report_submissions', [
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'My PDF Document',
            'original_filename' => 'document.pdf',
        ]);
    }

    public function test_invalid_file_type_is_rejected()
    {
        // Create a fake invalid file
        $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

        $response = $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'My Text Document',
                'description' => 'This should fail',
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');

        // Verify no submission was created
        $this->assertDatabaseMissing('student_report_submissions', [
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_file_size_limit_is_enforced()
    {
        // Create a fake file that's too large (25MB)
        $file = UploadedFile::fake()->create('large_presentation.pptx', 25600, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'Large Presentation',
                'description' => 'This should fail due to size',
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');

        // Verify no submission was created
        $this->assertDatabaseMissing('student_report_submissions', [
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
        ]);
    }

    public function test_student_can_update_submission_with_powerpoint()
    {
        // First create a submission with PDF
        $pdfFile = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');
        
        $this->actingAs($this->student)
            ->post(route('student.reports.submissions.store', $this->report), [
                'subject' => 'Original PDF',
                'description' => 'Original submission',
                'file' => $pdfFile,
            ]);

        $submission = StudentReportSubmission::where('report_id', $this->report->id)->first();

        // Now update with PowerPoint
        $pptFile = UploadedFile::fake()->create('updated_presentation.pptx', 1024, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->actingAs($this->student)
            ->put(route('student.reports.submissions.update', [$this->report, $submission]), [
                'subject' => 'Updated PowerPoint',
                'description' => 'Updated with PowerPoint',
                'file' => $pptFile,
            ]);

        $response->assertRedirect(route('student.reports.show', $this->report));
        $response->assertSessionHas('success');

        // Verify submission was updated
        $submission->refresh();
        $this->assertEquals('Updated PowerPoint', $submission->subject);
        $this->assertEquals('updated_presentation.pptx', $submission->original_filename);
        $this->assertStringContains('pptx', $submission->file_path);
    }
}