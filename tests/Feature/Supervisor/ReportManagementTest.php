<?php

namespace Tests\Feature\Supervisor;

use App\Models\User;
use App\Models\Supervisor;
use App\Models\Group;
use App\Models\Report;
use App\Models\ReportComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $supervisorUser;
    private Supervisor $supervisor;
    private Group $group;
    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a supervisor user
        $this->supervisorUser = User::factory()->create([
            'login_type' => 'teacher',
            'email' => 'supervisor@test.com',
        ]);

        // Create supervisor record
        $this->supervisor = Supervisor::factory()->create([
            'email' => 'supervisor@test.com',
            'name' => 'Test Supervisor',
        ]);

        // Create a group
        $this->group = Group::factory()->create([
            'supervisor_id' => $this->supervisor->id,
            'name' => 'Test Group',
        ]);

        // Create a report
        $this->report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'created_by' => $this->supervisorUser->id,
        ]);
    }

    /** @test */
    public function supervisor_can_view_edit_form()
    {
        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.edit', $this->report));

        $response->assertStatus(200);
        $response->assertViewIs('supervisor.reports.edit');
        $response->assertViewHas('report', $this->report);
        $response->assertSee('Edit Report');
    }

    /** @test */
    public function supervisor_can_update_report()
    {
        $updateData = [
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Updated Project Title',
            'abstract_md' => 'Updated abstract content',
            'keywords' => 'updated, keywords, test',
            'extra_input' => 'Updated additional notes',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $this->report), $updateData);

        $response->assertRedirect(route('supervisor.reports.show', $this->report));
        $response->assertSessionHas('success', 'Report updated successfully.');

        $this->report->refresh();
        $this->assertEquals('final', $this->report->type);
        $this->assertEquals('Updated Project Title', $this->report->project_title);
        $this->assertEquals('Updated abstract content', $this->report->abstract_md);
        $this->assertEquals('Updated additional notes', $this->report->extra_input);
    }

    /** @test */
    public function supervisor_can_delete_report()
    {
        // Create a comment for the report to test cascade delete
        $comment = ReportComment::factory()->create([
            'report_id' => $this->report->id,
            'teacher_id' => $this->supervisorUser->id,
            'body' => 'Test comment',
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->delete(route('supervisor.reports.destroy', $this->report));

        $response->assertRedirect(route('supervisor.reports.index'));
        $response->assertSessionHas('success', 'Report deleted successfully.');

        // Check that report is soft deleted
        $this->assertSoftDeleted('reports', ['id' => $this->report->id]);
        
        // Check that comment is also deleted (cascade)
        $this->assertDatabaseMissing('report_comments', ['id' => $comment->id]);
    }

    /** @test */
    public function supervisor_cannot_edit_report_from_different_group()
    {
        // Create another supervisor and group
        $otherSupervisor = Supervisor::factory()->create([
            'email' => 'other@test.com',
        ]);
        
        $otherGroup = Group::factory()->create([
            'supervisor_id' => $otherSupervisor->id,
        ]);

        $otherReport = Report::factory()->create([
            'group_id' => $otherGroup->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->get(route('supervisor.reports.edit', $otherReport));

        $response->assertStatus(403);
    }

    /** @test */
    public function supervisor_cannot_delete_report_from_different_group()
    {
        // Create another supervisor and group
        $otherSupervisor = Supervisor::factory()->create([
            'email' => 'other@test.com',
        ]);
        
        $otherGroup = Group::factory()->create([
            'supervisor_id' => $otherSupervisor->id,
        ]);

        $otherReport = Report::factory()->create([
            'group_id' => $otherGroup->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        $response = $this->actingAs($this->supervisorUser)
            ->delete(route('supervisor.reports.destroy', $otherReport));

        $response->assertStatus(403);
    }

    /** @test */
    public function update_requires_valid_data()
    {
        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $this->report), [
                'group_id' => '',
                'type' => 'invalid_type',
            ]);

        $response->assertSessionHasErrors(['group_id', 'type']);
    }

    /** @test */
    public function final_report_requires_project_title_and_abstract()
    {
        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $this->report), [
                'group_id' => $this->group->id,
                'type' => 'final',
                'project_title' => '',
                'abstract_md' => '',
            ]);

        $response->assertSessionHasErrors(['project_title', 'abstract_md']);
    }
}