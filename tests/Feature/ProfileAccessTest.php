<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run the role permission seeder
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_admin_users_can_access_all_profile_functionality()
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Test profile access
        $response = $this->actingAs($admin)->get('/profile');
        $response->assertRedirect('/settings/profile');

        $response = $this->actingAs($admin)->get('/settings/profile');
        $response->assertStatus(200);

        // Test password page access
        $response = $this->actingAs($admin)->get('/settings/password');
        $response->assertStatus(200);

        // Test profile update
        $response = $this->actingAs($admin)->patch('/settings/profile', [
            'name' => 'Updated Admin',
            'email' => 'updated-admin@example.com',
        ]);
        $response->assertRedirect('/settings/profile');

        // Test password update
        $response = $this->actingAs($admin)->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $response->assertRedirect('/settings/password');
    }

    public function test_regular_users_can_access_all_profile_functionality()
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        $user->assignRole('user');

        // Test profile access
        $response = $this->actingAs($user)->get('/profile');
        $response->assertRedirect('/settings/profile');

        $response = $this->actingAs($user)->get('/settings/profile');
        $response->assertStatus(200);

        // Test password page access
        $response = $this->actingAs($user)->get('/settings/password');
        $response->assertStatus(200);

        // Test profile update
        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated User',
            'email' => 'updated-user@example.com',
        ]);
        $response->assertRedirect('/settings/profile');

        // Test password update
        $response = $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $response->assertRedirect('/settings/password');
    }

    public function test_users_without_roles_can_still_access_profile()
    {
        $user = User::factory()->create([
            'name' => 'No Role User',
            'email' => 'norole@example.com',
        ]);
        // Intentionally not assigning any role

        // Test profile access
        $response = $this->actingAs($user)->get('/profile');
        $response->assertRedirect('/settings/profile');

        $response = $this->actingAs($user)->get('/settings/profile');
        $response->assertStatus(200);

        // Test password page access
        $response = $this->actingAs($user)->get('/settings/password');
        $response->assertStatus(200);

        // Test profile update
        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated No Role User',
            'email' => 'updated-norole@example.com',
        ]);
        $response->assertRedirect('/settings/profile');
    }

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');

        $response = $this->get('/settings/profile');
        $response->assertRedirect('/login');

        $response = $this->get('/settings/password');
        $response->assertRedirect('/login');
    }

    public function test_profile_data_is_properly_displayed()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/settings/profile');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/Profile')
                ->has('user')
                ->where('user.name', 'Test User')
                ->where('user.email', 'test@example.com')
                ->has('user.created_at')
                ->has('user.updated_at')
        );
    }
}
