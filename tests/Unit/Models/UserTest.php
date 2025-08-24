<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_type' => 'student'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_type' => 'student'
        ]);
    }

    public function test_user_has_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $expectedFillable = [
            'name', 'email', 'password', 'api_id', 'department_id',
            'program_id', 'roll', 'status', 'department_name', 'program_name',
            'batch', 'profile_image_url', 'phone', 'login_type', 'address',
            'advisor', 'user_info_id', 'type_id', 'username', 'designation', 'salt'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_user_has_hidden_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_has_casts()
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_login_types()
    {
        $studentUser = User::factory()->create(['login_type' => 'student']);
        $teacherUser = User::factory()->create(['login_type' => 'teacher']);
        $adminUser = User::factory()->create(['login_type' => 'admin']);

        $this->assertEquals('student', $studentUser->login_type);
        $this->assertEquals('teacher', $teacherUser->login_type);
        $this->assertEquals('admin', $adminUser->login_type);
    }

    public function test_user_can_have_api_id()
    {
        $user = User::factory()->create(['api_id' => 12345]);
        
        $this->assertEquals(12345, $user->api_id);
    }

    public function test_user_can_have_type_ids_array()
    {
        $user = User::factory()->create(['type_id' => json_encode(['1', '2'])]);
        
        $this->assertEquals(['1', '2'], $user->type_ids);
        $this->assertIsArray($user->type_ids);
    }

    public function test_user_student_attributes()
    {
        $user = User::factory()->create([
            'login_type' => 'student',
            'roll' => '2020123456',
            'batch' => 2020,
            'department_name' => 'Computer Science & Engineering',
            'program_name' => 'Bachelor of Science'
        ]);

        $this->assertEquals('student', $user->login_type);
        $this->assertEquals('2020123456', $user->roll);
        $this->assertEquals(2020, $user->batch);
        $this->assertEquals('Computer Science & Engineering', $user->department_name);
        $this->assertEquals('Bachelor of Science', $user->program_name);
    }

    public function test_user_teacher_attributes()
    {
        $user = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 100,
            'type_id' => json_encode(['2'])
        ]);

        $this->assertEquals('teacher', $user->login_type);
        $this->assertEquals(100, $user->api_id);
        $this->assertEquals(['2'], $user->type_ids);
    }

    public function test_user_role_methods()
    {
        $adminUser = User::factory()->create(['type_id' => json_encode(['1'])]);
        $teacherUser = User::factory()->create(['type_id' => json_encode(['2'])]);
        $studentUser = User::factory()->create(['login_type' => 'student']);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($adminUser->isStudent());

        $this->assertTrue($teacherUser->isTeacher());
        $this->assertFalse($teacherUser->isAdmin());

        $this->assertTrue($studentUser->isStudent());
        $this->assertFalse($studentUser->isTeacher());
    }

    public function test_user_profile_image_attribute()
    {
        $userWithImage = User::factory()->create([
            'name' => 'John Doe',
            'profile_image_url' => 'https://example.com/image.jpg'
        ]);

        $userWithoutImage = User::factory()->create([
            'name' => 'Jane Doe',
            'profile_image_url' => null
        ]);

        $this->assertEquals('https://example.com/image.jpg', $userWithImage->profile_image);
        $this->assertStringContains('ui-avatars.com', $userWithoutImage->profile_image);
    }
}