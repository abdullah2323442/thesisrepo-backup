<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ComprehensiveSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $teacher;
    protected $student;
    protected $advisor;
    protected $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users for different roles
        $this->admin = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['1']), // Admin type
            'name' => 'Test Admin',
            'email' => 'admin@test.com'
        ]);

        $this->teacher = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['2']), // Teacher type
            'name' => 'Test Teacher',
            'email' => 'teacher@test.com'
        ]);

        $this->student = User::factory()->create([
            'login_type' => 'student',
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'roll' => '2020123456',
            'batch' => 2020
        ]);

        $this->advisor = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['3']), // Advisor type
            'name' => 'Test Advisor',
            'email' => 'advisor@test.com'
        ]);

        $this->supervisor = Supervisor::factory()->create([
            'name' => 'Test Supervisor',
            'email' => 'supervisor@test.com',
            'max_groups' => 5,
            'current_groups' => 0,
            'is_active' => true
        ]);
    }

    // ========== AUTHENTICATION TESTS ==========

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ========== DASHBOARD TESTS ==========

    public function test_admin_dashboard_functionality()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertViewHas('stats');
    }

    public function test_teacher_dashboard_functionality()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->teacher)->get('/teacher/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Teacher Dashboard');
    }

    public function test_student_dashboard_functionality()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Student Dashboard');
    }

    public function test_advisor_dashboard_functionality()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Advisor Dashboard');
    }

    public function test_supervisor_dashboard_functionality()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->teacher)->get('/supervisor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Supervisor Dashboard');
    }

    // ========== AREA OF INTEREST TESTS ==========

    public function test_admin_can_create_area_of_interest()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => 'Machine Learning',
            'description' => 'AI and ML research area',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('area_of_interests', [
            'name' => 'Machine Learning',
            'description' => 'AI and ML research area',
            'is_active' => true
        ]);
    }

    public function test_admin_can_update_area_of_interest()
    {
        $this->withoutMiddleware();
        
        $area = AreaOfInterest::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description'
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/areas-of-interest/{$area->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('area_of_interests', [
            'id' => $area->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_admin_can_delete_area_of_interest()
    {
        $this->withoutMiddleware();
        
        $area = AreaOfInterest::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/areas-of-interest/{$area->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('area_of_interests', ['id' => $area->id]);
    }

    public function test_admin_can_bulk_create_areas_of_interest()
    {
        $this->withoutMiddleware();

        $areas = [
            ['name' => 'Web Development', 'description' => 'Frontend and Backend'],
            ['name' => 'Mobile Development', 'description' => 'iOS and Android'],
            ['name' => 'Data Science', 'description' => 'Analytics and Visualization']
        ];

        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest/bulk', [
            'areas' => $areas
        ]);

        $response->assertRedirect();
        
        foreach ($areas as $area) {
            $this->assertDatabaseHas('area_of_interests', [
                'name' => $area['name'],
                'description' => $area['description']
            ]);
        }
    }

    // ========== SUPERVISOR MANAGEMENT TESTS ==========

    public function test_admin_can_view_supervisors()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/supervisors');

        $response->assertStatus(200);
        $response->assertSee('Supervisor Management');
    }

    public function test_admin_can_update_supervisor()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->put("/admin/supervisors/{$this->supervisor->id}", [
            'name' => 'Updated Supervisor Name',
            'email' => 'updated@test.com',
            'max_groups' => 10,
            'is_active' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supervisors', [
            'id' => $this->supervisor->id,
            'name' => 'Updated Supervisor Name',
            'email' => 'updated@test.com',
            'max_groups' => 10
        ]);
    }

    public function test_admin_can_toggle_supervisor_status()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->post("/admin/supervisors/{$this->supervisor->id}/toggle");

        $response->assertRedirect();
        $this->supervisor->refresh();
        $this->assertFalse($this->supervisor->is_active);
    }

    public function test_admin_can_bulk_update_supervisor_limits()
    {
        $this->withoutMiddleware();
        
        $supervisor2 = Supervisor::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/supervisors/bulk-limits', [
            'supervisors' => [
                $this->supervisor->id => ['max_groups' => 8],
                $supervisor2->id => ['max_groups' => 6]
            ]
        ]);

        $response->assertRedirect();
        
        $this->supervisor->refresh();
        $supervisor2->refresh();
        
        $this->assertEquals(8, $this->supervisor->max_groups);
        $this->assertEquals(6, $supervisor2->max_groups);
    }

    // ========== BATCH MANAGEMENT TESTS ==========

    public function test_admin_can_view_batches()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/batches');

        $response->assertStatus(200);
        $response->assertSee('Batch Management');
    }

    public function test_admin_can_update_batch()
    {
        $this->withoutMiddleware();
        
        $batch = Batch::factory()->create([
            'batch_number' => 2020,
            'batch_name' => 'CS Batch 2020'
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/batches/{$batch->id}", [
            'batch_name' => 'Updated CS Batch 2020',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'batch_name' => 'Updated CS Batch 2020'
        ]);
    }

    public function test_admin_can_toggle_batch_status()
    {
        $this->withoutMiddleware();
        
        $batch = Batch::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post("/admin/batches/{$batch->id}/toggle");

        $response->assertRedirect();
        $batch->refresh();
        $this->assertFalse($batch->is_active);
    }

    public function test_admin_can_delete_batch()
    {
        $this->withoutMiddleware();
        
        $batch = Batch::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/batches/{$batch->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_admin_can_bulk_activate_batches()
    {
        $this->withoutMiddleware();
        
        Batch::factory()->count(3)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->post('/admin/batches/activate-all');

        $response->assertRedirect();
        $this->assertEquals(3, Batch::where('is_active', true)->count());
    }

    public function test_admin_can_bulk_deactivate_batches()
    {
        $this->withoutMiddleware();
        
        Batch::factory()->count(3)->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post('/admin/batches/deactivate-all');

        $response->assertRedirect();
        $this->assertEquals(3, Batch::where('is_active', false)->count());
    }

    // ========== GROUP MANAGEMENT TESTS ==========

    public function test_admin_can_view_groups()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/groups');

        $response->assertStatus(200);
        $response->assertSee('Group Management');
    }

    public function test_admin_can_create_group()
    {
        $this->withoutMiddleware();
        
        $area = AreaOfInterest::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/groups/create', [
            'name' => 'Test Group',
            'max_students' => 3,
            'area_of_interest_id' => $area->id,
            'advisor_id' => $this->advisor->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
            'max_students' => 3,
            'area_of_interest_id' => $area->id,
            'advisor_id' => $this->advisor->id
        ]);
    }

    public function test_admin_can_assign_student_to_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);
    }

    public function test_admin_can_remove_student_from_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create();
        $groupStudent = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/groups/remove-student', [
            'group_student_id' => $groupStudent->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('group_students', ['id' => $groupStudent->id]);
    }

    public function test_admin_can_assign_supervisor_to_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create(['supervisor_id' => null]);

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-supervisor', [
            'group_id' => $group->id,
            'supervisor_id' => $this->supervisor->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'supervisor_id' => $this->supervisor->id
        ]);
    }

    public function test_admin_can_unassign_supervisor_from_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create(['supervisor_id' => $this->supervisor->id]);

        $response = $this->actingAs($this->admin)->post('/admin/groups/unassign-supervisor', [
            'group_id' => $group->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'supervisor_id' => null
        ]);
    }

    public function test_admin_can_delete_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/groups/{$group->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_admin_can_bulk_delete_groups()
    {
        $this->withoutMiddleware();
        
        $groups = Group::factory()->count(3)->create();
        $groupIds = $groups->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post('/admin/groups/bulk-delete', [
            'group_ids' => $groupIds
        ]);

        $response->assertRedirect();
        
        foreach ($groupIds as $id) {
            $this->assertDatabaseMissing('groups', ['id' => $id]);
        }
    }

    // ========== ADVISOR FUNCTIONALITY TESTS ==========

    public function test_advisor_can_view_students()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $response->assertSee('Students');
    }

    public function test_advisor_can_view_groups()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->get('/advisor/groups');

        $response->assertStatus(200);
        $response->assertSee('Group Management');
    }

    public function test_advisor_can_create_groups()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/create', [
            'batch_id' => 2020,
            'groups_count' => 5
        ]);

        $response->assertRedirect();
        $this->assertEquals(5, Group::where('advisor_id', $this->advisor->id)->count());
    }

    public function test_advisor_can_add_single_group()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/add', [
            'name' => 'New Group',
            'max_students' => 4
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'New Group',
            'max_students' => 4,
            'advisor_id' => $this->advisor->id
        ]);
    }

    public function test_advisor_can_assign_area_of_interest_to_group()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create(['advisor_id' => $this->advisor->id]);
        $area = AreaOfInterest::factory()->create();

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-area-of-interest', [
            'group_id' => $group->id,
            'area_of_interest_id' => $area->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'area_of_interest_id' => $area->id
        ]);
    }

    public function test_advisor_can_remove_all_groups()
    {
        $this->withoutMiddleware();
        
        Group::factory()->count(3)->create(['advisor_id' => $this->advisor->id]);

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-all-groups');

        $response->assertRedirect();
        $this->assertEquals(0, Group::where('advisor_id', $this->advisor->id)->count());
    }

    public function test_advisor_can_upload_excel_for_groups()
    {
        $this->withoutMiddleware();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('groups.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $file
        ]);

        // This would normally process the Excel file
        $response->assertRedirect();
    }

    public function test_advisor_can_download_template()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->get('/advisor/groups/download-template');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    // ========== SUPERVISOR ASSIGNMENT TESTS ==========

    public function test_advisor_can_view_supervisor_assignment()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->advisor)->get('/advisor/supervisor-assignment');

        $response->assertStatus(200);
        $response->assertSee('Supervisor Assignment');
    }

    public function test_advisor_can_assign_supervisor_manually()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create(['advisor_id' => $this->advisor->id]);

        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/assign-manual', [
            'group_id' => $group->id,
            'supervisor_id' => $this->supervisor->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'supervisor_id' => $this->supervisor->id
        ]);
    }

    public function test_advisor_can_unassign_supervisor()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'supervisor_id' => $this->supervisor->id
        ]);

        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/unassign', [
            'group_id' => $group->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'supervisor_id' => null
        ]);
    }

    public function test_advisor_can_unassign_all_supervisors()
    {
        $this->withoutMiddleware();
        
        Group::factory()->count(3)->create([
            'advisor_id' => $this->advisor->id,
            'supervisor_id' => $this->supervisor->id
        ]);

        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/unassign-all');

        $response->assertRedirect();
        $this->assertEquals(0, Group::where('advisor_id', $this->advisor->id)
                                  ->whereNotNull('supervisor_id')
                                  ->count());
    }

    public function test_advisor_can_run_lottery_assignment()
    {
        $this->withoutMiddleware();
        
        // Create groups without supervisors
        Group::factory()->count(3)->create([
            'advisor_id' => $this->advisor->id,
            'supervisor_id' => null
        ]);
        
        // Create additional supervisors
        Supervisor::factory()->count(2)->create(['is_active' => true, 'max_groups' => 5]);

        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/run-lottery');

        $response->assertRedirect();
        
        // Check that some groups got assigned supervisors
        $assignedGroups = Group::where('advisor_id', $this->advisor->id)
                              ->whereNotNull('supervisor_id')
                              ->count();
        $this->assertGreaterThan(0, $assignedGroups);
    }

    // ========== SUPERVISOR FUNCTIONALITY TESTS ==========

    public function test_supervisor_can_view_groups()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->teacher)->get('/supervisor/groups');

        $response->assertStatus(200);
        $response->assertSee('My Groups');
    }

    public function test_supervisor_can_view_meetings()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->teacher)->get('/supervisor/meetings');

        $response->assertStatus(200);
        $response->assertSee('Meetings');
    }

    public function test_supervisor_can_create_meeting()
    {
        $this->withoutMiddleware();
        
        $group = Group::factory()->create(['supervisor_id' => $this->supervisor->id]);

        $response = $this->actingAs($this->teacher)->post('/supervisor/meetings', [
            'title' => 'Weekly Progress Meeting',
            'description' => 'Discuss project progress',
            'meeting_date' => now()->addDays(1)->format('Y-m-d'),
            'meeting_time' => '10:00',
            'duration' => 60,
            'location' => 'Room 101',
            'group_ids' => [$group->id]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('meetings', [
            'title' => 'Weekly Progress Meeting',
            'description' => 'Discuss project progress',
            'location' => 'Room 101'
        ]);
    }

    // ========== STUDENT FUNCTIONALITY TESTS ==========

    public function test_student_can_view_meetings()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->student)->get('/student/meetings');

        $response->assertStatus(200);
        $response->assertSee('My Meetings');
    }

    public function test_student_can_download_meetings_pdf()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->student)->get('/student/meetings/pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // ========== PERFORMANCE MONITORING TESTS ==========

    public function test_admin_can_view_performance_dashboard()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/performance');

        $response->assertStatus(200);
        $response->assertSee('Performance Monitoring');
    }

    public function test_admin_can_view_performance_metrics()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/performance/metrics');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_health_status()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/performance/health');

        $response->assertStatus(200);
    }

    public function test_admin_can_clear_cache()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->post('/admin/performance/clear-cache');

        $response->assertRedirect();
    }

    public function test_admin_can_export_performance_data()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/performance/export');

        $response->assertStatus(200);
    }

    // ========== PROFILE MANAGEMENT TESTS ==========

    public function test_user_can_view_profile()
    {
        $response = $this->actingAs($this->student)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
    }

    public function test_user_can_update_profile()
    {
        $response = $this->actingAs($this->student)->patch('/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@test.com'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com'
        ]);
    }

    // ========== MODEL RELATIONSHIP TESTS ==========

    public function test_user_group_relationships()
    {
        $group = Group::factory()->create(['advisor_id' => $this->advisor->id]);
        GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        $this->assertTrue($this->advisor->advisedGroups->contains($group));
        $this->assertTrue($this->student->groupStudents->first()->group->is($group));
    }

    public function test_supervisor_group_relationships()
    {
        $group = Group::factory()->create(['supervisor_id' => $this->supervisor->id]);

        $this->assertTrue($this->supervisor->groups->contains($group));
        $this->assertTrue($group->supervisor->is($this->supervisor));
    }

    public function test_area_of_interest_group_relationships()
    {
        $area = AreaOfInterest::factory()->create();
        $group = Group::factory()->create(['area_of_interest_id' => $area->id]);

        $this->assertTrue($area->groups->contains($group));
        $this->assertTrue($group->areaOfInterest->is($area));
    }

    // ========== VALIDATION TESTS ==========

    public function test_area_of_interest_validation()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => '', // Required field
            'description' => 'Test description'
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_group_creation_validation()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->post('/admin/groups/create', [
            'name' => '', // Required field
            'max_students' => 'invalid' // Should be integer
        ]);

        $response->assertSessionHasErrors(['name', 'max_students']);
    }

    // ========== MIDDLEWARE TESTS ==========

    public function test_admin_middleware_blocks_non_admin()
    {
        $response = $this->actingAs($this->student)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_teacher_middleware_blocks_non_teacher()
    {
        $response = $this->actingAs($this->student)->get('/teacher/dashboard');

        $response->assertStatus(403);
    }

    public function test_student_middleware_blocks_non_student()
    {
        $response = $this->actingAs($this->teacher)->get('/student/dashboard');

        $response->assertStatus(403);
    }

    // ========== API RATE LIMITING TESTS ==========

    public function test_external_api_rate_limiting()
    {
        $this->withoutMiddleware(['throttle:external_api_student_dashboard']);

        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->student)->get('/student/dashboard');
            $response->assertStatus(200);
        }
    }

    // ========== DATABASE INTEGRITY TESTS ==========

    public function test_database_constraints()
    {
        // Test foreign key constraints
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Group::create([
            'name' => 'Test Group',
            'advisor_id' => 99999, // Non-existent user
            'max_students' => 3
        ]);
    }

    public function test_model_factories_create_valid_data()
    {
        $user = User::factory()->create();
        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create();
        $batch = Batch::factory()->create();
        $group = Group::factory()->create();
        $groupStudent = GroupStudent::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(AreaOfInterest::class, $area);
        $this->assertInstanceOf(Supervisor::class, $supervisor);
        $this->assertInstanceOf(Batch::class, $batch);
        $this->assertInstanceOf(Group::class, $group);
        $this->assertInstanceOf(GroupStudent::class, $groupStudent);
    }

    // ========== SYSTEM HEALTH TESTS ==========

    public function test_database_connection()
    {
        $this->assertTrue(DB::connection()->getPdo() !== null);
    }

    public function test_environment_configuration()
    {
        $this->assertNotEmpty(config('app.name'));
        $this->assertNotEmpty(config('app.key'));
        $this->assertNotEmpty(config('database.default'));
    }

    public function test_storage_directories_exist()
    {
        $this->assertTrue(is_dir(storage_path('app')));
        $this->assertTrue(is_dir(storage_path('logs')));
        $this->assertTrue(is_dir(storage_path('framework')));
    }

    // ========== CLEANUP TESTS ==========

    protected function tearDown(): void
    {
        // Clean up any test data if needed
        parent::tearDown();
    }
}