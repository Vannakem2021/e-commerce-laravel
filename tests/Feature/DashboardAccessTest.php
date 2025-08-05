<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_users_can_access_admin_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/Dashboard'));
    }

    public function test_regular_users_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/admin');

        // Should get 403 Forbidden or redirect (depending on middleware implementation)
        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(),
            'Regular users should not be able to access admin dashboard'
        );
    }

    public function test_users_without_roles_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();
        // Don't assign any role

        $response = $this->actingAs($user)->get('/admin');

        // Should get 403 Forbidden or redirect
        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(),
            'Users without roles should not be able to access admin dashboard'
        );
    }

    public function test_admin_login_redirects_to_admin_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
    }

    public function test_regular_user_login_redirects_to_home()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
    }
}
