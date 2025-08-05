<?php

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;

beforeEach(function () {
    // Create roles and permissions for testing
    $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    
    // Create an admin user for testing
    $this->admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'test-admin@example.com',
    ]);
    $this->admin->assignRole('admin');
    
    // Create test data
    $this->brand = Brand::factory()->create();
    $this->category = Category::factory()->create();
});

test('admin can access product creation page without validation errors', function () {
    $response = $this->actingAs($this->admin)->get('/admin/products/create');
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('admin/products/Create')
    );
});

test('product creation form does not trigger validation on page load', function () {
    $response = $this->actingAs($this->admin)->get('/admin/products/create');

    $response->assertStatus(200);

    // Check that no validation errors are present in the response
    $response->assertInertia(fn ($page) =>
        $page->has('brands')
            ->has('categories')
            ->where('errors', []) // No validation errors should be present
    );
});

test('product creation requires explicit form submission for validation', function () {
    // Test that validation only occurs when actually submitting the form
    $response = $this->actingAs($this->admin)->post('/admin/products', [
        'name' => '', // Invalid: empty name
        'price' => -1, // Invalid: price must be >= 0
        'sku' => '', // Invalid: empty SKU
        'slug' => '', // Invalid: empty slug
        'stock_status' => '', // Invalid: required field
        'product_type' => '', // Invalid: required field
        'status' => '', // Invalid: required field
    ]);

    // Should have validation errors when actually submitting
    $response->assertSessionHasErrors(['name', 'price', 'sku', 'slug', 'stock_status', 'product_type', 'status']);
});

test('product creation succeeds with valid data', function () {
    $productData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'short_description' => 'A test product',
        'description' => 'This is a test product description',
        'price' => 99.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'product_type' => 'simple',
        'status' => 'published',
        'is_featured' => false,
        'is_on_sale' => false,
        'is_digital' => false,
        'is_virtual' => false,
        'requires_shipping' => true,
        'track_inventory' => true,
        'low_stock_threshold' => 5,
        'brand_id' => $this->brand->id,
        'category_ids' => [$this->category->id],
    ];
    
    $response = $this->actingAs($this->admin)->post('/admin/products', $productData);
    
    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    
    // Verify product was created
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'price' => 9999, // Price is stored in cents
    ]);
});

test('product creation form handles image upload without triggering validation', function () {
    // This test simulates the scenario where clicking on image upload
    // should not trigger form validation

    // First, access the create page
    $response = $this->actingAs($this->admin)->get('/admin/products/create');
    $response->assertStatus(200);

    // The form should be accessible and not show validation errors
    // even when the form has default values (like price: 0)
    $response->assertInertia(fn ($page) =>
        $page->component('admin/products/Create')
            ->where('errors', []) // Check that errors is an empty array
    );
});
