<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create roles and permissions for testing
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
});

test('admin user can access admin dashboard', function () {
    // Create an admin user for testing
    $admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'test-admin@example.com',
    ]);
    $admin->assignRole('admin');
    
    expect($admin)->not->toBeNull();
    expect($admin->hasRole('admin'))->toBeTrue();
    
    // Test that admin can access admin dashboard
    $response = $this->actingAs($admin)->get('/admin');
    
    $response->assertStatus(200);
});

test('admin user has correct permissions', function () {
    // Create an admin user for testing
    $admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'test-admin@example.com',
    ]);
    $admin->assignRole('admin');
    
    // Check some key admin permissions
    expect($admin->can('manage-users'))->toBeTrue();
    expect($admin->can('manage-products'))->toBeTrue();
    expect($admin->can('manage-categories'))->toBeTrue();
    expect($admin->can('view-reports'))->toBeTrue();
    expect($admin->can('manage-settings'))->toBeTrue();
    
    // Admin should have all permissions
    expect($admin->getAllPermissions()->count())->toBe(36);
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    
    $response = $this->actingAs($user)->get('/admin');
    
    $response->assertStatus(403);
});

test('unauthenticated user cannot access admin dashboard', function () {
    $response = $this->get('/admin');
    
    $response->assertRedirect('/login');
});
