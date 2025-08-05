<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions and roles
        $permissions = [
            'view-dashboard',
            'edit-profile',
            'view-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo(['view-dashboard', 'edit-profile', 'view-settings']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }

    public function test_profile_route_redirects_to_settings_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect('/settings/profile');
    }

    public function test_settings_profile_page_displays_user_information()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/settings/profile');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/Profile')
                ->has('user')
                ->where('user.name', 'John Doe')
                ->where('user.email', 'john@example.com')
                ->has('user.created_at')
                ->has('user.updated_at')
        );
    }

    public function test_profile_update_works_correctly()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertRedirect('/settings/profile');
        $response->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_password_update_works_correctly()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password'),
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/settings/password');
        $response->assertSessionHas('status', 'password-updated');
    }

    public function test_unauthenticated_users_cannot_access_profile()
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');

        $response = $this->get('/settings/profile');
        $response->assertRedirect('/login');
    }

    public function test_all_authenticated_users_can_access_profile()
    {
        $user = User::factory()->create();
        // Don't assign any role or permission - should still be able to access profile

        $response = $this->actingAs($user)->get('/profile');
        $response->assertRedirect('/settings/profile');

        $response = $this->actingAs($user)->get('/settings/profile');
        $response->assertStatus(200);
    }
}
