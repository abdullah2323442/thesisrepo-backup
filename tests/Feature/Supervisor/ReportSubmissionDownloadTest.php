<?php

namespace Tests\Feature\Supervisor;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Report;
use App\Models\StudentReportSubmission;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSubmissionDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected $supervisor;
    protected $supervisorUser;
    protected $student;
    protected $group;
    protected $report;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Create supervisor
        $this->supervisor = Supervisor::factory()->create([
            'name' => 'Dr. John Smith',
            'email' => 'supervisor@example.com',
        ]);

        $this->supervisorUser = User::factory()->create([
            'name' => 'Dr. John Smith',
            'email' => 'supervisor@example.com',
            'login_type' => 'teacher',
        ]);

        // Create student
        $this->student = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'student@example.com',
            'roll' => '2021001',
            'login_type' => 'student',
        ]);

        // Create group
        $this->group = Group::factory()->create([
            'name' => 'Group 1',
            'supervisor_id' => $this->supervisor->id,
        ]);

        // Add student to group
        GroupStudent::create([
            'group_id' => $this->group->id,
            'student_id' => $this->student->roll,
        ]);

        // Create report
        $this->report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'created_by' => $this->supervisorUser->id,
        ]);
    }

    public function test_supervisor_can_see_pdf_submissions_with_download_buttons()
    {
        // Create a PDF submission
        $submission = StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'Final Report PDF',
            'description' => 'My final report submission',
            'file_path' => 'submissions/test.pdf',
            'original_filename' => 'final_report.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Final Report PDF');
        $response->assertSee('PDF Document');
        $response->assertSee('PDF'); // Download button text
        $response->assertSee('John Doe'); // Student name
        $response->assertSee('1 submission');
    }

    public function test_supervisor_can_see_powerpoint_submissions_with_download_buttons()
    {
        // Create a PowerPoint submission
        $submission = StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'Final Presentation',
            'description' => 'My final presentation',
            'file_path' => 'submissions/test.pptx',
            'original_filename' => 'final_presentation.pptx',
            'file_size' => 2048000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Final Presentation');
        $response->assertSee('PowerPoint Presentation');
        $response->assertSee('PPT'); // Download button text
        $response->assertSee('John Doe'); // Student name
        $response->assertSee('1 submission');
    }

    public function test_supervisor_can_see_multiple_submissions_from_different_students()
    {
        // Create another student
        $student2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'student2@example.com',
            'roll' => '2021002',
            'login_type' => 'student',
        ]);

        // Add second student to group
        GroupStudent::create([
            'group_id' => $this->group->id,
            'student_id' => $student2->roll,
        ]);

        // Create PDF submission from first student
        StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'Report PDF',
            'file_path' => 'submissions/report.pdf',
            'original_filename' => 'report.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
        ]);

        // Create PowerPoint submission from second student
        StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $student2->id,
            'subject' => 'Presentation PPT',
            'file_path' => 'submissions/presentation.pptx',
            'original_filename' => 'presentation.pptx',
            'file_size' => 2048000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('2 submissions');
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
        $response->assertSee('Report PDF');
        $response->assertSee('Presentation PPT');
        $response->assertSee('PDF Document');
        $response->assertSee('PowerPoint Presentation');
    }

    public function test_supervisor_can_see_file_sizes_and_timestamps()
    {
        $submission = StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'Test Submission',
            'file_path' => 'submissions/test.pdf',
            'original_filename' => 'test.pdf',
            'file_size' => 1536000, // 1.5 MB
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('1.5 MB');
        $response->assertSee('ago'); // Timestamp should show relative time
    }

    public function test_supervisor_cannot_see_submissions_from_other_groups()
    {
        // Create another supervisor and group
        $otherSupervisor = Supervisor::factory()->create([
            'name' => 'Dr. Jane Doe',
            'email' => 'other@example.com',
        ]);

        $otherGroup = Group::factory()->create([
            'name' => 'Other Group',
            'supervisor_id' => $otherSupervisor->id,
        ]);

        $otherReport = Report::factory()->create([
            'group_id' => $otherGroup->id,
            'type' => 'final',
            'created_by' => $this->supervisorUser->id,
        ]);

        // Create submission for other group
        StudentReportSubmission::create([
            'report_id' => $otherReport->id,
            'student_id' => $this->student->id,
            'subject' => 'Other Group Submission',
            'file_path' => 'submissions/other.pdf',
            'original_filename' => 'other.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Other Group Submission');
        $response->assertDontSee('Other Group');
    }

    public function test_download_buttons_have_correct_colors_and_icons()
    {
        // Create PDF submission
        StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'PDF Report',
            'file_path' => 'submissions/report.pdf',
            'original_filename' => 'report.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
        ]);

        // Create PowerPoint submission
        StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'PPT Presentation',
            'file_path' => 'submissions/presentation.pptx',
            'original_filename' => 'presentation.pptx',
            'file_size' => 2048000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        
        // Check PDF button styling
        $response->assertSee('bg-red-600'); // PDF button color
        $response->assertSee('hover:bg-red-700');
        
        // Check PowerPoint button styling
        $response->assertSee('bg-orange-600'); // PowerPoint button color
        $response->assertSee('hover:bg-orange-700');
    }

    public function test_reports_without_submissions_dont_show_submission_section()
    {
        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Student Submissions:');
        $response->assertDontSee('submission'); // Should not show submission count
    }

    public function test_submission_section_shows_correct_file_type_icons()
    {
        // Create PDF submission
        StudentReportSubmission::create([
            'report_id' => $this->report->id,
            'student_id' => $this->student->id,
            'subject' => 'PDF Report',
            'file_path' => 'submissions/report.pdf',
            'original_filename' => 'report.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.index'));

        $response->assertStatus(200);
        
        // Check for PDF icon (red color)
        $response->assertSee('text-red-600');
        
        // Check for file icon SVG paths
        $response->assertSee('fill-rule="evenodd"');
    }
}