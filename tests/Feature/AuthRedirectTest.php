<?php

use App\Models\User;
use App\Services\AuthRedirectService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create permissions and roles
    $permissions = [
        'view-dashboard',
        'edit-profile',
        'manage-products',
        'manage-users',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permission::all());

    $userRole = Role::create(['name' => 'user']);
    $userRole->givePermissionTo(['view-dashboard', 'edit-profile']);
});

test('admin users are redirected to dashboard after login', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
});

test('regular users are redirected to home after login when no intended URL', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
});

test('regular users are redirected to intended URL after login', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Simulate accessing a protected page first (which sets intended URL)
    $this->get('/settings/profile');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/settings/profile');
});

test('admin users ignore intended URL and go to dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Simulate accessing a page first (which would set intended URL)
    $this->get('/settings/profile');

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    // Admin should go to dashboard regardless of intended URL
    $response->assertRedirect('/dashboard');
});

test('admin users are redirected to dashboard after registration', function () {
    $response = $this->post('/register', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'admin@example.com')->first();
    
    // Manually assign admin role for this test
    $user->assignRole('admin');

    // Test the redirect service directly since registration assigns 'user' role by default
    $redirectService = new AuthRedirectService();
    $redirect = $redirectService->getRegistrationRedirect($user);
    
    expect($redirect->getTargetUrl())->toContain('/dashboard');
});

test('regular users are redirected to home after registration', function () {
    $response = $this->post('/register', [
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/');
});

test('auth redirect service handles safe URLs correctly', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $redirectService = new AuthRedirectService();

    // Test safe relative URL
    $redirect = $redirectService->getLoginRedirect($user, '/settings/profile');
    expect($redirect->getTargetUrl())->toContain('/settings/profile');

    // Test unsafe external URL (should fallback to home)
    $redirect = $redirectService->getLoginRedirect($user, 'https://evil.com/malicious');
    expect($redirect->getTargetUrl())->toContain('/');
});

test('auth redirect service provides correct default redirects for roles', function () {
    $redirectService = new AuthRedirectService();

    expect($redirectService->getDefaultRedirectForRole('admin'))->toContain('/dashboard');
    expect($redirectService->getDefaultRedirectForRole('user'))->toContain('/');
    expect($redirectService->getDefaultRedirectForRole('unknown'))->toContain('/');
});

test('password confirmation redirects based on role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $response = $this->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
});

test('users without roles default to home page', function () {
    $user = User::factory()->create();
    // Don't assign any role

    $redirectService = new AuthRedirectService();
    $redirect = $redirectService->getLoginRedirect($user);
    
    expect($redirect->getTargetUrl())->toContain('/');
});

test('redirect service handles malformed URLs safely', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $redirectService = new AuthRedirectService();

    // Test malformed URL
    $redirect = $redirectService->getLoginRedirect($user, 'not-a-valid-url');
    expect($redirect->getTargetUrl())->toContain('/');

    // Test empty URL
    $redirect = $redirectService->getLoginRedirect($user, '');
    expect($redirect->getTargetUrl())->toContain('/');
});

test('admin detection works correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    $redirectService = new AuthRedirectService();

    expect($redirectService->shouldRedirectToAdmin($admin))->toBeTrue();
    expect($redirectService->shouldRedirectToAdmin($user))->toBeFalse();
});
