<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Report;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApprovalDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $supervisor;
    protected $supervisorUser;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_approved_final_report_shows_approval_status_on_index_page()
    {
        // Create an approved final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Final Report',
            'abstract_md' => 'This is a test abstract',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->supervisorUser->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('✓ Approved');
        $response->assertSee('Approved');
        $response->assertSee($report->status_badge_color);
    }

    public function test_draft_final_report_does_not_show_approval_status()
    {
        // Create a draft final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Draft Report',
            'status' => Report::STATUS_DRAFT,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.index'));

        $response->assertStatus(200);
        $response->assertDontSee('✓ Approved');
        $response->assertSee('Final Report');
    }

    public function test_general_report_does_not_show_approval_status()
    {
        // Create a general report (general reports don't have approval status)
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'status' => Report::STATUS_DRAFT,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.index'));

        $response->assertStatus(200);
        $response->assertDontSee('✓ Approved');
        $response->assertSee('General Report');
    }

    public function test_approved_final_report_shows_congratulations_message_on_detail_page()
    {
        // Create an approved final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Final Report',
            'abstract_md' => 'This is a test abstract',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->supervisorUser->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertSee('🎉 Congratulations! Your Final Report has been Approved');
        $response->assertSee('This report was approved on');
        $response->assertSee('Approved by: Dr. John Smith');
        $response->assertSee('View Public Thesis Page');
        $response->assertSee('Copy Link');
    }

    public function test_draft_final_report_does_not_show_congratulations_message()
    {
        // Create a draft final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Draft Report',
            'status' => Report::STATUS_DRAFT,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertDontSee('🎉 Congratulations! Your Final Report has been Approved');
        $response->assertDontSee('View Public Thesis Page');
    }

    public function test_approved_final_report_shows_status_badge_in_header()
    {
        // Create an approved final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Final Report',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->supervisorUser->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertSee('✓ Approved');
        $response->assertSee('Final Report');
    }

    public function test_approval_status_includes_public_thesis_link()
    {
        // Create an approved final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Final Report',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->supervisorUser->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertSee(route('reports.show', $report));
        $response->assertSee('Your thesis is now publicly available');
    }

    public function test_copy_link_javascript_is_included_for_approved_reports()
    {
        // Create an approved final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Final Report',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->supervisorUser->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertSee('function copyThesisLink()');
        $response->assertSee('navigator.clipboard.writeText');
    }

    public function test_under_review_final_report_does_not_show_approval_status()
    {
        // Create an under review final report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Under Review Report',
            'status' => Report::STATUS_UNDER_REVIEW,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.reports.show', $report));

        $response->assertStatus(200);
        $response->assertDontSee('🎉 Congratulations! Your Final Report has been Approved');
        $response->assertDontSee('✓ Approved');
    }
}