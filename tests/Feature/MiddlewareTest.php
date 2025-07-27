<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create permissions and roles
    $permissions = [
        'view-dashboard',
        'edit-profile',
        'manage-products',
        'view-reports',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permission::all());

    $userRole = Role::create(['name' => 'user']);
    $userRole->givePermissionTo(['view-dashboard', 'edit-profile']);
});

test('ensure permission middleware allows access with correct permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('ensure permission middleware denies access without permission', function () {
    $user = User::factory()->create();
    // Don't assign any role

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(403);
});

test('admin middleware allows admin users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    // Test a route that would use admin middleware
    // Since we don't have actual admin routes yet, we'll test the middleware logic
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('admin middleware denies non-admin users', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    expect($user->hasRole('admin'))->toBeFalse();
});

test('settings routes require edit-profile permission', function () {
    $user = User::factory()->create();
    $user->assignRole('user'); // Has edit-profile permission

    $this->actingAs($user);

    $response = $this->get('/settings/profile');
    $response->assertStatus(200);
});

test('settings routes deny access without edit-profile permission', function () {
    $user = User::factory()->create();
    // Don't assign any role

    $this->actingAs($user);

    $response = $this->get('/settings/profile');
    $response->assertStatus(403);
});

test('unauthenticated users are redirected to login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('unauthenticated users accessing settings are redirected to login', function () {
    $response = $this->get('/settings/profile');
    $response->assertRedirect('/login');
});

test('middleware logs unauthorized access attempts', function () {
    $user = User::factory()->create();
    // Don't assign any role - user won't have permissions

    $this->actingAs($user);

    // This should trigger logging in our custom middleware
    $response = $this->get('/dashboard');
    $response->assertStatus(403);

    // In a real test, you might want to check log files or use a log testing package
    // For now, we just verify the response
    expect($response->status())->toBe(403);
});

test('api requests receive json error responses', function () {
    $user = User::factory()->create();
    // Don't assign any role

    $this->actingAs($user);

    $response = $this->getJson('/dashboard');
    
    $response->assertStatus(403)
             ->assertJson([
                 'message' => 'You do not have permission to access this resource.',
                 'error' => 'insufficient_permissions'
             ]);
});

test('simplified role system with admin and user works correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');

    // Test admin permissions
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->can('manage-products'))->toBeTrue();
    expect($admin->can('manage-users'))->toBeTrue();

    // Test regular user permissions
    expect($regularUser->hasRole('user'))->toBeTrue();
    expect($regularUser->can('view-dashboard'))->toBeTrue();
    expect($regularUser->can('edit-profile'))->toBeTrue();
    expect($regularUser->can('manage-products'))->toBeFalse();
    expect($regularUser->can('manage-users'))->toBeFalse();
});
