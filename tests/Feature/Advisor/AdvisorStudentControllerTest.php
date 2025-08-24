<?php

namespace Tests\Feature\Advisor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class AdvisorStudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    protected function createAdvisorWithSupervisor()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email,
            'fullname' => $advisor->name
        ]);

        return $advisor;
    }

    public function test_index_displays_students()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $response->assertSee('My Students');
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
        $response->assertViewHas('students');
    }

    public function test_index_filters_by_batch()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2021123002',
                        'advisor_id' => 123,
                        'batch_name' => '2021'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students?batch=2020');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    public function test_index_searches_students()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    public function test_show_displays_student_details()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020',
                        'email' => 'john@example.com'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students/1');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('2020123001');
        $response->assertSee('john@example.com');
        $response->assertViewHas('student');
    }

    public function test_show_redirects_if_student_not_found()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response(['Data' => []])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students/999');

        $response->assertRedirect('/advisor/students');
        $response->assertSessionHas('error');
    }

    public function test_refresh_data_clears_cache()
    {
        $advisor = $this->createAdvisorWithSupervisor();

        $response = $this->actingAs($advisor)->post('/advisor/students/refresh');

        $response->assertRedirect('/advisor/students');
        $response->assertSessionHas('success');
    }

    public function test_index_requires_supervisor_record()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        // No supervisor record

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertRedirect('/teacher/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_index_handles_api_failure()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([], 500) // API failure
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $response->assertViewHas('error');
        $response->assertSee('Failed to fetch student data');
    }

    public function test_index_shows_no_active_batches_message()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        // No active batches

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $response->assertViewHas('no_active_batches', true);
        $response->assertSee('No active batches found');
    }

    public function test_index_sorts_students_by_roll()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ],
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $students = $response->viewData('students');
        
        // Should be sorted by roll number
        $this->assertEquals('2020123001', $students->first()['roll']);
        $this->assertEquals('2020123002', $students->last()['roll']);
    }

    public function test_show_requires_student_to_be_advisors()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 999, // Different advisor
                        'batch_name' => '2020'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students/1');

        $response->assertRedirect('/advisor/students');
        $response->assertSessionHas('error');
    }

    public function test_index_shows_total_students_count()
    {
        $advisor = $this->createAdvisorWithSupervisor();
        Batch::factory()->create(['batch_number' => 2020, 'is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123,
                        'batch_name' => '2020'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/students');

        $response->assertStatus(200);
        $response->assertViewHas('totalStudents', 2);
    }
}