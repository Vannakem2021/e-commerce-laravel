<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin role and user with permissions
    $adminRole = Role::create(['name' => 'admin']);
    $adminRole->givePermissionTo(Permission::create(['name' => 'manage-products']));

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);

    // Create a test product
    $this->product = Product::factory()->published()->create();
});

test('admin can view product variants', function () {
    // Create some test variants
    ProductVariant::factory()->count(3)->create(['product_id' => $this->product->id]);
    
    $response = $this->actingAs($this->admin)
        ->get(route('admin.variants.index', $this->product));
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('admin/variants/Index')
            ->has('variants.data', 3)
            ->where('product.id', $this->product->id)
    );
});

test('admin can create variant', function () {
    $variantData = [
        'sku' => 'TEST-VAR-001',
        'name' => 'Red Large',
        'price' => 29.99,
        'stock_quantity' => 50,
        'stock_status' => 'in_stock',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)
        ->post(route('admin.variants.store', $this->product), $variantData);

    $response->assertRedirect();

    $this->assertDatabaseHas('product_variants', [
        'product_id' => $this->product->id,
        'sku' => 'TEST-VAR-001',
        'name' => 'Red Large',
        'price' => 2999, // Price stored in cents
        'stock_quantity' => 50,
        'stock_status' => 'in_stock',
        'is_active' => true,
    ]);
});

test('admin can update variant', function () {
    $variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'sku' => 'ORIGINAL-SKU',
        'price' => 1999,
        'stock_quantity' => 10,
    ]);
    
    $updateData = [
        'sku' => 'UPDATED-SKU',
        'name' => 'Updated Variant',
        'price' => 39.99,
        'stock_quantity' => 25,
        'stock_status' => 'in_stock',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->admin)
        ->patch(route('admin.variants.update', [$this->product, $variant]), $updateData);

    $response->assertRedirect();

    $variant->refresh();
    expect($variant->sku)->toBe('UPDATED-SKU');
    expect($variant->name)->toBe('Updated Variant');
    expect($variant->price)->toBe(3999); // Price in cents
    expect($variant->stock_quantity)->toBe(25);
});

test('admin can delete variant', function () {
    $variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
    
    $response = $this->actingAs($this->admin)
        ->delete(route('admin.variants.destroy', [$this->product, $variant]));
    
    $response->assertRedirect();
    $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
});



test('variant sku must be unique', function () {
    ProductVariant::factory()->create(['sku' => 'EXISTING-SKU']);
    
    $variantData = [
        'sku' => 'EXISTING-SKU',
        'price' => 29.99,
        'stock_quantity' => 10,
        'stock_status' => 'in_stock',
        'is_active' => true,
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.variants.store', $this->product), $variantData);
    
    $response->assertSessionHasErrors(['sku']);
});

test('variant stock status validation works', function () {
    $variantData = [
        'sku' => 'TEST-STOCK',
        'price' => 29.99,
        'stock_quantity' => 0,
        'stock_status' => 'in_stock', // Invalid: can't be in stock with 0 quantity
        'is_active' => true,
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('admin.variants.store', $this->product), $variantData);
    
    $response->assertSessionHasErrors(['stock_status']);
});

test('variant belongs to correct product', function () {
    $otherProduct = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $otherProduct->id]);
    
    // Try to access variant through wrong product
    $response = $this->actingAs($this->admin)
        ->get(route('admin.variants.edit', [$this->product, $variant]));
    
    $response->assertStatus(404);
});

test('regular user cannot access variant management', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get(route('admin.variants.index', $this->product));
    
    $response->assertStatus(403);
});
