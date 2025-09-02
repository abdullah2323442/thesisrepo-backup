<?php

namespace Tests\Feature\Supervisor;

use App\Models\User;
use App\Models\Supervisor;
use App\Models\Group;
use App\Models\Report;
use App\Models\GroupStudent;
use App\Notifications\NewReportAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReportMessagingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $supervisorUser;
    private Supervisor $supervisor;
    private Group $group;
    private User $studentUser;

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

        // Create a student user
        $this->studentUser = User::factory()->create([
            'login_type' => 'student',
            'roll' => 'STU001',
            'email' => 'student@test.com',
        ]);

        // Add student to group
        GroupStudent::factory()->create([
            'group_id' => $this->group->id,
            'student_id' => 'STU001',
            'student_name' => $this->studentUser->name,
        ]);
    }

    /** @test */
    public function supervisor_can_create_report_with_message()
    {
        Notification::fake();

        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Please submit your progress report by Friday. Include details about your research methodology and current findings.',
            'extra_input' => 'Additional notes here',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that report was created with message
        $report = Report::latest()->first();
        $this->assertEquals('Please submit your progress report by Friday. Include details about your research methodology and current findings.', $report->supervisor_message);
        $this->assertEquals('general', $report->type);

        // Check that notification was sent
        Notification::assertSentTo(
            $this->studentUser,
            NewReportAssigned::class,
            function ($notification) use ($report) {
                $data = $notification->toArray($this->studentUser);
                return $data['report_id'] === $report->id &&
                       str_contains($data['message'], 'Please submit your progress report by Friday');
            }
        );
    }

    /** @test */
    public function supervisor_can_update_report_message()
    {
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Original message',
            'created_by' => $this->supervisorUser->id,
        ]);

        $updateData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Updated message with new instructions',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $report), $updateData);

        $response->assertRedirect(route('supervisor.reports.show', $report));
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('Updated message with new instructions', $report->supervisor_message);
    }

    /** @test */
    public function notification_includes_supervisor_message()
    {
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'final',
            'project_title' => 'Test Project',
            'supervisor_message' => 'Please focus on the implementation details and testing results.',
            'created_by' => $this->supervisorUser->id,
        ]);

        $notification = new NewReportAssigned($report);
        $data = $notification->toArray($this->studentUser);

        $this->assertEquals('report_assigned', $data['type']);
        $this->assertEquals('Final Report Assigned', $data['title']);
        $this->assertStringContainsString('Please focus on the implementation details and testing results.', $data['message']);
        $this->assertEquals('Please focus on the implementation details and testing results.', $data['supervisor_message']);
    }

    /** @test */
    public function report_can_be_created_without_message()
    {
        Notification::fake();

        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'extra_input' => 'Some additional notes',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report = Report::latest()->first();
        $this->assertNull($report->supervisor_message);

        // Check that notification was still sent
        Notification::assertSentTo(
            $this->studentUser,
            NewReportAssigned::class,
            function ($notification) use ($report) {
                $data = $notification->toArray($this->studentUser);
                return $data['report_id'] === $report->id &&
                       !str_contains($data['message'], 'Supervisor Instructions:');
            }
        );
    }

    /** @test */
    public function supervisor_message_validation()
    {
        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => str_repeat('a', 2001), // Exceeds max length
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        $response->assertSessionHasErrors(['supervisor_message']);
    }
}