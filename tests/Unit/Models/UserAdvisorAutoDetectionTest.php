<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAdvisorAutoDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_or_update_teacher_from_list_api_creates_new_teacher()
    {
        $apiData = [
            'id' => 17,
            'fullname' => 'Farhana Shirin Chowdhury',
            'gender' => 'Female',
            'email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering'
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->wasRecentlyCreated);
        
        $this->assertDatabaseHas('users', [
            'api_id' => 17,
            'name' => 'Farhana Shirin Chowdhury',
            'email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'login_type' => 'teacher',
            'username' => 'Farhana Shirin Chowdhury',
            'department_id' => 1,
            'user_info_id' => 17,
            'type_id' => json_encode(['2']),
            'status' => 'Active'
        ]);
    }

    public function test_create_or_update_teacher_from_list_api_updates_existing_teacher()
    {
        // Create existing teacher
        $existingUser = User::factory()->create([
            'api_id' => 17,
            'login_type' => 'teacher',
            'name' => 'Old Name',
            'email' => 'old@email.com'
        ]);

        $apiData = [
            'id' => 17,
            'fullname' => 'Farhana Shirin Chowdhury',
            'gender' => 'Female',
            'email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering'
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertFalse($user->wasRecentlyCreated);
        $this->assertEquals($existingUser->id, $user->id);
        
        // Check that data was updated
        $this->assertEquals('Farhana Shirin Chowdhury', $user->name);
        $this->assertEquals('fshirin2007@gmail.com', $user->email);
        $this->assertEquals('Associate Professor', $user->designation);
    }

    public function test_create_or_update_teacher_from_list_api_handles_minimal_data()
    {
        $apiData = [
            'id' => 18,
            'fullname' => 'Test Teacher'
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        
        $this->assertDatabaseHas('users', [
            'api_id' => 18,
            'name' => 'Test Teacher',
            'email' => '',
            'designation' => '',
            'login_type' => 'teacher',
            'username' => 'Test Teacher',
            'department_id' => 1,
            'user_info_id' => 18,
            'type_id' => json_encode(['2']),
            'status' => 'Active',
            'phone' => '',
            'address' => '',
            'salt' => 0
        ]);
    }

    public function test_create_or_update_teacher_from_list_api_generates_fallback_username()
    {
        $apiData = [
            'id' => 19
            // No fullname provided
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        
        $this->assertDatabaseHas('users', [
            'api_id' => 19,
            'username' => 'teacher_19',
            'login_type' => 'teacher'
        ]);
    }

    public function test_create_or_update_teacher_from_list_api_vs_login_api_format()
    {
        // Test that both methods create different users with different data formats
        
        // Teacher List API format
        $listApiData = [
            'id' => 17,
            'fullname' => 'Farhana Shirin Chowdhury',
            'gender' => 'Female',
            'email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering'
        ];

        // Teacher Login API format
        $loginApiData = [
            'Id' => 18,
            'UserName' => 'fshirin',
            'Name' => 'Farhana Shirin Chowdhury',
            'Phone' => '123456789',
            'Email' => 'fshirin2007@gmail.com',
            'designation' => 'Associate Professor',
            'UserInfoId' => 18,
            'DeptId' => 1,
            'TypeId' => "'2'"
        ];

        $userFromList = User::createOrUpdateTeacherFromListApi($listApiData);
        $userFromLogin = User::createOrUpdateTeacherFromApi($loginApiData);

        // Both should be created successfully
        $this->assertInstanceOf(User::class, $userFromList);
        $this->assertInstanceOf(User::class, $userFromLogin);
        
        // They should have different API IDs
        $this->assertEquals(17, $userFromList->api_id);
        $this->assertEquals(18, $userFromLogin->api_id);
        
        // List API user should have fullname as username
        $this->assertEquals('Farhana Shirin Chowdhury', $userFromList->username);
        
        // Login API user should have UserName as username
        $this->assertEquals('fshirin', $userFromLogin->username);
    }

    public function test_create_or_update_teacher_from_list_api_preserves_password()
    {
        // Create existing teacher with password
        $existingUser = User::factory()->create([
            'api_id' => 17,
            'login_type' => 'teacher',
            'password' => bcrypt('existing_password')
        ]);

        $originalPassword = $existingUser->password;

        $apiData = [
            'id' => 17,
            'fullname' => 'Updated Name',
            'email' => 'updated@email.com'
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        // Password should remain the same
        $this->assertEquals($originalPassword, $user->password);
        
        // Other data should be updated
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@email.com', $user->email);
    }

    public function test_create_or_update_teacher_from_list_api_sets_default_teacher_role()
    {
        $apiData = [
            'id' => 20,
            'fullname' => 'New Teacher'
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertEquals(['2'], $user->type_ids);
        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->isAdmin());
    }

    public function test_create_or_update_teacher_from_list_api_handles_empty_email()
    {
        $apiData = [
            'id' => 21,
            'fullname' => 'Teacher Without Email',
            'email' => null
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('', $user->email);
    }

    public function test_create_or_update_teacher_from_list_api_handles_empty_designation()
    {
        $apiData = [
            'id' => 22,
            'fullname' => 'Teacher Without Designation',
            'designation' => null
        ];

        $user = User::createOrUpdateTeacherFromListApi($apiData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('', $user->designation);
    }
}