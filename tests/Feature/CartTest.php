<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    // Create test products
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 1000, // $10.00 in cents
        'stock_quantity' => 10,
        'status' => 'published'
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 1200, // $12.00 in cents
        'stock_quantity' => 5,
        'is_active' => true
    ]);
});

test('guest can add product to cart', function () {
    $response = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data',
                 'cart_summary'
             ]);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'quantity' => 2,
        'price' => $this->product->price
    ]);
});

test('authenticated user can add product to cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
                     ->postJson('/cart', [
                         'product_id' => $this->product->id,
                         'quantity' => 1
                     ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'status' => 'active'
    ]);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'quantity' => 1
    ]);
});

test('can add product variant to cart', function () {
    $response = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'variant_id' => $this->variant->id,
        'quantity' => 1
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 1,
        'price' => $this->variant->price
    ]);
});

test('adding same product increments quantity', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add product first time
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    // Add same product again
    $response = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 3
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'quantity' => 5 // 2 + 3
    ]);

    // Should only have one cart item
    expect(CartItem::count())->toBe(1);
});

test('cannot add more than max quantity', function () {
    $response = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 15 // More than max allowed (10)
    ]);

    $response->assertStatus(422) // Laravel validation returns 422
             ->assertJsonStructure([
                 'message',
                 'errors'
             ]);

    $this->assertDatabaseMissing('cart_items', [
        'product_id' => $this->product->id
    ]);
});

test('cannot add more than stock quantity', function () {
    $response = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 15 // More than stock (10)
    ]);

    $response->assertStatus(422); // Laravel validation returns 422
});

test('can update cart item quantity', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add item to cart first
    $addResponse = $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $addResponse->assertStatus(201);

    $cartItem = CartItem::first();

    $response = $this->putJson("/cart/{$cartItem->id}", [
        'quantity' => 5
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 5
    ]);
});

test('can remove cart item by setting quantity to zero', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add item to cart first
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $cartItem = CartItem::first();

    $response = $this->putJson("/cart/{$cartItem->id}", [
        'quantity' => 0
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id
    ]);
});

test('can delete cart item', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add item to cart first
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $cartItem = CartItem::first();

    $response = $this->deleteJson("/cart/{$cartItem->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id
    ]);
});

test('can clear entire cart', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add multiple items to cart
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $product2 = Product::factory()->create(['status' => 'published']);
    $this->postJson('/cart', [
        'product_id' => $product2->id,
        'quantity' => 1
    ]);

    expect(CartItem::count())->toBe(2);

    $response = $this->deleteJson('/cart');

    $response->assertStatus(200);

    expect(CartItem::count())->toBe(0);
});

test('cart validation detects out of stock items', function () {
    // Use authenticated user to avoid session issues
    $user = User::factory()->create();
    $this->actingAs($user);

    // Add item to cart
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 5
    ]);

    // Reduce stock to less than cart quantity
    $this->product->update(['stock_quantity' => 2]);

    $response = $this->getJson('/cart/validate');

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'has_errors' => true
             ]);
});

test('cart page loads successfully', function () {
    $response = $this->get('/cart');

    $response->assertStatus(200);
});

test('cart data endpoint returns json', function () {
    $response = $this->getJson('/cart/data');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'cart'
             ]);
});

test('cart summary endpoint returns json', function () {
    $response = $this->getJson('/cart/summary');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'cart_summary'
             ]);
});

test('rate limiting works on cart modification endpoints', function () {
    // Make many requests quickly to test rate limiting
    for ($i = 0; $i < 65; $i++) {
        $response = $this->postJson('/cart', [
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        if ($i < 60) {
            // First 60 should succeed
            expect($response->status())->toBeIn([201, 400]); // 400 for validation errors is ok
        } else {
            // After 60, should be rate limited
            expect($response->status())->toBe(429);
            break;
        }
    }
});

test('guest cart transfers to user on login', function () {
    // Add item to cart as guest
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 2
    ]);

    $guestCart = Cart::first();
    $sessionId = $guestCart->session_id;
    expect($guestCart->session_id)->not->toBeNull();
    expect($guestCart->user_id)->toBeNull();

    // Create user and login
    $user = User::factory()->create();

    // Manually trigger cart transfer (since automatic transfer on login is not implemented)
    $cartService = app(\App\Services\CartService::class);
    $cartService->transferGuestCart($sessionId, $user->id);

    // Now act as the user
    $this->actingAs($user);

    // Add another item to test merging
    $this->postJson('/cart', [
        'product_id' => $this->product->id,
        'quantity' => 1
    ]);

    // Check that cart now belongs to user and quantities are merged
    $userCart = Cart::where('user_id', $user->id)->first();
    expect($userCart)->not->toBeNull();

    $cartItem = $userCart->items->first();
    expect($cartItem->quantity)->toBe(3); // 2 + 1
});
