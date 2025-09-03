<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Report;
use App\Models\ReportComment;
use App\Models\Supervisor;
use App\Notifications\NewReportAssigned;
use App\Notifications\ReportUpdated;
use App\Notifications\NewReportComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GroupNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $supervisor;
    protected $supervisorUser;
    protected $group;
    protected $students;
    protected $studentUsers;

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

        // Create group
        $this->group = Group::factory()->create([
            'name' => 'Group A',
            'supervisor_id' => $this->supervisor->id,
        ]);

        // Create students and add them to the group
        $this->studentUsers = collect();
        for ($i = 1; $i <= 3; $i++) {
            $studentUser = User::factory()->create([
                'name' => "Student {$i}",
                'email' => "student{$i}@example.com",
                'roll' => "2021{$i:03d}",
                'login_type' => 'student',
            ]);

            GroupStudent::create([
                'group_id' => $this->group->id,
                'student_id' => $studentUser->roll,
            ]);

            $this->studentUsers->push($studentUser);
        }
    }

    public function test_all_group_members_receive_notification_when_report_is_created()
    {
        Notification::fake();

        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Please submit your progress report by next week.',
            'extra_input' => 'Include all research findings.',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that all students received the notification
        foreach ($this->studentUsers as $student) {
            Notification::assertSentTo(
                $student,
                NewReportAssigned::class,
                function ($notification) {
                    $data = $notification->toArray($this->studentUsers->first());
                    return $data['type'] === 'report_assigned' &&
                           str_contains($data['message'], 'Please submit your progress report by next week.');
                }
            );
        }

        // Verify notification count
        Notification::assertSentTimes(NewReportAssigned::class, 3);
    }

    public function test_all_group_members_receive_notification_when_report_is_updated()
    {
        Notification::fake();

        // First create a report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Original message',
            'created_by' => $this->supervisorUser->id,
        ]);

        // Update the report
        $updateData = [
            'group_id' => $this->group->id,
            'type' => 'final',
            'supervisor_message' => 'Updated message with new requirements',
            'extra_input' => 'New additional requirements',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $report), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that all students received the update notification
        foreach ($this->studentUsers as $student) {
            Notification::assertSentTo(
                $student,
                ReportUpdated::class,
                function ($notification) {
                    $data = $notification->toArray($this->studentUsers->first());
                    return $data['type'] === 'report_updated' &&
                           str_contains($data['message'], 'has been updated by your supervisor');
                }
            );
        }

        // Verify notification count
        Notification::assertSentTimes(ReportUpdated::class, 3);
    }

    public function test_no_notification_sent_when_report_update_has_no_changes()
    {
        Notification::fake();

        // First create a report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Original message',
            'created_by' => $this->supervisorUser->id,
        ]);

        // Update the report with same data (no changes)
        $updateData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Original message',
            'extra_input' => null,
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $report), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that no update notifications were sent
        Notification::assertNotSentTo($this->studentUsers->first(), ReportUpdated::class);
    }

    public function test_all_group_members_receive_notification_when_comment_is_added()
    {
        Notification::fake();

        // Create a report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'created_by' => $this->supervisorUser->id,
        ]);

        // Add a comment
        $commentData = [
            'body' => 'Great work on the research methodology. Please add more details to the conclusion section.',
        ];

        $response = $this->actingAs($this->supervisorUser)
            ->post(route('teacher.reports.comments.store', $report), $commentData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify that all students received the comment notification
        foreach ($this->studentUsers as $student) {
            Notification::assertSentTo(
                $student,
                NewReportComment::class,
                function ($notification) {
                    $data = $notification->toArray($this->studentUsers->first());
                    return $data['type'] === 'report_comment' &&
                           str_contains($data['comment_preview'], 'Great work on the research methodology');
                }
            );
        }

        // Verify notification count
        Notification::assertSentTimes(NewReportComment::class, 3);
    }

    public function test_notification_data_contains_correct_information()
    {
        Notification::fake();

        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'final',
            'supervisor_message' => 'This is your final report submission.',
            'extra_input' => 'Include all appendices.',
        ];

        $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        Notification::assertSentTo(
            $this->studentUsers->first(),
            NewReportAssigned::class,
            function ($notification) {
                $data = $notification->toArray($this->studentUsers->first());
                
                return $data['type'] === 'report_assigned' &&
                       $data['report_type'] === 'final' &&
                       $data['group_name'] === 'Group A' &&
                       $data['title'] === 'Final Report Assigned' &&
                       str_contains($data['message'], 'This is your final report submission.') &&
                       $data['created_by'] === 'Dr. John Smith';
            }
        );
    }

    public function test_update_notification_includes_change_details()
    {
        Notification::fake();

        // Create a report
        $report = Report::factory()->create([
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Original message',
            'extra_input' => 'Original requirements',
            'created_by' => $this->supervisorUser->id,
        ]);

        // Update multiple fields
        $updateData = [
            'group_id' => $this->group->id,
            'type' => 'final',
            'supervisor_message' => 'Updated message',
            'extra_input' => 'Updated requirements',
        ];

        $this->actingAs($this->supervisorUser)
            ->put(route('supervisor.reports.update', $report), $updateData);

        Notification::assertSentTo(
            $this->studentUsers->first(),
            ReportUpdated::class,
            function ($notification) {
                $data = $notification->toArray($this->studentUsers->first());
                
                return $data['type'] === 'report_updated' &&
                       isset($data['changes']) &&
                       array_key_exists('type', $data['changes']) &&
                       array_key_exists('supervisor_message', $data['changes']) &&
                       array_key_exists('extra_input', $data['changes']);
            }
        );
    }

    public function test_notifications_work_with_different_student_id_formats()
    {
        Notification::fake();

        // Create a student with student_id instead of roll
        $studentWithStudentId = User::factory()->create([
            'name' => 'Student with ID',
            'email' => 'student_id@example.com',
            'student_id' => '2021999',
            'roll' => null,
            'login_type' => 'student',
        ]);

        GroupStudent::create([
            'group_id' => $this->group->id,
            'student_id' => '2021999',
        ]);

        $reportData = [
            'group_id' => $this->group->id,
            'type' => 'general',
            'supervisor_message' => 'Test message',
        ];

        $this->actingAs($this->supervisorUser)
            ->post(route('supervisor.reports.store'), $reportData);

        // Verify that the student with student_id also received notification
        Notification::assertSentTo($studentWithStudentId, NewReportAssigned::class);
        
        // Total notifications should be 4 (3 original students + 1 new student)
        Notification::assertSentTimes(NewReportAssigned::class, 4);
    }
}