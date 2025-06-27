<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view login page
     */
    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test inactive user cannot login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test user is redirected based on role after login
     */
    public function test_user_redirected_based_on_role(): void
    {
        // Test admin redirect
        $admin = User::factory()->create([
            'is_active' => true
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect('/admin');

        // Test dosen redirect
        $dosen = User::factory()->create([
            'is_active' => true
        ]);

        $response = $this->actingAs($dosen)->get('/dashboard');
        $response->assertRedirect('/dosen/dashboard');

        // Test mahasiswa redirect
        $mahasiswa = User::factory()->create([
            'is_active' => true
        ]);

        $response = $this->actingAs($mahasiswa)->get('/dashboard');
        $response->assertRedirect('/mahasiswa/dashboard');
    }

    /**
     * Test user can logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test role middleware protection
     */
    public function test_role_middleware_protection(): void
    {
        $mahasiswa = User::factory()->create(['role_id' => 3]);
        $dosen = User::factory()->create(['role_id' => 2]);

        // Mahasiswa cannot access dosen routes
        $response = $this->actingAs($mahasiswa)->get('/dosen/dashboard');
        $response->assertStatus(403);

        // Dosen cannot access mahasiswa routes
        $response = $this->actingAs($dosen)->get('/mahasiswa/dashboard');
        $response->assertStatus(403);

        // Non-admin cannot access admin routes
        $response = $this->actingAs($mahasiswa)->get('/admin');
        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access protected routes
     */
    public function test_guest_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/dosen/dashboard',
            '/mahasiswa/dashboard',
            '/admin'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test user registration
     */
    public function test_user_can_register(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 3,
            'nim_nip' => '123456789'
        ];

        $response = $this->post('/register', $userData);
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => 3,
            'nim_nip' => '123456789'
        ]);
    }
}
