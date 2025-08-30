<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Batch;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\AssignmentHistory;
use App\Models\AdminCreatedGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class ComprehensiveModelTest extends TestCase
{
    use RefreshDatabase;

    // ========== USER MODEL TESTS ==========

    public function test_user_model_attributes()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_type' => 'student',
            'roll' => '2020123456',
            'batch' => 2020,
            'department_name' => 'Computer Science',
            'program_name' => 'Bachelor of Science',
            'type_id' => json_encode(['2'])
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('student', $user->login_type);
        $this->assertEquals('2020123456', $user->roll);
        $this->assertEquals(2020, $user->batch);
        $this->assertEquals('Computer Science', $user->department_name);
        $this->assertEquals('Bachelor of Science', $user->program_name);
        $this->assertEquals(['2'], $user->type_ids);
    }

    public function test_user_role_methods()
    {
        $admin = User::factory()->create(['type_id' => json_encode(['1'])]);
        $teacher = User::factory()->create(['type_id' => json_encode(['2'])]);
        $advisor = User::factory()->create(['type_id' => json_encode(['3'])]);
        $student = User::factory()->create(['login_type' => 'student']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());
        $this->assertFalse($admin->isAdvisor());
        $this->assertFalse($admin->isStudent());

        $this->assertFalse($teacher->isAdmin());
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAdvisor());
        $this->assertFalse($teacher->isStudent());

        $this->assertFalse($advisor->isAdmin());
        $this->assertFalse($advisor->isTeacher());
        $this->assertTrue($advisor->isAdvisor());
        $this->assertFalse($advisor->isStudent());

        $this->assertFalse($student->isAdmin());
        $this->assertFalse($student->isTeacher());
        $this->assertFalse($student->isAdvisor());
        $this->assertTrue($student->isStudent());
    }

    public function test_user_profile_image_accessor()
    {
        $userWithImage = User::factory()->create([
            'name' => 'John Doe',
            'profile_image_url' => 'https://example.com/image.jpg'
        ]);

        $userWithoutImage = User::factory()->create([
            'name' => 'Jane Doe',
            'profile_image_url' => null
        ]);

        $this->assertEquals('https://example.com/image.jpg', $userWithImage->profile_image);
        $this->assertStringContains('ui-avatars.com', $userWithoutImage->profile_image);
        $this->assertStringContains('Jane+Doe', $userWithoutImage->profile_image);
    }

    public function test_user_relationships()
    {
        $advisor = User::factory()->create();
        $student = User::factory()->create();
        
        $group = Group::factory()->create(['advisor_id' => $advisor->id]);
        $groupStudent = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $student->id
        ]);

        $this->assertInstanceOf(Collection::class, $advisor->advisedGroups);
        $this->assertTrue($advisor->advisedGroups->contains($group));
        
        $this->assertInstanceOf(Collection::class, $student->groupStudents);
        $this->assertTrue($student->groupStudents->contains($groupStudent));
    }

    // ========== AREA OF INTEREST MODEL TESTS ==========

    public function test_area_of_interest_model_attributes()
    {
        $area = AreaOfInterest::factory()->create([
            'name' => 'Machine Learning',
            'description' => 'AI and ML research',
            'is_active' => true
        ]);

        $this->assertEquals('Machine Learning', $area->name);
        $this->assertEquals('AI and ML research', $area->description);
        $this->assertTrue($area->is_active);
    }

    public function test_area_of_interest_scopes()
    {
        AreaOfInterest::factory()->count(3)->create(['is_active' => true]);
        AreaOfInterest::factory()->count(2)->create(['is_active' => false]);

        $this->assertEquals(3, AreaOfInterest::active()->count());
        $this->assertEquals(2, AreaOfInterest::inactive()->count());
    }

    public function test_area_of_interest_static_methods()
    {
        AreaOfInterest::factory()->count(5)->create(['is_active' => true]);
        AreaOfInterest::factory()->count(3)->create(['is_active' => false]);

        $stats = AreaOfInterest::getStats();
        
        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(5, $stats['active']);
        $this->assertEquals(3, $stats['inactive']);
    }

    public function test_area_of_interest_relationships()
    {
        $area = AreaOfInterest::factory()->create();
        $group = Group::factory()->create(['area_of_interest_id' => $area->id]);

        $this->assertInstanceOf(Collection::class, $area->groups);
        $this->assertTrue($area->groups->contains($group));
    }

    public function test_area_of_interest_casts()
    {
        $area = AreaOfInterest::factory()->create(['is_active' => 1]);
        
        $this->assertIsBool($area->is_active);
        $this->assertTrue($area->is_active);
    }

    // ========== SUPERVISOR MODEL TESTS ==========

    public function test_supervisor_model_attributes()
    {
        $supervisor = Supervisor::factory()->create([
            'name' => 'Dr. Smith',
            'email' => 'smith@university.edu',
            'api_id' => 123,
            'max_groups' => 5,
            'current_groups' => 2,
            'is_active' => true
        ]);

        $this->assertEquals('Dr. Smith', $supervisor->name);
        $this->assertEquals('smith@university.edu', $supervisor->email);
        $this->assertEquals(123, $supervisor->api_id);
        $this->assertEquals(5, $supervisor->max_groups);
        $this->assertEquals(2, $supervisor->current_groups);
        $this->assertTrue($supervisor->is_active);
    }

    public function test_supervisor_scopes()
    {
        Supervisor::factory()->count(4)->create(['is_active' => true]);
        Supervisor::factory()->count(2)->create(['is_active' => false]);

        $this->assertEquals(4, Supervisor::active()->count());
        $this->assertEquals(2, Supervisor::inactive()->count());
    }

    public function test_supervisor_available_slots_accessor()
    {
        $supervisor = Supervisor::factory()->create([
            'max_groups' => 5,
            'current_groups' => 2
        ]);

        $this->assertEquals(3, $supervisor->available_slots);
    }

    public function test_supervisor_is_available_method()
    {
        $availableSupervisor = Supervisor::factory()->create([
            'max_groups' => 5,
            'current_groups' => 3,
            'is_active' => true
        ]);

        $unavailableSupervisor = Supervisor::factory()->create([
            'max_groups' => 3,
            'current_groups' => 3,
            'is_active' => true
        ]);

        $inactiveSupervisor = Supervisor::factory()->create([
            'max_groups' => 5,
            'current_groups' => 1,
            'is_active' => false
        ]);

        $this->assertTrue($availableSupervisor->isAvailable());
        $this->assertFalse($unavailableSupervisor->isAvailable());
        $this->assertFalse($inactiveSupervisor->isAvailable());
    }

    public function test_supervisor_relationships()
    {
        $supervisor = Supervisor::factory()->create();
        $group = Group::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertInstanceOf(Collection::class, $supervisor->groups);
        $this->assertTrue($supervisor->groups->contains($group));
    }

    public function test_supervisor_static_methods()
    {
        Supervisor::factory()->count(6)->create(['is_active' => true]);
        Supervisor::factory()->count(2)->create(['is_active' => false]);

        $stats = Supervisor::getStats();
        
        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(6, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    // ========== BATCH MODEL TESTS ==========

    public function test_batch_model_attributes()
    {
        $batch = Batch::factory()->create([
            'batch_number' => 2020,
            'batch_name' => 'CS Batch 2020',
            'api_id' => 456,
            'is_active' => true
        ]);

        $this->assertEquals(2020, $batch->batch_number);
        $this->assertEquals('CS Batch 2020', $batch->batch_name);
        $this->assertEquals(456, $batch->api_id);
        $this->assertTrue($batch->is_active);
    }

    public function test_batch_scopes()
    {
        Batch::factory()->count(3)->create(['is_active' => true]);
        Batch::factory()->count(2)->create(['is_active' => false]);

        $this->assertEquals(3, Batch::active()->count());
        $this->assertEquals(2, Batch::inactive()->count());
    }

    public function test_batch_display_name_accessor()
    {
        $batchWithName = Batch::factory()->create([
            'batch_number' => 2020,
            'batch_name' => 'CS Batch 2020'
        ]);

        $batchWithoutName = Batch::factory()->create([
            'batch_number' => 2021,
            'batch_name' => null
        ]);

        $this->assertEquals('CS Batch 2020', $batchWithName->display_name);
        $this->assertEquals('Batch 2021', $batchWithoutName->display_name);
    }

    public function test_batch_static_methods()
    {
        Batch::factory()->count(4)->create(['is_active' => true]);
        Batch::factory()->count(1)->create(['is_active' => false]);

        $stats = Batch::getStats();
        
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(4, $stats['active']);
        $this->assertEquals(1, $stats['inactive']);
    }

    // ========== GROUP MODEL TESTS ==========

    public function test_group_model_attributes()
    {
        $advisor = User::factory()->create();
        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create();

        $group = Group::factory()->create([
            'name' => 'Group Alpha',
            'advisor_id' => $advisor->id,
            'area_of_interest_id' => $area->id,
            'supervisor_id' => $supervisor->id,
            'max_students' => 4,
            'batch' => 2020
        ]);

        $this->assertEquals('Group Alpha', $group->name);
        $this->assertEquals($advisor->id, $group->advisor_id);
        $this->assertEquals($area->id, $group->area_of_interest_id);
        $this->assertEquals($supervisor->id, $group->supervisor_id);
        $this->assertEquals(4, $group->max_students);
        $this->assertEquals(2020, $group->batch);
    }

    public function test_group_scopes()
    {
        $supervisor = Supervisor::factory()->create();
        
        Group::factory()->count(3)->create(['supervisor_id' => $supervisor->id]);
        Group::factory()->count(2)->create(['supervisor_id' => null]);

        $this->assertEquals(3, Group::assigned()->count());
        $this->assertEquals(2, Group::unassigned()->count());
    }

    public function test_group_student_count_accessor()
    {
        $group = Group::factory()->create();
        GroupStudent::factory()->count(3)->create(['group_id' => $group->id]);

        $this->assertEquals(3, $group->student_count);
    }

    public function test_group_available_slots_accessor()
    {
        $group = Group::factory()->create(['max_students' => 5]);
        GroupStudent::factory()->count(2)->create(['group_id' => $group->id]);

        $this->assertEquals(3, $group->available_slots);
    }

    public function test_group_is_full_method()
    {
        $fullGroup = Group::factory()->create(['max_students' => 3]);
        GroupStudent::factory()->count(3)->create(['group_id' => $fullGroup->id]);

        $notFullGroup = Group::factory()->create(['max_students' => 3]);
        GroupStudent::factory()->count(2)->create(['group_id' => $notFullGroup->id]);

        $this->assertTrue($fullGroup->isFull());
        $this->assertFalse($notFullGroup->isFull());
    }

    public function test_group_relationships()
    {
        $advisor = User::factory()->create();
        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create();
        $student = User::factory()->create();

        $group = Group::factory()->create([
            'advisor_id' => $advisor->id,
            'area_of_interest_id' => $area->id,
            'supervisor_id' => $supervisor->id
        ]);

        $groupStudent = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $student->id
        ]);

        $this->assertInstanceOf(User::class, $group->advisor);
        $this->assertTrue($group->advisor->is($advisor));

        $this->assertInstanceOf(AreaOfInterest::class, $group->areaOfInterest);
        $this->assertTrue($group->areaOfInterest->is($area));

        $this->assertInstanceOf(Supervisor::class, $group->supervisor);
        $this->assertTrue($group->supervisor->is($supervisor));

        $this->assertInstanceOf(Collection::class, $group->students);
        $this->assertTrue($group->students->contains($groupStudent));
    }

    // ========== GROUP STUDENT MODEL TESTS ==========

    public function test_group_student_model_attributes()
    {
        $group = Group::factory()->create();
        $student = User::factory()->create();

        $groupStudent = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'joined_at' => now()
        ]);

        $this->assertEquals($group->id, $groupStudent->group_id);
        $this->assertEquals($student->id, $groupStudent->student_id);
        $this->assertInstanceOf(Carbon::class, $groupStudent->joined_at);
    }

    public function test_group_student_relationships()
    {
        $group = Group::factory()->create();
        $student = User::factory()->create();

        $groupStudent = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $student->id
        ]);

        $this->assertInstanceOf(Group::class, $groupStudent->group);
        $this->assertTrue($groupStudent->group->is($group));

        $this->assertInstanceOf(User::class, $groupStudent->student);
        $this->assertTrue($groupStudent->student->is($student));
    }

    // ========== MEETING MODEL TESTS ==========

    public function test_meeting_model_attributes()
    {
        $supervisor = Supervisor::factory()->create();

        $meeting = Meeting::factory()->create([
            'title' => 'Weekly Progress Meeting',
            'description' => 'Discuss project progress',
            'supervisor_id' => $supervisor->id,
            'meeting_date' => '2024-01-15',
            'meeting_time' => '10:00:00',
            'duration' => 60,
            'location' => 'Room 101',
            'status' => 'scheduled'
        ]);

        $this->assertEquals('Weekly Progress Meeting', $meeting->title);
        $this->assertEquals('Discuss project progress', $meeting->description);
        $this->assertEquals($supervisor->id, $meeting->supervisor_id);
        $this->assertEquals('2024-01-15', $meeting->meeting_date->format('Y-m-d'));
        $this->assertEquals('10:00:00', $meeting->meeting_time);
        $this->assertEquals(60, $meeting->duration);
        $this->assertEquals('Room 101', $meeting->location);
        $this->assertEquals('scheduled', $meeting->status);
    }

    public function test_meeting_relationships()
    {
        $supervisor = Supervisor::factory()->create();
        $meeting = Meeting::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertInstanceOf(Supervisor::class, $meeting->supervisor);
        $this->assertTrue($meeting->supervisor->is($supervisor));
    }

    public function test_meeting_scopes()
    {
        Meeting::factory()->count(3)->create(['status' => 'scheduled']);
        Meeting::factory()->count(2)->create(['status' => 'completed']);
        Meeting::factory()->count(1)->create(['status' => 'cancelled']);

        $this->assertEquals(3, Meeting::scheduled()->count());
        $this->assertEquals(2, Meeting::completed()->count());
        $this->assertEquals(1, Meeting::cancelled()->count());
    }

    // ========== MEETING ATTENDANCE MODEL TESTS ==========

    public function test_meeting_attendance_model_attributes()
    {
        $meeting = Meeting::factory()->create();
        $student = User::factory()->create();

        $attendance = MeetingAttendance::factory()->create([
            'meeting_id' => $meeting->id,
            'student_id' => $student->id,
            'status' => 'present',
            'notes' => 'Active participation'
        ]);

        $this->assertEquals($meeting->id, $attendance->meeting_id);
        $this->assertEquals($student->id, $attendance->student_id);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('Active participation', $attendance->notes);
    }

    public function test_meeting_attendance_relationships()
    {
        $meeting = Meeting::factory()->create();
        $student = User::factory()->create();

        $attendance = MeetingAttendance::factory()->create([
            'meeting_id' => $meeting->id,
            'student_id' => $student->id
        ]);

        $this->assertInstanceOf(Meeting::class, $attendance->meeting);
        $this->assertTrue($attendance->meeting->is($meeting));

        $this->assertInstanceOf(User::class, $attendance->student);
        $this->assertTrue($attendance->student->is($student));
    }

    // ========== ASSIGNMENT HISTORY MODEL TESTS ==========

    public function test_assignment_history_model_attributes()
    {
        $group = Group::factory()->create();
        $supervisor = Supervisor::factory()->create();
        $user = User::factory()->create();

        $history = AssignmentHistory::factory()->create([
            'group_id' => $group->id,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $user->id,
            'action' => 'assigned',
            'notes' => 'Manual assignment'
        ]);

        $this->assertEquals($group->id, $history->group_id);
        $this->assertEquals($supervisor->id, $history->supervisor_id);
        $this->assertEquals($user->id, $history->assigned_by);
        $this->assertEquals('assigned', $history->action);
        $this->assertEquals('Manual assignment', $history->notes);
    }

    public function test_assignment_history_relationships()
    {
        $group = Group::factory()->create();
        $supervisor = Supervisor::factory()->create();
        $user = User::factory()->create();

        $history = AssignmentHistory::factory()->create([
            'group_id' => $group->id,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $user->id
        ]);

        $this->assertInstanceOf(Group::class, $history->group);
        $this->assertTrue($history->group->is($group));

        $this->assertInstanceOf(Supervisor::class, $history->supervisor);
        $this->assertTrue($history->supervisor->is($supervisor));

        $this->assertInstanceOf(User::class, $history->assignedBy);
        $this->assertTrue($history->assignedBy->is($user));
    }

    // ========== ADMIN CREATED GROUP MODEL TESTS ==========

    public function test_admin_created_group_model_attributes()
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();

        $adminGroup = AdminCreatedGroup::factory()->create([
            'group_id' => $group->id,
            'created_by' => $admin->id,
            'notes' => 'Created for special project'
        ]);

        $this->assertEquals($group->id, $adminGroup->group_id);
        $this->assertEquals($admin->id, $adminGroup->created_by);
        $this->assertEquals('Created for special project', $adminGroup->notes);
    }

    public function test_admin_created_group_relationships()
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();

        $adminGroup = AdminCreatedGroup::factory()->create([
            'group_id' => $group->id,
            'created_by' => $admin->id
        ]);

        $this->assertInstanceOf(Group::class, $adminGroup->group);
        $this->assertTrue($adminGroup->group->is($group));

        $this->assertInstanceOf(User::class, $adminGroup->createdBy);
        $this->assertTrue($adminGroup->createdBy->is($admin));
    }

    // ========== MODEL VALIDATION TESTS ==========

    public function test_model_required_fields()
    {
        // Test User required fields
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::create([]);
    }

    public function test_model_unique_constraints()
    {
        User::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    // ========== MODEL CASTING TESTS ==========

    public function test_model_date_casting()
    {
        $meeting = Meeting::factory()->create([
            'meeting_date' => '2024-01-15'
        ]);

        $this->assertInstanceOf(Carbon::class, $meeting->meeting_date);
    }

    public function test_model_boolean_casting()
    {
        $area = AreaOfInterest::factory()->create(['is_active' => 1]);
        $supervisor = Supervisor::factory()->create(['is_active' => 0]);
        $batch = Batch::factory()->create(['is_active' => 1]);

        $this->assertIsBool($area->is_active);
        $this->assertTrue($area->is_active);

        $this->assertIsBool($supervisor->is_active);
        $this->assertFalse($supervisor->is_active);

        $this->assertIsBool($batch->is_active);
        $this->assertTrue($batch->is_active);
    }

    public function test_model_json_casting()
    {
        $user = User::factory()->create(['type_id' => json_encode(['1', '2'])]);

        $this->assertIsArray($user->type_ids);
        $this->assertEquals(['1', '2'], $user->type_ids);
    }

    // ========== MODEL FACTORY TESTS ==========

    public function test_all_model_factories_work()
    {
        $models = [
            User::class,
            AreaOfInterest::class,
            Supervisor::class,
            Batch::class,
            Group::class,
            GroupStudent::class,
            Meeting::class,
            MeetingAttendance::class,
            AssignmentHistory::class,
            AdminCreatedGroup::class
        ];

        foreach ($models as $model) {
            $instance = $model::factory()->create();
            $this->assertInstanceOf($model, $instance);
            $this->assertDatabaseHas($instance->getTable(), ['id' => $instance->id]);
        }
    }

    // ========== MODEL SOFT DELETE TESTS ==========

    public function test_soft_delete_functionality()
    {
        // Test if models that should have soft deletes actually do
        $group = Group::factory()->create();
        $group->delete();

        $this->assertSoftDeleted($group);
        $this->assertNotNull($group->deleted_at);
    }
}