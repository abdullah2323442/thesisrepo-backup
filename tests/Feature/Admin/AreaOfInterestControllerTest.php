<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AreaOfInterestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    public function test_index_displays_areas_of_interest()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        AreaOfInterest::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/areas-of-interest');

        $response->assertStatus(200);
        $response->assertSee('Area of Interest Management');
        $response->assertViewHas('areas');
        $this->assertCount(3, $response->viewData('areas'));
    }

    public function test_create_displays_form()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->get('/admin/areas-of-interest/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Area of Interest');
        $response->assertSee('Name');
        $response->assertSee('Description');
    }

    public function test_store_creates_new_area_of_interest()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $data = [
            'name' => 'Machine Learning',
            'description' => 'Study of algorithms that improve automatically through experience',
            'is_active' => true
        ];

        $response = $this->actingAs($admin)->post('/admin/areas-of-interest', $data);

        $response->assertRedirect('/admin/areas-of-interest');
        $this->assertDatabaseHas('area_of_interests', [
            'name' => 'Machine Learning',
            'description' => 'Study of algorithms that improve automatically through experience',
            'is_active' => true
        ]);
    }

    public function test_store_bulk_creates_multiple_areas()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $data = [
            'areas' => "Machine Learning\nWeb Development\nData Science"
        ];

        $response = $this->actingAs($admin)->post('/admin/areas-of-interest/bulk', $data);

        $response->assertRedirect('/admin/areas-of-interest');
        $this->assertDatabaseHas('area_of_interests', ['name' => 'Machine Learning']);
        $this->assertDatabaseHas('area_of_interests', ['name' => 'Web Development']);
        $this->assertDatabaseHas('area_of_interests', ['name' => 'Data Science']);
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $area = AreaOfInterest::factory()->create([
            'name' => 'Artificial Intelligence',
            'description' => 'AI research area'
        ]);

        $response = $this->actingAs($admin)->get("/admin/areas-of-interest/{$area->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Area of Interest');
        $response->assertSee('Artificial Intelligence');
        $response->assertSee('AI research area');
    }

    public function test_update_modifies_existing_area()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $area = AreaOfInterest::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old description'
        ]);

        $data = [
            'name' => 'New Name',
            'description' => 'New description',
            'is_active' => false
        ];

        $response = $this->actingAs($admin)->put("/admin/areas-of-interest/{$area->id}", $data);

        $response->assertRedirect('/admin/areas-of-interest');
        $this->assertDatabaseHas('area_of_interests', [
            'id' => $area->id,
            'name' => 'New Name',
            'description' => 'New description',
            'is_active' => false
        ]);
    }

    public function test_destroy_deletes_area_of_interest()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $area = AreaOfInterest::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/areas-of-interest/{$area->id}");

        $response->assertRedirect('/admin/areas-of-interest');
        $this->assertDatabaseMissing('area_of_interests', ['id' => $area->id]);
    }

    public function test_store_validates_required_fields()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->post('/admin/areas-of-interest', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validates_unique_name()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        AreaOfInterest::factory()->create(['name' => 'Existing Area']);

        $data = [
            'name' => 'Existing Area',
            'description' => 'Some description'
        ];

        $response = $this->actingAs($admin)->post('/admin/areas-of-interest', $data);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_validates_unique_name_except_current()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $area1 = AreaOfInterest::factory()->create(['name' => 'Area 1']);
        $area2 = AreaOfInterest::factory()->create(['name' => 'Area 2']);

        $data = [
            'name' => 'Area 1', // Same as area1
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($admin)->put("/admin/areas-of-interest/{$area2->id}", $data);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_index_shows_supervisor_count()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $area = AreaOfInterest::factory()->create();
        $supervisor = Supervisor::factory()->create();
        
        $area->supervisors()->attach($supervisor->id);

        $response = $this->actingAs($admin)->get('/admin/areas-of-interest');

        $response->assertStatus(200);
        $response->assertSee('1 Supervisor'); // Should show supervisor count
    }

    public function test_bulk_store_handles_empty_lines()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $data = [
            'areas' => "Machine Learning\n\nWeb Development\n\n\nData Science\n"
        ];

        $response = $this->actingAs($admin)->post('/admin/areas-of-interest/bulk', $data);

        $response->assertRedirect('/admin/areas-of-interest');
        $this->assertEquals(3, AreaOfInterest::count()); // Should only create 3, ignoring empty lines
    }
}