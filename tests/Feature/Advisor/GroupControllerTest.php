<?php

namespace Tests\Feature\Advisor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Batch;
use App\Services\StudentApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $advisor;
    protected $studentApiService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create advisor user
        $this->advisor = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['2']), // Teacher
            'api_id' => 17, // Advisor API ID
            'name' => 'Test Advisor'
        ]);

        // Mock student API service
        $this->studentApiService = $this->createMock(StudentApiService::class);
        $this->app->instance(StudentApiService::class, $this->studentApiService);
    }

    public function test_index_displays_group_management_page()
    {
        // Mock API responses
        $this->studentApiService->method('getStudentsByAdvisor')
            ->willReturn([
                'success' => true,
                'batches' => [39, 40]
            ]);

        $response = $this->actingAs($this->advisor)->get('/advisor/groups');

        $response->assertStatus(200);
        $response->assertViewHas(['batches', 'groups', 'areasOfInterest']);
    }

    public function test_index_with_selected_batch_shows_groups_and_students()
    {
        // Create groups for the advisor
        $advisorCreatedGroup = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $adminCreatedGroup = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'admin'
        ]);

        $otherAdvisorGroup = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => 999, // Different advisor
            'created_by_type' => 'advisor'
        ]);

        // Mock API responses
        $this->studentApiService->method('getStudentsByAdvisor')
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

        $response = $this->actingAs($this->advisor)->get('/advisor/groups?batch=39');

        $response->assertStatus(200);
        $response->assertViewHas([
            'groups', 'readonlyGroups', 'adminCreatedGroups', 
            'students', 'unassignedStudents'
        ]);

        // Check that only advisor's groups are in the main groups collection
        $groups = $response->viewData('groups');
        $this->assertCount(1, $groups);
        $this->assertEquals($advisorCreatedGroup->id, $groups->first()->id);

        // Check readonly groups from other advisors
        $readonlyGroups = $response->viewData('readonlyGroups');
        $this->assertCount(1, $readonlyGroups);
        $this->assertEquals($otherAdvisorGroup->id, $readonlyGroups->first()->id);

        // Check admin created groups
        $adminGroups = $response->viewData('adminCreatedGroups');
        $this->assertCount(1, $adminGroups);
        $this->assertEquals($adminCreatedGroup->id, $adminGroups->first()->id);
    }

    public function test_create_groups_for_batch()
    {
        // Mock API responses
        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    ['roll' => '001', 'name' => 'Student 1', 'advisor_id' => 17],
                    ['roll' => '002', 'name' => 'Student 2', 'advisor_id' => 17],
                    ['roll' => '003', 'name' => 'Student 3', 'advisor_id' => 17],
                    ['roll' => '004', 'name' => 'Student 4', 'advisor_id' => 17],
                    ['roll' => '005', 'name' => 'Student 5', 'advisor_id' => 17],
                ]
            ]);

        $data = ['batch' => 39];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/create', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Should create 2 groups (5 students / 3 per group = 2 groups)
        $this->assertDatabaseHas('groups', [
            'name' => 'Group 1',
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $this->assertDatabaseHas('groups', [
            'name' => 'Group 2',
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);
    }

    public function test_create_groups_fails_when_groups_already_exist()
    {
        // Create existing group
        Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    ['roll' => '001', 'name' => 'Student 1', 'advisor_id' => 17]
                ]
            ]);

        $data = ['batch' => 39];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/create', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_add_single_group()
    {
        $data = [
            'batch' => 39,
            'group_name' => 'Special Group'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/add', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('groups', [
            'name' => 'Special Group',
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);
    }

    public function test_assign_student_to_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor',
            'max_students' => 3
        ]);

        // Mock API responses
        $this->studentApiService->method('getStudentsByAdvisor')
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
                        'advisor_id' => 17
                    ]
                ]
            ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682',
            'student_name' => 'Test Student'
        ]);
    }

    public function test_assign_student_fails_for_admin_created_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'admin' // Admin-created group
        ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_assign_student_fails_when_group_is_full()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor',
            'max_students' => 3
        ]);

        // Fill the group
        GroupStudent::factory()->count(3)->create(['group_id' => $group->id]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_assign_student_fails_when_already_assigned()
    {
        $group1 = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $group2 = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        // Assign student to first group
        GroupStudent::factory()->create([
            'group_id' => $group1->id,
            'student_id' => '1803510201682'
        ]);

        $data = [
            'group_id' => $group2->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group2->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_assign_areas_of_interest_to_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $areas = AreaOfInterest::factory()->count(2)->create();

        $data = [
            'group_id' => $group->id,
            'area_of_interest_ids' => $areas->pluck('id')->toArray()
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-area-of-interest', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(2, $group->areasOfInterest()->count());
    }

    public function test_assign_areas_of_interest_fails_for_admin_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'admin'
        ]);

        $area = AreaOfInterest::factory()->create();

        $data = [
            'group_id' => $group->id,
            'area_of_interest_ids' => [$area->id]
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-area-of-interest', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_remove_student_from_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        $groupStudent = GroupStudent::factory()->create(['group_id' => $group->id]);

        $data = ['group_student_id' => $groupStudent->id];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('group_students', ['id' => $groupStudent->id]);
    }

    public function test_remove_student_fails_for_admin_group()
    {
        $group = Group::factory()->create([
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'admin'
        ]);

        $groupStudent = GroupStudent::factory()->create(['group_id' => $group->id]);

        $data = ['group_student_id' => $groupStudent->id];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('group_students', ['id' => $groupStudent->id]);
    }

    public function test_remove_all_groups_for_batch()
    {
        // Create advisor groups
        $groups = Group::factory()->count(3)->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        // Create admin group (should not be deleted)
        $adminGroup = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'admin'
        ]);

        // Add students to advisor groups
        foreach ($groups as $group) {
            GroupStudent::factory()->create(['group_id' => $group->id]);
        }

        $data = ['batch' => 39];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/remove-all', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check advisor groups are deleted
        foreach ($groups as $group) {
            $this->assertDatabaseMissing('groups', ['id' => $group->id]);
        }

        // Check admin group is not deleted
        $this->assertDatabaseHas('groups', ['id' => $adminGroup->id]);
    }

    public function test_upload_excel_file_for_bulk_assignment()
    {
        Storage::fake('local');

        // Create groups first
        Group::factory()->count(2)->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor',
            'name' => 'Group 1'
        ]);

        Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor',
            'name' => 'Group 2'
        ]);

        // Mock API responses
        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    ['roll' => '001', 'name' => 'Student 1', 'advisor_id' => 17],
                    ['roll' => '002', 'name' => 'Student 2', 'advisor_id' => 17],
                    ['roll' => '003', 'name' => 'Student 3', 'advisor_id' => 17],
                ]
            ]);

        // Create a mock Excel file
        $file = UploadedFile::fake()->create('assignments.xlsx', 100);

        // Mock Excel reading
        Excel::fake();
        Excel::shouldReceive('toArray')
            ->once()
            ->andReturn([[
                ['Student_ID', 'Group_Name'], // Header
                ['001', 'Group A'],
                ['002', 'Group A'],
                ['003', 'Group B']
            ]]);

        $data = [
            'batch' => 39,
            'excel_file' => $file
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_download_template_generates_excel_file()
    {
        // Create groups
        Group::factory()->count(2)->create([
            'batch_number' => 39,
            'advisor_id' => $this->advisor->id,
            'created_by_type' => 'advisor'
        ]);

        // Mock API responses
        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    ['roll' => '001', 'name' => 'Student 1', 'advisor_id' => 17],
                    ['roll' => '002', 'name' => 'Student 2', 'advisor_id' => 17]
                ]
            ]);

        Excel::fake();

        $response = $this->actingAs($this->advisor)->get('/advisor/groups/download-template?batch=39');

        $response->assertStatus(200);
        Excel::assertDownloaded('group_assignment_template_batch_39.xlsx');
    }

    public function test_unauthorized_access_to_other_advisor_group()
    {
        $otherAdvisor = User::factory()->create(['login_type' => 'teacher']);
        $group = Group::factory()->create([
            'advisor_id' => $otherAdvisor->id,
            'created_by_type' => 'advisor'
        ]);

        $data = [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ];

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', $data);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_api_id_validation_throws_exception_when_missing()
    {
        // Create advisor without API ID
        $advisorWithoutApiId = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => null
        ]);

        $response = $this->actingAs($advisorWithoutApiId)->get('/advisor/groups');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_batch_validation_prevents_access_to_unauthorized_batch()
    {
        // Mock API to return different batches than requested
        $this->studentApiService->method('getStudentsByAdvisor')
            ->willReturn([
                'success' => true,
                'batches' => [40, 41] // Advisor has students in batches 40, 41
            ]);

        // Try to access batch 39 (not authorized)
        $response = $this->actingAs($this->advisor)->get('/advisor/groups?batch=39');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}