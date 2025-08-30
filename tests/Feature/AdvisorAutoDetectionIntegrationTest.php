<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Services\StudentApiService;
use App\Services\SupervisorApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdvisorAutoDetectionIntegrationTest extends TestCase
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
            'type_id' => json_encode(['1', '2']),
            'api_id' => 4
        ]);

        // Mock services
        $this->studentApiService = $this->createMock(StudentApiService::class);
        $this->supervisorApiService = $this->createMock(SupervisorApiService::class);
        
        $this->app->instance(StudentApiService::class, $this->studentApiService);
        $this->app->instance(SupervisorApiService::class, $this->supervisorApiService);
    }

    public function test_complete_advisor_auto_detection_flow_success()
    {
        // Create a group without advisor
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin',
            'advisor_auto_detected' => false
        ]);

        // Mock student API to return student with advisor
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
                        'name' => 'John Doe',
                        'advisor_id' => 17,
                        'advisor' => 'Farhana Shirin Chowdhury'
                    ]
                ]
            ]);

        // Mock teacher API to return advisor data
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
                    ],
                    [
                        'id' => 18,
                        'fullname' => 'Another Teacher',
                        'gender' => 'Male',
                        'email' => 'another@example.com',
                        'designation' => 'Assistant Professor',
                        'department' => 'Computer Science & Engineering'
                    ]
                ]
            ])
        ]);

        // Assign student to group
        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert successful response
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Student assigned to group successfully');

        // Assert advisor was created in database
        $this->assertDatabaseHas('users', [
            'api_id' => 17,
            'name' => 'Farhana Shirin Chowdhury',
            'email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'login_type' => 'teacher',
            'username' => 'Farhana Shirin Chowdhury'
        ]);

        // Assert group was updated with advisor
        $group->refresh();
        $this->assertNotNull($group->advisor_id);
        $this->assertTrue($group->advisor_auto_detected);

        // Assert student was assigned
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682',
            'student_name' => 'John Doe'
        ]);

        // Assert advisor relationship
        $advisor = User::where('api_id', 17)->first();
        $this->assertEquals($advisor->id, $group->advisor_id);
    }

    public function test_advisor_auto_detection_fails_when_advisor_not_in_api()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API
        $this->studentApiService->method('getBatches')
            ->willReturn(['success' => true, 'batches' => [39]]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'John Doe',
                        'advisor_id' => 999, // Non-existent advisor
                        'advisor' => 'Non Existent Advisor'
                    ]
                ]
            ]);

        // Mock teacher API without the advisor
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

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert failure response
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHasErrors();

        // Assert advisor was not created
        $this->assertDatabaseMissing('users', ['api_id' => 999]);

        // Assert group advisor was not set
        $group->refresh();
        $this->assertNull($group->advisor_id);

        // Assert student was not assigned
        $this->assertDatabaseMissing('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_advisor_auto_detection_with_name_fallback()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API
        $this->studentApiService->method('getBatches')
            ->willReturn(['success' => true, 'batches' => [39]]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'John Doe',
                        'advisor_id' => 999, // ID doesn't match
                        'advisor' => 'Farhana Shirin Chowdhury' // But name matches
                    ]
                ]
            ]);

        // Mock teacher API with advisor having different ID but matching name
        Http::fake([
            'http://puc.ac.bd:8012/api/Teacher/TeacherList*' => Http::response([
                'Data' => [
                    [
                        'id' => 17, // Different ID
                        'fullname' => 'Farhana Shirin Chowdhury', // Matching name
                        'gender' => 'Female',
                        'email' => 'fshirin2007@gmail.com',
                        'designation' => 'Associate Professor',
                        'department' => 'Computer Science & Engineering'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert successful response (name fallback worked)
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert advisor was created with the API's actual ID (17, not 999)
        $this->assertDatabaseHas('users', [
            'api_id' => 17,
            'name' => 'Farhana Shirin Chowdhury',
            'login_type' => 'teacher'
        ]);

        // Assert group was updated
        $group->refresh();
        $this->assertNotNull($group->advisor_id);
        $this->assertTrue($group->advisor_auto_detected);
    }

    public function test_advisor_auto_detection_handles_api_failure()
    {
        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => null,
            'created_by_type' => 'admin'
        ]);

        // Mock student API
        $this->studentApiService->method('getBatches')
            ->willReturn(['success' => true, 'batches' => [39]]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'John Doe',
                        'advisor_id' => 17,
                        'advisor' => 'Farhana Shirin Chowdhury'
                    ]
                ]
            ]);

        // Mock teacher API failure
        Http::fake([
            'http://puc.ac.bd:8012/api/Teacher/TeacherList*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert failure response
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Assert no advisor was created
        $this->assertDatabaseMissing('users', ['api_id' => 17]);

        // Assert group advisor was not set
        $group->refresh();
        $this->assertNull($group->advisor_id);
    }

    public function test_advisor_auto_detection_skipped_when_group_already_has_advisor()
    {
        // Create advisor and group with existing advisor
        $existingAdvisor = User::factory()->create([
            'api_id' => 20,
            'login_type' => 'teacher'
        ]);

        $group = Group::factory()->create([
            'batch_number' => 39,
            'advisor_id' => $existingAdvisor->id,
            'created_by_type' => 'admin'
        ]);

        // Mock student API
        $this->studentApiService->method('getBatches')
            ->willReturn(['success' => true, 'batches' => [39]]);

        $this->studentApiService->method('getStudentsByBatch')
            ->willReturn([
                'success' => true,
                'students' => [
                    [
                        'roll' => '1803510201682',
                        'name' => 'John Doe',
                        'advisor_id' => 20, // Same advisor
                        'advisor' => 'Existing Advisor'
                    ]
                ]
            ]);

        // No HTTP fake needed - API should not be called

        $response = $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert successful response
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert group advisor unchanged
        $group->refresh();
        $this->assertEquals($existingAdvisor->id, $group->advisor_id);

        // Assert student was assigned
        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);
    }

    public function test_advisor_auto_detection_logs_detailed_information()
    {
        Log::spy();

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
                        'name' => 'John Doe',
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

        $this->actingAs($this->admin)->post('/admin/groups/assign-student', [
            'group_id' => $group->id,
            'student_id' => '1803510201682'
        ]);

        // Assert specific log messages were called
        Log::shouldHaveReceived('info')
            ->with('Advisor not found locally, attempting to fetch from API', \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Attempting to fetch advisor from teacher API', \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Teacher API response received', \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Advisor found in API, creating user', \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Advisor successfully created from API', \Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Advisor successfully created from API and auto-detected', \Mockery::type('array'));
    }
}