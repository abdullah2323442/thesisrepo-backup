<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Batch;
use App\Models\Group;
use App\Models\GroupStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ComprehensiveApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $advisor;
    protected $teacher;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['1'])
        ]);

        $this->advisor = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['3'])
        ]);

        $this->teacher = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['2'])
        ]);

        $this->student = User::factory()->create([
            'login_type' => 'student'
        ]);
    }

    // ========== ADMIN API ENDPOINTS ==========

    public function test_admin_areas_of_interest_crud()
    {
        $this->withoutMiddleware();

        // Test Create
        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => 'Artificial Intelligence',
            'description' => 'AI research and development',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $area = AreaOfInterest::where('name', 'Artificial Intelligence')->first();
        $this->assertNotNull($area);

        // Test Read
        $response = $this->actingAs($this->admin)->get('/admin/areas-of-interest');
        $response->assertStatus(200);
        $response->assertSee('Artificial Intelligence');

        // Test Update
        $response = $this->actingAs($this->admin)->put("/admin/areas-of-interest/{$area->id}", [
            'name' => 'Machine Learning & AI',
            'description' => 'Updated description',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $area->refresh();
        $this->assertEquals('Machine Learning & AI', $area->name);

        // Test Delete
        $response = $this->actingAs($this->admin)->delete("/admin/areas-of-interest/{$area->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('area_of_interests', ['id' => $area->id]);
    }

    public function test_admin_bulk_areas_creation()
    {
        $this->withoutMiddleware();

        $areas = [
            ['name' => 'Web Development', 'description' => 'Frontend and Backend'],
            ['name' => 'Mobile Development', 'description' => 'iOS and Android'],
            ['name' => 'Data Science', 'description' => 'Analytics and ML']
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

    public function test_admin_supervisor_management()
    {
        $this->withoutMiddleware();

        $supervisor = Supervisor::factory()->create();

        // Test supervisor list
        $response = $this->actingAs($this->admin)->get('/admin/supervisors');
        $response->assertStatus(200);

        // Test supervisor update
        $response = $this->actingAs($this->admin)->put("/admin/supervisors/{$supervisor->id}", [
            'name' => 'Updated Supervisor',
            'email' => 'updated@test.com',
            'max_groups' => 8,
            'is_active' => true
        ]);

        $response->assertRedirect();
        $supervisor->refresh();
        $this->assertEquals('Updated Supervisor', $supervisor->name);
        $this->assertEquals(8, $supervisor->max_groups);

        // Test supervisor toggle
        $response = $this->actingAs($this->admin)->post("/admin/supervisors/{$supervisor->id}/toggle");
        $response->assertRedirect();
        $supervisor->refresh();
        $this->assertFalse($supervisor->is_active);
    }

    public function test_admin_bulk_supervisor_limits_update()
    {
        $this->withoutMiddleware();

        $supervisors = Supervisor::factory()->count(3)->create();

        $updateData = [];
        foreach ($supervisors as $supervisor) {
            $updateData[$supervisor->id] = ['max_groups' => 10];
        }

        $response = $this->actingAs($this->admin)->post('/admin/supervisors/bulk-limits', [
            'supervisors' => $updateData
        ]);

        $response->assertRedirect();

        foreach ($supervisors as $supervisor) {
            $supervisor->refresh();
            $this->assertEquals(10, $supervisor->max_groups);
        }
    }

    public function test_admin_batch_management()
    {
        $this->withoutMiddleware();

        $batch = Batch::factory()->create();

        // Test batch list
        $response = $this->actingAs($this->admin)->get('/admin/batches');
        $response->assertStatus(200);

        // Test batch update
        $response = $this->actingAs($this->admin)->put("/admin/batches/{$batch->id}", [
            'batch_name' => 'Updated Batch Name',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $batch->refresh();
        $this->assertEquals('Updated Batch Name', $batch->batch_name);

        // Test batch toggle
        $response = $this->actingAs($this->admin)->post("/admin/batches/{$batch->id}/toggle");
        $response->assertRedirect();
        $batch->refresh();
        $this->assertFalse($batch->is_active);

        // Test batch delete
        $response = $this->actingAs($this->admin)->delete("/admin/batches/{$batch->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_admin_batch_bulk_operations()
    {
        $this->withoutMiddleware();

        Batch::factory()->count(3)->create(['is_active' => false]);

        // Test bulk activate
        $response = $this->actingAs($this->admin)->post('/admin/batches/activate-all');
        $response->assertRedirect();
        $this->assertEquals(3, Batch::where('is_active', true)->count());

        // Test bulk deactivate
        $response = $this->actingAs($this->admin)->post('/admin/batches/deactivate-all');
        $response->assertRedirect();
        $this->assertEquals(3, Batch::where('is_active', false)->count());
    }

    public function test_admin_group_management()
    {
        $this->withoutMiddleware();

        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create();

        // Test group creation
        $response = $this->actingAs($this->admin)->post('/admin/groups/create', [
            'name' => 'Test Group',
            'max_students' => 4,
            'area_of_interest_id' => $area->id,
            'advisor_id' => $this->advisor->id
        ]);

        $response->assertRedirect();
        $group = Group::where('name', 'Test Group')->first();
        $this->assertNotNull($group);

        // Test student assignment
        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        // Test supervisor assignment
        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-supervisor', [
            'group_id' => $group->id,
            'supervisor_id' => $supervisor->id
        ]);

        $response->assertRedirect();
        $group->refresh();
        $this->assertEquals($supervisor->id, $group->supervisor_id);

        // Test supervisor unassignment
        $response = $this->actingAs($this->admin)->post('/admin/groups/unassign-supervisor', [
            'group_id' => $group->id
        ]);

        $response->assertRedirect();
        $group->refresh();
        $this->assertNull($group->supervisor_id);

        // Test group deletion
        $response = $this->actingAs($this->admin)->delete("/admin/groups/{$group->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_admin_performance_monitoring()
    {
        $this->withoutMiddleware();

        // Test performance dashboard
        $response = $this->actingAs($this->admin)->get('/admin/performance');
        $response->assertStatus(200);

        // Test metrics endpoint
        $response = $this->actingAs($this->admin)->get('/admin/performance/metrics');
        $response->assertStatus(200);

        // Test health endpoint
        $response = $this->actingAs($this->admin)->get('/admin/performance/health');
        $response->assertStatus(200);

        // Test database performance
        $response = $this->actingAs($this->admin)->get('/admin/performance/database');
        $response->assertStatus(200);

        // Test API performance
        $response = $this->actingAs($this->admin)->get('/admin/performance/api');
        $response->assertStatus(200);

        // Test security monitoring
        $response = $this->actingAs($this->admin)->get('/admin/performance/security');
        $response->assertStatus(200);

        // Test cache clearing
        $response = $this->actingAs($this->admin)->post('/admin/performance/clear-cache');
        $response->assertRedirect();

        // Test performance export
        $response = $this->actingAs($this->admin)->get('/admin/performance/export');
        $response->assertStatus(200);
    }

    // ========== ADVISOR API ENDPOINTS ==========

    public function test_advisor_student_management()
    {
        $this->withoutMiddleware();

        // Test students list
        $response = $this->actingAs($this->advisor)->get('/advisor/students');
        $response->assertStatus(200);

        // Test student details
        $response = $this->actingAs($this->advisor)->get("/advisor/students/{$this->student->id}");
        $response->assertStatus(200);

        // Test refresh student data
        $response = $this->actingAs($this->advisor)->post('/advisor/students/refresh');
        $response->assertRedirect();
    }

    public function test_advisor_group_management()
    {
        $this->withoutMiddleware();

        // Test groups list
        $response = $this->actingAs($this->advisor)->get('/advisor/groups');
        $response->assertStatus(200);

        // Test create multiple groups
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/create', [
            'batch_id' => 2020,
            'groups_count' => 5
        ]);

        $response->assertRedirect();
        $this->assertEquals(5, Group::where('advisor_id', $this->advisor->id)->count());

        // Test add single group
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/add', [
            'name' => 'Special Group',
            'max_students' => 3
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'Special Group',
            'advisor_id' => $this->advisor->id
        ]);

        $group = Group::where('name', 'Special Group')->first();

        // Test assign area of interest
        $area = AreaOfInterest::factory()->create();
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-area-of-interest', [
            'group_id' => $group->id,
            'area_of_interest_id' => $area->id
        ]);

        $response->assertRedirect();
        $group->refresh();
        $this->assertEquals($area->id, $group->area_of_interest_id);

        // Test assign student to group
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => $this->student->id
        ]);

        // Test remove student from group
        $groupStudent = GroupStudent::where('group_id', $group->id)->first();
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-student', [
            'group_student_id' => $groupStudent->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('group_students', ['id' => $groupStudent->id]);

        // Test remove all groups
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-all-groups');
        $response->assertRedirect();
        $this->assertEquals(0, Group::where('advisor_id', $this->advisor->id)->count());
    }

    public function test_advisor_excel_operations()
    {
        $this->withoutMiddleware();
        Storage::fake('local');

        // Test Excel upload
        $file = UploadedFile::fake()->create('groups.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $file
        ]);

        $response->assertRedirect();

        // Test template download
        $response = $this->actingAs($this->advisor)->get('/advisor/groups/download-template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_advisor_supervisor_assignment()
    {
        $this->withoutMiddleware();

        $group = Group::factory()->create(['advisor_id' => $this->advisor->id]);
        $supervisor = Supervisor::factory()->create(['is_active' => true]);

        // Test supervisor assignment page
        $response = $this->actingAs($this->advisor)->get('/advisor/supervisor-assignment');
        $response->assertStatus(200);

        // Test manual supervisor assignment
        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/assign-manual', [
            'group_id' => $group->id,
            'supervisor_id' => $supervisor->id
        ]);

        $response->assertRedirect();
        $group->refresh();
        $this->assertEquals($supervisor->id, $group->supervisor_id);

        // Test supervisor unassignment
        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/unassign', [
            'group_id' => $group->id
        ]);

        $response->assertRedirect();
        $group->refresh();
        $this->assertNull($group->supervisor_id);

        // Test get available supervisors
        $response = $this->actingAs($this->advisor)->get('/advisor/supervisor-assignment/available-supervisors');
        $response->assertStatus(200);

        // Test lottery preview
        $response = $this->actingAs($this->advisor)->get('/advisor/supervisor-assignment/preview-lottery');
        $response->assertStatus(200);

        // Test run lottery
        Group::factory()->count(3)->create(['advisor_id' => $this->advisor->id, 'supervisor_id' => null]);
        Supervisor::factory()->count(2)->create(['is_active' => true, 'max_groups' => 5]);

        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/run-lottery');
        $response->assertRedirect();

        // Test unassign all supervisors
        $response = $this->actingAs($this->advisor)->post('/advisor/supervisor-assignment/unassign-all');
        $response->assertRedirect();
    }

    // ========== SUPERVISOR API ENDPOINTS ==========

    public function test_supervisor_dashboard_and_groups()
    {
        $this->withoutMiddleware();

        // Test supervisor dashboard
        $response = $this->actingAs($this->teacher)->get('/supervisor/dashboard');
        $response->assertStatus(200);

        // Test supervisor groups
        $response = $this->actingAs($this->teacher)->get('/supervisor/groups');
        $response->assertStatus(200);
    }

    public function test_supervisor_meeting_management()
    {
        $this->withoutMiddleware();

        $supervisor = Supervisor::factory()->create();
        $group = Group::factory()->create(['supervisor_id' => $supervisor->id]);

        // Test meetings list
        $response = $this->actingAs($this->teacher)->get('/supervisor/meetings');
        $response->assertStatus(200);

        // Test create meeting
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
            'location' => 'Room 101'
        ]);

        $meeting = \App\Models\Meeting::where('title', 'Weekly Progress Meeting')->first();

        // Test view meeting
        $response = $this->actingAs($this->teacher)->get("/supervisor/meetings/{$meeting->id}");
        $response->assertStatus(200);

        // Test edit meeting
        $response = $this->actingAs($this->teacher)->get("/supervisor/meetings/{$meeting->id}/edit");
        $response->assertStatus(200);

        // Test update meeting
        $response = $this->actingAs($this->teacher)->put("/supervisor/meetings/{$meeting->id}", [
            'title' => 'Updated Meeting Title',
            'description' => 'Updated description',
            'meeting_date' => now()->addDays(2)->format('Y-m-d'),
            'meeting_time' => '11:00',
            'duration' => 90,
            'location' => 'Room 102'
        ]);

        $response->assertRedirect();
        $meeting->refresh();
        $this->assertEquals('Updated Meeting Title', $meeting->title);

        // Test get students for meetings
        $response = $this->actingAs($this->teacher)->get('/supervisor/meetings/students');
        $response->assertStatus(200);
    }

    // ========== STUDENT API ENDPOINTS ==========

    public function test_student_dashboard_and_meetings()
    {
        $this->withoutMiddleware();

        // Test student dashboard
        $response = $this->actingAs($this->student)->get('/student/dashboard');
        $response->assertStatus(200);

        // Test student meetings
        $response = $this->actingAs($this->student)->get('/student/meetings');
        $response->assertStatus(200);

        // Test meetings PDF download
        $response = $this->actingAs($this->student)->get('/student/meetings/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // ========== PROFILE API ENDPOINTS ==========

    public function test_profile_management()
    {
        // Test view profile
        $response = $this->actingAs($this->student)->get('/profile');
        $response->assertStatus(200);

        // Test update profile
        $response = $this->actingAs($this->student)->patch('/profile', [
            'name' => 'Updated Student Name',
            'email' => 'updated.student@test.com'
        ]);

        $response->assertRedirect();
        $this->student->refresh();
        $this->assertEquals('Updated Student Name', $this->student->name);
        $this->assertEquals('updated.student@test.com', $this->student->email);

        // Test delete profile (should be restricted)
        $response = $this->actingAs($this->student)->delete('/profile');
        $response->assertRedirect();
    }

    // ========== AJAX API ENDPOINTS ==========

    public function test_ajax_available_supervisors()
    {
        $this->withoutMiddleware();

        Supervisor::factory()->count(3)->create(['is_active' => true, 'max_groups' => 5]);

        $response = $this->actingAs($this->admin)->get('/admin/groups/available-supervisors');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'email', 'available_slots']
        ]);
    }

    // ========== ERROR HANDLING TESTS ==========

    public function test_unauthorized_access_returns_403()
    {
        // Student trying to access admin routes
        $response = $this->actingAs($this->student)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Teacher trying to access student routes
        $response = $this->actingAs($this->teacher)->get('/student/dashboard');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_access_redirects_to_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/student/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_invalid_resource_returns_404()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/areas-of-interest/99999');
        $response->assertStatus(404);

        $response = $this->actingAs($this->admin)->get('/admin/supervisors/99999/edit');
        $response->assertStatus(404);
    }

    // ========== VALIDATION TESTS ==========

    public function test_form_validation_errors()
    {
        $this->withoutMiddleware();

        // Test area of interest validation
        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => '', // Required
            'description' => str_repeat('a', 1001) // Too long
        ]);

        $response->assertSessionHasErrors(['name', 'description']);

        // Test group creation validation
        $response = $this->actingAs($this->admin)->post('/admin/groups/create', [
            'name' => '',
            'max_students' => 'invalid',
            'advisor_id' => 99999 // Non-existent
        ]);

        $response->assertSessionHasErrors(['name', 'max_students', 'advisor_id']);
    }

    // ========== RATE LIMITING TESTS ==========

    public function test_api_rate_limiting()
    {
        // Test that rate limiting middleware is applied
        $this->withoutMiddleware(['throttle:external_api_student_dashboard']);

        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($this->student)->get('/student/dashboard');
            $response->assertStatus(200);
        }
    }

    // ========== FILE UPLOAD TESTS ==========

    public function test_excel_file_upload_validation()
    {
        $this->withoutMiddleware();
        Storage::fake('local');

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $invalidFile
        ]);

        $response->assertSessionHasErrors(['excel_file']);

        // Test valid file
        $validFile = UploadedFile::fake()->create('groups.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $validFile
        ]);

        $response->assertRedirect();
    }

    // ========== BULK OPERATIONS TESTS ==========

    public function test_bulk_operations()
    {
        $this->withoutMiddleware();

        // Test bulk group deletion
        $groups = Group::factory()->count(3)->create();
        $groupIds = $groups->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post('/admin/groups/bulk-delete', [
            'group_ids' => $groupIds
        ]);

        $response->assertRedirect();

        foreach ($groupIds as $id) {
            $this->assertDatabaseMissing('groups', ['id' => $id]);
        }

        // Test bulk batch action
        $batches = Batch::factory()->count(3)->create(['is_active' => false]);
        $batchIds = $batches->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post('/admin/batches/bulk-action', [
            'batch_ids' => $batchIds,
            'action' => 'activate'
        ]);

        $response->assertRedirect();

        foreach ($batches as $batch) {
            $batch->refresh();
            $this->assertTrue($batch->is_active);
        }
    }

    // ========== SEARCH AND FILTER TESTS ==========

    public function test_search_and_filter_functionality()
    {
        $this->withoutMiddleware();

        // Create test data
        AreaOfInterest::factory()->create(['name' => 'Machine Learning']);
        AreaOfInterest::factory()->create(['name' => 'Web Development']);
        AreaOfInterest::factory()->create(['name' => 'Mobile Development']);

        // Test search in areas of interest
        $response = $this->actingAs($this->admin)->get('/admin/areas-of-interest?search=Machine');
        $response->assertStatus(200);
        $response->assertSee('Machine Learning');
        $response->assertDontSee('Web Development');
    }

    // ========== EXPORT FUNCTIONALITY TESTS ==========

    public function test_export_functionality()
    {
        $this->withoutMiddleware();

        // Test performance data export
        $response = $this->actingAs($this->admin)->get('/admin/performance/export');
        $response->assertStatus(200);

        // Test meetings PDF export
        $response = $this->actingAs($this->student)->get('/student/meetings/pdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Test template download
        $response = $this->actingAs($this->advisor)->get('/advisor/groups/download-template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}