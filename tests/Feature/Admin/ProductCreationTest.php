<?php

use App\Models\User;
use App\Models\Product;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create admin role and user with permissions
    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permission::create(['name' => 'manage-products']));
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
});

test('admin can create product with all required fields', function () {
    $productData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertRedirect(route('admin.products.index'));
    
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'price' => 2999, // Price stored in cents
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ]);
});

test('product creation fails when sku is missing', function () {
    $productData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        // 'sku' => 'TEST-001', // Missing SKU
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['sku']);
});

test('product creation fails when slug is missing', function () {
    $productData = [
        'name' => 'Test Product',
        // 'slug' => 'test-product', // Missing slug
        'sku' => 'TEST-001',
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['slug']);
});

test('product creation fails when name is missing', function () {
    $productData = [
        // 'name' => 'Test Product', // Missing name
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['name']);
});

test('product creation fails when price is zero or negative', function () {
    $productData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST-001',
        'price' => 0, // Invalid price
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['price']);
});

test('product creation fails with duplicate sku', function () {
    // Create existing product
    Product::factory()->create(['sku' => 'DUPLICATE-SKU']);
    
    $productData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'DUPLICATE-SKU', // Duplicate SKU
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['sku']);
});

test('product creation fails with duplicate slug', function () {
    // Create existing product
    Product::factory()->create(['slug' => 'duplicate-slug']);
    
    $productData = [
        'name' => 'Test Product',
        'slug' => 'duplicate-slug', // Duplicate slug
        'sku' => 'TEST-001',
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'status' => 'draft',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.products.store'), $productData);
    
    $response->assertSessionHasErrors(['slug']);
});
