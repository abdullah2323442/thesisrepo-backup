<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_displays_correctly_without_group()
    {
        $user = User::factory()->create([
            'login_type' => 'student',
            'roll' => '2020123456'
        ]);

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Student Dashboard');
        $response->assertSee('You are not assigned to any group yet.');
    }

    public function test_student_dashboard_displays_group_information()
    {
        // Create a student user
        $user = User::factory()->create([
            'login_type' => 'student',
            'roll' => '2020123456'
        ]);

        // Create an advisor
        $advisor = User::factory()->create([
            'name' => 'Dr. John Advisor'
        ]);

        // Create an area of interest
        $areaOfInterest = AreaOfInterest::create([
            'name' => 'Machine Learning',
            'description' => 'Study of algorithms that improve automatically through experience',
            'is_active' => true
        ]);

        // Create a supervisor
        $supervisor = Supervisor::create([
            'api_id' => 123,
            'fullname' => 'Dr. Jane Supervisor',
            'gender' => 'Female',
            'email' => 'supervisor@example.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering',
            'is_active' => true,
            'thesis_limit' => 5,
        ]);

        // Create a group
        $group = Group::create([
            'name' => 'Group 1',
            'batch_number' => 2020,
            'advisor_id' => $advisor->id,
            'max_students' => 3,
            'area_of_interest_id' => $areaOfInterest->id,
            'supervisor_id' => $supervisor->id
        ]);

        // Add student to group
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => $user->roll,
            'student_name' => $user->name,
            'student_email' => $user->email
        ]);

        // Add another student to the group
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2020123457',
            'student_name' => 'Jane Doe',
            'student_email' => 'jane@example.com'
        ]);

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Student Dashboard');
        $response->assertSee('Group 1');
        $response->assertSee('Machine Learning');
        $response->assertSee('Dr. Jane Supervisor');
        $response->assertSee('Associate Professor');
        $response->assertSee('Jane Doe');
        $response->assertSee('(You)');
    }
}