<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Services\SupervisorApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SupervisorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    public function test_index_displays_supervisors()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        Supervisor::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/supervisors');

        $response->assertStatus(200);
        $response->assertSee('Supervisor Management');
        $response->assertViewHas('supervisors');
        $this->assertCount(3, $response->viewData('supervisors'));
    }

    public function test_index_shows_supervisor_statistics()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Supervisor::factory()->count(5)->create(['is_active' => true]);
        Supervisor::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($admin)->get('/admin/supervisors');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(7, $stats['total']);
        $this->assertEquals(5, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    public function test_sync_from_api_calls_service()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'Id' => 1,
                        'Name' => 'Dr. John Smith',
                        'Gender' => 'Male',
                        'Email' => 'john@example.com',
                        'Designation' => 'Professor',
                        'DeptName' => 'Computer Science'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($admin)->post('/admin/supervisors/sync');

        $response->assertRedirect('/admin/supervisors');
        $response->assertSessionHas('success');
    }

    public function test_edit_displays_supervisor_form()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create([
            'fullname' => 'Dr. Jane Doe',
            'thesis_limit' => 5
        ]);

        $response = $this->actingAs($admin)->get("/admin/supervisors/{$supervisor->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Supervisor');
        $response->assertSee('Dr. Jane Doe');
        $response->assertSee('5'); // thesis limit
    }

    public function test_update_modifies_supervisor()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create(['thesis_limit' => 3]);

        $data = [
            'thesis_limit' => 5,
            'is_active' => false
        ];

        $response = $this->actingAs($admin)->put("/admin/supervisors/{$supervisor->id}", $data);

        $response->assertRedirect('/admin/supervisors');
        $this->assertDatabaseHas('supervisors', [
            'id' => $supervisor->id,
            'thesis_limit' => 5,
            'is_active' => false
        ]);
    }

    public function test_bulk_update_limits_updates_multiple_supervisors()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor1 = Supervisor::factory()->create(['thesis_limit' => 3]);
        $supervisor2 = Supervisor::factory()->create(['thesis_limit' => 3]);

        $data = [
            'supervisors' => [
                $supervisor1->id => ['thesis_limit' => 5],
                $supervisor2->id => ['thesis_limit' => 7]
            ]
        ];

        $response = $this->actingAs($admin)->post('/admin/supervisors/bulk-limits', $data);

        $response->assertRedirect('/admin/supervisors');
        $this->assertDatabaseHas('supervisors', ['id' => $supervisor1->id, 'thesis_limit' => 5]);
        $this->assertDatabaseHas('supervisors', ['id' => $supervisor2->id, 'thesis_limit' => 7]);
    }

    public function test_toggle_status_changes_supervisor_status()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/supervisors/{$supervisor->id}/toggle");

        $response->assertRedirect('/admin/supervisors');
        $this->assertDatabaseHas('supervisors', [
            'id' => $supervisor->id,
            'is_active' => false
        ]);
    }

    public function test_refresh_from_api_updates_supervisor()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create(['api_id' => 123]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'Id' => 123,
                        'Name' => 'Dr. Updated Name',
                        'Gender' => 'Male',
                        'Email' => 'updated@example.com',
                        'Designation' => 'Associate Professor',
                        'DeptName' => 'Computer Science'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($admin)->post("/admin/supervisors/{$supervisor->id}/refresh");

        $response->assertRedirect('/admin/supervisors');
        $response->assertSessionHas('success');
    }

    public function test_index_filters_by_status()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Supervisor::factory()->create(['is_active' => true, 'fullname' => 'Active Supervisor']);
        Supervisor::factory()->create(['is_active' => false, 'fullname' => 'Inactive Supervisor']);

        $response = $this->actingAs($admin)->get('/admin/supervisors?status=active');

        $response->assertStatus(200);
        $response->assertSee('Active Supervisor');
        $response->assertDontSee('Inactive Supervisor');
    }

    public function test_index_searches_by_name()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Supervisor::factory()->create(['fullname' => 'Dr. John Smith']);
        Supervisor::factory()->create(['fullname' => 'Dr. Jane Doe']);

        $response = $this->actingAs($admin)->get('/admin/supervisors?search=John');

        $response->assertStatus(200);
        $response->assertSee('Dr. John Smith');
        $response->assertDontSee('Dr. Jane Doe');
    }

    public function test_edit_shows_areas_of_interest()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create();
        $area = AreaOfInterest::factory()->create(['name' => 'Machine Learning']);
        
        $supervisor->areasOfInterest()->attach($area->id);

        $response = $this->actingAs($admin)->get("/admin/supervisors/{$supervisor->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Machine Learning');
        $response->assertViewHas('areas');
    }

    public function test_update_validates_thesis_limit()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $supervisor = Supervisor::factory()->create();

        $data = [
            'thesis_limit' => -1 // Invalid negative value
        ];

        $response = $this->actingAs($admin)->put("/admin/supervisors/{$supervisor->id}", $data);

        $response->assertSessionHasErrors(['thesis_limit']);
    }

    public function test_sync_handles_api_failure()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Http::fake([
            '*' => Http::response([], 500) // Simulate API failure
        ]);

        $response = $this->actingAs($admin)->post('/admin/supervisors/sync');

        $response->assertRedirect('/admin/supervisors');
        $response->assertSessionHas('error');
    }
}