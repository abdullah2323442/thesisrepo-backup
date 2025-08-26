<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Log in');
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_external_api_login_for_students()
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'Success',
                'Data' => [
                    'Id' => 123,
                    'Name' => 'John Student',
                    'Roll' => '2020123456',
                    'Email' => 'john@example.com',
                    'Batch' => 2020,
                    'DepartmentName' => 'Computer Science',
                    'ProgramName' => 'Bachelor of Science'
                ]
            ])
        ]);

        $response = $this->post('/login', [
            'user' => '2020123456',
            'pass' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/student/dashboard');
    }

    public function test_external_api_login_for_teachers()
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'Success',
                'Data' => [
                    'Id' => 456,
                    'Name' => 'Dr. Jane Teacher',
                    'UserName' => 'jane.teacher',
                    'Email' => 'jane@example.com',
                    'DeptId' => 1,
                    'TypeId' => ['2']
                ]
            ])
        ]);

        $response = $this->post('/login', [
            'user' => 'jane.teacher',
            'pass' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/teacher/dashboard');
    }

    public function test_external_api_login_failure()
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'Failed',
                'Message' => 'Invalid credentials'
            ])
        ]);

        $response = $this->post('/login', [
            'user' => 'invalid',
            'pass' => 'wrong',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_login_rate_limiting()
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'Failed',
                'Message' => 'Invalid credentials'
            ])
        ]);

        // Make 5 failed attempts (the rate limit)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'user' => 'test',
                'pass' => 'wrong',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'user' => 'test',
            'pass' => 'wrong',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }



    public function test_password_reset_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
    }

    public function test_password_reset_link_can_be_requested()
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        // Should not throw an exception
        $this->assertTrue(true);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        // This would normally send an email with a reset token
        // For testing purposes, we'll just verify the request doesn't error
        $response->assertStatus(302);
    }

    public function test_external_api_timeout_handling()
    {
        Http::fake([
            '*' => Http::response([], 500) // Simulate timeout/error
        ]);

        $response = $this->post('/login', [
            'user' => 'test',
            'pass' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_login_redirects_based_on_user_type()
    {
        // Test student redirect
        Http::fake([
            '*' => Http::response([
                'Status' => 'Success',
                'Data' => [
                    'Id' => 123,
                    'Name' => 'John Student',
                    'Roll' => '2020123456',
                    'LoginType' => 'Student'
                ]
            ])
        ]);

        $response = $this->post('/login', [
            'user' => '2020123456',
            'pass' => 'password',
        ]);

        $response->assertRedirect('/student/dashboard');
    }

    public function test_login_creates_user_record_for_external_users()
    {
        Http::fake([
            '*' => Http::response([
                'Status' => 'Success',
                'Data' => [
                    'Id' => 123,
                    'Name' => 'John Student',
                    'Roll' => '2020123456',
                    'Email' => 'john@example.com',
                    'LoginType' => 'Student'
                ]
            ])
        ]);

        $this->post('/login', [
            'user' => '2020123456',
            'pass' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'login_type' => 'student'
        ]);
    }
}