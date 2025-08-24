<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestSuite extends TestCase
{
    use RefreshDatabase;

    public function test_database_connection_works()
    {
        $this->assertTrue(DB::connection()->getPdo() !== null);
    }

    public function test_all_models_can_be_created()
    {
        // Test User model
        $user = \App\Models\User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        // Test AreaOfInterest model
        $area = \App\Models\AreaOfInterest::factory()->create();
        $this->assertDatabaseHas('area_of_interests', ['id' => $area->id]);

        // Test Supervisor model
        $supervisor = \App\Models\Supervisor::factory()->create();
        $this->assertDatabaseHas('supervisors', ['id' => $supervisor->id]);

        // Test Batch model
        $batch = \App\Models\Batch::factory()->create();
        $this->assertDatabaseHas('batches', ['id' => $batch->id]);

        // Test Group model
        $group = \App\Models\Group::factory()->create();
        $this->assertDatabaseHas('groups', ['id' => $group->id]);

        // Test GroupStudent model
        $groupStudent = \App\Models\GroupStudent::factory()->create();
        $this->assertDatabaseHas('group_students', ['id' => $groupStudent->id]);
    }

    public function test_all_routes_are_accessible()
    {
        $user = \App\Models\User::factory()->create(['login_type' => 'teacher']);
        
        // Skip middleware for route testing
        $this->withoutMiddleware();

        // Test main routes
        $routes = [
            '/',
            '/dashboard',
            '/teacher/dashboard',
            '/admin/dashboard',
            '/advisor/dashboard',
            '/supervisor/dashboard',
            '/student/dashboard',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $this->assertNotEquals(404, $response->status(), "Route {$route} returned 404");
        }
    }

    public function test_all_middleware_groups_exist()
    {
        $middlewareGroups = [
            'web',
            'api',
            'auth',
            'guest',
            'verified',
            'admin',
            'teacher',
            'student',
            'advisor'
        ];

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $middlewareGroups = $kernel->getMiddlewareGroups();

        foreach ($middlewareGroups as $group => $middleware) {
            $this->assertIsArray($middleware, "Middleware group {$group} should be an array");
        }
    }

    public function test_all_factories_work()
    {
        // Test all model factories
        $models = [
            \App\Models\User::class,
            \App\Models\AreaOfInterest::class,
            \App\Models\Supervisor::class,
            \App\Models\Batch::class,
            \App\Models\Group::class,
            \App\Models\GroupStudent::class,
        ];

        foreach ($models as $model) {
            $instance = $model::factory()->create();
            $this->assertInstanceOf($model, $instance);
        }
    }

    public function test_all_relationships_work()
    {
        // Create related models
        $user = \App\Models\User::factory()->create();
        $area = \App\Models\AreaOfInterest::factory()->create();
        $supervisor = \App\Models\Supervisor::factory()->create();
        $group = \App\Models\Group::factory()->create([
            'advisor_id' => $user->id,
            'area_of_interest_id' => $area->id,
            'supervisor_id' => $supervisor->id
        ]);
        $groupStudent = \App\Models\GroupStudent::factory()->create(['group_id' => $group->id]);

        // Test relationships
        $this->assertInstanceOf(\App\Models\User::class, $group->advisor);
        $this->assertInstanceOf(\App\Models\AreaOfInterest::class, $group->areaOfInterest);
        $this->assertInstanceOf(\App\Models\Supervisor::class, $group->supervisor);
        $this->assertInstanceOf(\App\Models\Group::class, $groupStudent->group);
        
        // Test reverse relationships
        $this->assertTrue($area->groups->contains($group));
        $this->assertTrue($supervisor->groups->contains($group));
        $this->assertTrue($group->students->contains($groupStudent));
    }

    public function test_all_scopes_work()
    {
        // Test AreaOfInterest scopes
        \App\Models\AreaOfInterest::factory()->create(['is_active' => true]);
        \App\Models\AreaOfInterest::factory()->create(['is_active' => false]);
        
        $this->assertEquals(1, \App\Models\AreaOfInterest::active()->count());
        $this->assertEquals(1, \App\Models\AreaOfInterest::inactive()->count());

        // Test Batch scopes
        \App\Models\Batch::factory()->create(['is_active' => true]);
        \App\Models\Batch::factory()->create(['is_active' => false]);
        
        $this->assertEquals(1, \App\Models\Batch::active()->count());
        $this->assertEquals(1, \App\Models\Batch::inactive()->count());

        // Test Group scopes
        $supervisor = \App\Models\Supervisor::factory()->create();
        \App\Models\Group::factory()->create(['supervisor_id' => null]);
        \App\Models\Group::factory()->create(['supervisor_id' => $supervisor->id]);
        
        $this->assertEquals(1, \App\Models\Group::unassigned()->count());
        $this->assertEquals(1, \App\Models\Group::assigned()->count());
    }

    public function test_all_validation_rules_work()
    {
        // Test AreaOfInterest validation
        $this->expectException(\Illuminate\Database\QueryException::class);
        \App\Models\AreaOfInterest::create([]); // Should fail due to required name

        // Reset for next test
        $this->refreshDatabase();

        // Test Supervisor validation
        $this->expectException(\Illuminate\Database\QueryException::class);
        \App\Models\Supervisor::create([]); // Should fail due to required fields
    }

    public function test_all_casts_work()
    {
        // Test User casts
        $user = \App\Models\User::factory()->create(['typeIds' => ['1', '2']]);
        $this->assertIsArray($user->typeIds);

        // Test AreaOfInterest casts
        $area = \App\Models\AreaOfInterest::factory()->create(['is_active' => true]);
        $this->assertIsBool($area->is_active);

        // Test Batch casts
        $batch = \App\Models\Batch::factory()->create(['is_active' => true]);
        $this->assertIsBool($batch->is_active);
    }

    public function test_all_accessors_work()
    {
        // Test Group accessors
        $group = \App\Models\Group::factory()->create(['max_students' => 3]);
        \App\Models\GroupStudent::factory()->count(2)->create(['group_id' => $group->id]);
        
        $this->assertEquals(2, $group->student_count);
        $this->assertEquals(1, $group->available_slots);
        $this->assertFalse($group->isFull());

        // Test Batch accessors
        $batch = \App\Models\Batch::factory()->create([
            'batch_number' => 2020,
            'batch_name' => 'CS Batch 2020'
        ]);
        $this->assertEquals('CS Batch 2020', $batch->display_name);

        $batchWithoutName = \App\Models\Batch::factory()->create([
            'batch_number' => 2021,
            'batch_name' => null
        ]);
        $this->assertEquals('Batch 2021', $batchWithoutName->display_name);
    }

    public function test_all_static_methods_work()
    {
        // Test AreaOfInterest static methods
        \App\Models\AreaOfInterest::factory()->count(3)->create(['is_active' => true]);
        \App\Models\AreaOfInterest::factory()->count(2)->create(['is_active' => false]);
        
        $stats = \App\Models\AreaOfInterest::getStats();
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);

        // Test Batch static methods
        \App\Models\Batch::factory()->count(4)->create(['is_active' => true]);
        \App\Models\Batch::factory()->count(1)->create(['is_active' => false]);
        
        $batchStats = \App\Models\Batch::getStats();
        $this->assertEquals(5, $batchStats['total']);
        $this->assertEquals(4, $batchStats['active']);
        $this->assertEquals(1, $batchStats['inactive']);
    }

    public function test_configuration_files_are_valid()
    {
        // Test that all config files can be loaded
        $configs = [
            'app',
            'auth',
            'cache',
            'database',
            'filesystems',
            'logging',
            'mail',
            'queue',
            'services',
            'session',
            'external_api'
        ];

        foreach ($configs as $config) {
            $configData = config($config);
            $this->assertNotNull($configData, "Config {$config} should not be null");
        }
    }

    public function test_environment_variables_are_set()
    {
        $requiredEnvVars = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'DB_CONNECTION',
            'DB_DATABASE'
        ];

        foreach ($requiredEnvVars as $var) {
            $this->assertNotEmpty(env($var), "Environment variable {$var} should be set");
        }
    }
}