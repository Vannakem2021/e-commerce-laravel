<?php

use App\Models\Product;
use App\Models\User;

test('cart page loads', function () {
    $response = $this->get('/cart');
    $response->assertStatus(200);
});

test('cart data endpoint works', function () {
    $response = $this->getJson('/cart/data');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'cart']);
});

test('cart summary endpoint works', function () {
    $response = $this->getJson('/cart/summary');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'cart_summary']);
});

test('can add simple product to cart', function () {
    // Create a simple product without complex relationships
    $product = Product::create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'sku' => 'TEST001',
        'price' => 1000,
        'stock_quantity' => 10,
        'status' => 'published',
        'user_id' => User::factory()->create()->id,
        'short_description' => 'Test description',
        'description' => 'Test description',
        'product_type' => 'simple',
        'stock_status' => 'in_stock',
        'track_inventory' => true,
        'requires_shipping' => true,
        'is_digital' => false,
        'is_virtual' => false,
        'is_featured' => false,
        'is_on_sale' => false,
        'sort_order' => 0,
    ]);

    $response = $this->postJson('/cart', [
        'product_id' => $product->id,
        'quantity' => 2
    ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => $product->price
    ]);
});
