<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Batch;
use App\Services\StudentApiService;
use App\Services\SupervisorApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroupManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $studentApiService;
    protected $supervisorApiService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['1', '2']), // Admin and Teacher
            'api_id' => 4
        ]);

        // Mock services
        $this->studentApiService = $this->createMock(StudentApiService::class);
        $this->supervisorApiService = $this->createMock(SupervisorApiService::class);
        
        $this->app->instance(StudentApiService::class, $this->studentApiService);
        $this->app->instance(SupervisorApiService::class, $this->supervisorApiService);
    }

    public function test_index_displays_group_management_page()
    {
        $batch = Batch::factory()->create(['batch_number' => 39]);
        $group = Group::factory()->create(['batch_number' => 39]);

        $response = $this->actingAs($this->admin)->get('/admin/groups?batch=39');

        $response->assertStatus(200);
        $response->assertViewHas(['availableBatches', 'selectedBatch', 'groups']);
    }

    public function test_create_group_successfully()
    {
        $batch = 39;
        $supervisor = Supervisor::factory()->create(['thesis_limit' => 5]);
        $areaOfInterest = AreaOfInterest::factory()->create();

        $data = [
            'batch' => $batch,
            'group_name' => 'Test Group',
            'supervisor_id' => $supervisor->id,
            'area_of_interest_ids' => [$areaOfInterest->id]
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
            'batch_number' => $batch,
            'supervisor_id' => $supervisor->id,
            'created_by_type' => 'admin',
            'created_by_admin_id' => $this->admin->id
        ]);
    }

    public function test_assign_student_with_existing_advisor()
    {
        // Create group and advisor
        $advisor = User::factory()->create([
            'api_id' => 17,
            'login_type' => 'teacher',
            'name' => 'Farhana Shirin Chowdhury'
        ]);
        
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API response
        $this->studentApiService->method('getBatches')
            ->willReturn([
                'success' => true,
                'batches' => [39]
            ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 17,
                        'advisor' => 'Farhana Shirin Chowdhury'
                    ]
                ]
            ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check that advisor was auto-detected
        $group->refresh();
        $this->assertEquals($advisor->id, $group->advisor_id);
        $this->assertTrue($group->advisor_auto_detected);
        
        // Check student assignment
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682',
            'student_name' => 'Test Student'
        ]);
    }

    public function test_assign_student_with_advisor_auto_creation_from_api()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API response
        $this->studentApiService->method('getBatches')
            ->willReturn([
                'success' => true,
                'batches' => [39]
            ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 17,
                        'advisor' => 'Farhana Shirin Chowdhury'
                    ]
                ]
            ]);

        // Mock teacher API response for advisor creation
        Http::fake([
            'http://puc.ac.bd:8012/api/Teacher/TeacherList*' => Http::response([
                'Data' => [
                    [
                        'id' => 17,
                        'fullname' => 'Farhana Shirin Chowdhury',
                        'gender' => 'Female',
                        'email' => 'fshirin2007@gmail.com',
                        'designation' => 'Associate Professor',
                        'department' => 'Computer Science & Engineering'
                    ]
                ]
            ])
        ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check that advisor was created and auto-detected
        $group->refresh();
        $this->assertNotNull($group->advisor_id);
        $this->assertTrue($group->advisor_auto_detected);
        
        // Check advisor was created in database
        $this->assertDatabaseHas('users', [
            'api_id' => 17,
            'name' => 'Farhana Shirin Chowdhury',
            'login_type' => 'teacher'
        ]);
        
        // Check student assignment
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682',
            'student_name' => 'Test Student'
        ]);
    }

    public function test_assign_student_fails_when_advisor_not_found_in_api()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API response
        $this->studentApiService->method('getBatches')
            ->willReturn([
                'success' => true,
                'batches' => [39]
            ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 999, // Non-existent advisor
                        'advisor' => 'Non Existent Advisor'
                    ]
                ]
            ]);

        // Mock teacher API response without the advisor
        Http::fake([
            'http://puc.ac.bd:8012/api/Teacher/TeacherList*' => Http::response([
                'Data' => [
                    [
                        'id' => 17,
                        'fullname' => 'Farhana Shirin Chowdhury',
                        'gender' => 'Female',
                        'email' => 'fshirin2007@gmail.com',
                        'designation' => 'Associate Professor',
                        'department' => 'Computer Science & Engineering'
                    ]
                ]
            ])
        ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Check that advisor was not set
        $group->refresh();
        $this->assertNull($group->advisor_id);
        
        // Check student was not assigned
        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_assign_student_enforces_same_advisor_per_group()
    {
        // Create advisor and group with existing advisor
        $advisor1 = User::factory()->create(['api_id' => 17, 'login_type' => 'teacher']);
        $advisor2 = User::factory()->create(['api_id' => 18, 'login_type' => 'teacher']);
        
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $advisor1->id,
            'created_by_type' => 'admin'
        ]);

        // Mock student API response with different advisor
        $this->studentApiService->method('getBatches')
            ->willReturn([
                'success' => true,
                'batches' => [39]
            ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 18, // Different advisor
                        'advisor' => 'Different Advisor'
                    ]
                ]
            ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Check student was not assigned
        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_assign_student_prevents_duplicate_assignment()
    {
        $group1 = Group::factory()->create(['batch_number' => 39]);
        $group2 = Group::factory()->create(['batch_number' => 39]);
        
        // Create existing assignment
        GroupStudent::factory()->create([
            'group_id' => $group1->id,
            'student_id' => '1803510201682'
        ]);

        $data = [
            'group_id' => $group2->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Check student was not assigned to second group
        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group2->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_assign_student_respects_group_capacity()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'max_students' => 4
        ]);
        
        // Fill group to capacity
        GroupStudent::factory()->count(4)->create(['group_id' => $group->id]);

        // Mock student API response
        $this->studentApiService->method('getBatches')
            ->willReturn([
                'success' => true,
                'batches' => [39]
            ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 17,
                        'advisor' => 'Test Advisor'
                    ]
                ]
            ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Check student was not assigned
        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_remove_student_from_group()
    {
        $group = Group::factory()->create();
        $groupStudent = GroupStudent::factory()->create(['group_id' => $group->id]);

        $data = ['group_student_id' => $groupStudent->id];

        $response = $this->actingAs($this->admin)->post('/admin/groups/remove-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('group_students', ['id' => $groupStudent->id]);
    }

    public function test_assign_areas_of_interest_to_group()
    {
        $group = Group::factory()->create();
        $areas = AreaOfInterest::factory()->count(2)->create();

        $data = [
            'group_id' => $group->id,
            'area_of_interest_ids' => $areas->pluck('id')->toArray()
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-area-of-interest', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check areas were assigned
        $this->assertEquals(2, $group->areasOfInterest()->count());
    }

    public function test_assign_supervisor_to_group()
    {
        $group = Group::factory()->create();
        $supervisor = Supervisor::factory()->create(['thesis_limit' => 5]);

        $data = [
            'group_id' => $group->id,
            'supervisor_id' => $supervisor->id
        ];

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-supervisor', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $group->refresh();
        $this->assertEquals($supervisor->id, $group->supervisor_id);
        $this->assertTrue($group->is_manual_assignment);
    }

    public function test_unassign_supervisor_from_group()
    {
        $supervisor = Supervisor::factory()->create();
        $group = Group::factory()->create([
            'supervisor_id' => $supervisor->id,
            'is_manual_assignment' => true
        ]);

        $data = ['group_id' => $group->id];

        $response = $this->actingAs($this->admin)->post('/admin/groups/unassign-supervisor', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $group->refresh();
        $this->assertNull($group->supervisor_id);
        $this->assertFalse($group->is_manual_assignment);
    }

    public function test_delete_group_without_students()
    {
        $group = Group::factory()->create(['name' => 'Group 1', 'batch_number' => 39]);

        $response = $this->actingAs($this->admin)->delete("/admin/groups/{$group->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_delete_group_with_students_fails()
    {
        $group = Group::factory()->create();
        GroupStudent::factory()->create(['group_id' => $group->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/groups/{$group->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('groups', ['id' => $group->id]);
    }

    public function test_bulk_delete_groups()
    {
        $groups = Group::factory()->count(3)->create(['batch_number' => 39]);
        $groupIds = $groups->pluck('id')->toArray();

        $data = ['group_ids' => $groupIds];

        $response = $this->actingAs($this->admin)->post('/admin/groups/bulk-delete', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($groupIds as $groupId) {
            $this->assertDatabaseMissing('groups', ['id' => $groupId]);
        }
    }

    public function test_get_available_supervisors_for_area_of_interest()
    {
        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create(['thesis_limit' => 5]);
        $supervisor->areasOfInterest()->attach($area->id);

        $response = $this->actingAs($this->admin)
            ->get("/admin/groups/available-supervisors?area_of_interest_id={$area->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'fullname', 'designation', 'available_slots', 'rank_priority']
        ]);
    }

    public function test_advisor_auto_detection_logs_properly()
    {
        Log::shouldReceive('info')->once()->with('Advisor not found locally, attempting to fetch from API', \Mockery::type('array'));
        Log::shouldReceive('info')->once()->with('Attempting to fetch advisor from teacher API', \Mockery::type('array'));
        Log::shouldReceive('info')->once()->with('Teacher API response received', \Mockery::type('array'));
        Log::shouldReceive('info')->once()->with('Advisor found in API, creating user', \Mockery::type('array'));
        Log::shouldReceive('info')->once()->with('Advisor successfully created from API', \Mockery::type('array'));
        Log::shouldReceive('info')->once()->with('Advisor successfully created from API and auto-detected', \Mockery::type('array'));

        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        $this->studentApiService->method('getBatches')
            ->willReturn(['success' => true, 'batches' => [39]]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'Test Student',
                        'advisor_id' => 17,
                        'advisor' => 'Farhana Shirin Chowdhury'
                    ]
                ]
            ]);

        Http::fake([
            'http://puc.ac.bd:8012/api/Teacher/TeacherList*' => Http::response([
                'Data' => [
                    [
                        'id' => 17,
                        'fullname' => 'Farhana Shirin Chowdhury',
                        'gender' => 'Female',
                        'email' => 'fshirin2007@gmail.com',
                        'designation' => 'Associate Professor',
                        'department' => 'Computer Science & Engineering'
                    ]
                ]
            ])
        ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $this->actingAs($this->admin)->post('/admin/groups/assign-student', $data);
    }
}