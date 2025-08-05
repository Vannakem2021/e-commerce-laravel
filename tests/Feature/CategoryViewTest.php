<?php

use App\Models\Category;
use App\Models\User;

test('admin can view category show page', function () {
    // Create an admin user
    $user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);

    // Create a test category
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'description' => 'This is a test category',
        'is_active' => true,
    ]);

    // Visit the category show page
    $response = $this->get(route('admin.categories.show', $category));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->component('admin/categories/Show')
             ->has('category')
             ->where('category.name', 'Test Category')
             ->where('category.slug', 'test-category')
    );
});

test('category show page displays parent relationship', function () {
    // Create an admin user
    $user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);

    // Create parent and child categories
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    $child = Category::factory()->create([
        'name' => 'Child Category',
        'parent_id' => $parent->id,
    ]);

    $response = $this->get(route('admin.categories.show', $child));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->has('category.parent')
             ->where('category.parent.name', 'Parent Category')
    );
});
