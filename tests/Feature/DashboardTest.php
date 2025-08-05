<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create roles for each test
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
});

test('guests are redirected to the login page', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});

test('admin users can visit the admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertStatus(200);
});

test('regular users cannot visit the admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertStatus(403);
});