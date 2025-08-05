<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create basic permissions and roles
    $permissions = [
        'view-dashboard',
        'manage-products',
        'manage-users',
        'view-reports',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permission::all());

    $userRole = Role::create(['name' => 'user']);
    $userRole->givePermissionTo(['view-dashboard']);
});

test('new users are assigned default user role and redirected to home', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->post('/register', $userData);

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole('user'))->toBeTrue();
    expect($user->can('view-dashboard'))->toBeTrue();
    expect($user->can('manage-products'))->toBeFalse();

    // Verify redirect to home page for regular users
    $response->assertRedirect('/');
});

test('admin users can access dashboard with manage-products permission', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    expect($admin->can('view-dashboard'))->toBeTrue();
    expect($admin->can('manage-products'))->toBeTrue();
    expect($admin->can('manage-users'))->toBeTrue();
    expect($admin->can('view-reports'))->toBeTrue();
});

test('regular users cannot access admin features', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    expect($user->can('view-dashboard'))->toBeTrue();
    expect($user->can('manage-products'))->toBeFalse();
    expect($user->can('manage-users'))->toBeFalse();
    expect($user->can('view-reports'))->toBeFalse();
});

test('admin dashboard requires admin role', function () {
    $user = User::factory()->create();
    // Don't assign any role - user should not have admin access

    $this->actingAs($user);

    $response = $this->get('/admin');

    // Should be forbidden due to missing admin role
    $response->assertStatus(403);
});

test('admin dashboard allows access with admin role', function () {
    $user = User::factory()->create();
    $user->assignRole('admin'); // Admin role required for admin dashboard access

    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertStatus(200);
});

test('regular users cannot access admin dashboard even with permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('user'); // Regular user role - should not access admin dashboard

    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertStatus(403);
});

test('roles and permissions are shared with frontend', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $response = $this->get('/admin');

    $response->assertInertia(fn ($page) =>
        $page->has('auth.user')
             ->has('auth.roles')
             ->has('auth.permissions')
             ->where('auth.roles', ['admin'])
    );
});

test('role system works with admin and user roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');

    // Test admin role
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('user'))->toBeFalse();
    expect($admin->hasAnyRole(['admin', 'user']))->toBeTrue();

    // Test user role
    expect($regularUser->hasRole('user'))->toBeTrue();
    expect($regularUser->hasRole('admin'))->toBeFalse();
    expect($regularUser->hasAnyRole(['admin', 'user']))->toBeTrue();
});
