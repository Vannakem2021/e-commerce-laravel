<?php

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;

beforeEach(function () {
    $this->cartService = app(CartService::class);

    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 1000,
        'stock_quantity' => 10,
        'status' => 'published'
    ]);

    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 1200,
        'stock_quantity' => 5,
        'is_active' => true
    ]);
});

test('can create cart for guest user', function () {
    $cart = $this->cartService->getOrCreateCart();

    expect($cart)->toBeInstanceOf(Cart::class);
    expect($cart->session_id)->not->toBeNull();
    expect($cart->user_id)->toBeNull();
    expect($cart->status)->toBe('active');
});

test('can create cart for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cart = $this->cartService->getOrCreateCart();

    expect($cart)->toBeInstanceOf(Cart::class);
    expect($cart->user_id)->toBe($user->id);
    expect($cart->session_id)->toBeNull();
    expect($cart->status)->toBe('active');
});

test('returns existing cart for same user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cart1 = $this->cartService->getOrCreateCart();
    $cart2 = $this->cartService->getOrCreateCart();

    expect($cart1->id)->toBe($cart2->id);
});

test('can add product to cart', function () {
    $cartItem = $this->cartService->addToCart($this->product, 2);

    expect($cartItem)->toBeInstanceOf(CartItem::class);
    expect($cartItem->product_id)->toBe($this->product->id);
    expect($cartItem->quantity)->toBe(2);
    expect($cartItem->price)->toBe($this->product->price);
});

test('can add product variant to cart', function () {
    $cartItem = $this->cartService->addToCart($this->product, 1, $this->variant);

    expect($cartItem->product_id)->toBe($this->product->id);
    expect($cartItem->product_variant_id)->toBe($this->variant->id);
    expect($cartItem->price)->toBe($this->variant->price);
});

test('adding same product increments quantity', function () {
    $cartItem1 = $this->cartService->addToCart($this->product, 2);
    $cartItem2 = $this->cartService->addToCart($this->product, 3);

    expect($cartItem1->id)->toBe($cartItem2->id);
    expect($cartItem2->quantity)->toBe(5);
});

test('throws exception when exceeding max quantity', function () {
    expect(fn() => $this->cartService->addToCart($this->product, 15))
        ->toThrow(CartException::class);
});

test('throws exception when exceeding max cart items', function () {
    // Create 50 different products and add to cart
    for ($i = 0; $i < 50; $i++) {
        $product = Product::factory()->create(['status' => 'published']);
        $this->cartService->addToCart($product, 1);
    }

    // Adding 51st item should throw exception
    $extraProduct = Product::factory()->create(['status' => 'published']);
    expect(fn() => $this->cartService->addToCart($extraProduct, 1))
        ->toThrow(CartException::class);
});

test('can update cart item quantity', function () {
    $cartItem = $this->cartService->addToCart($this->product, 2);

    $result = $this->cartService->updateQuantity($cartItem, 5);

    expect($result)->toBeTrue();
    expect($cartItem->fresh()->quantity)->toBe(5);
});

test('can remove cart item by setting quantity to zero', function () {
    $cartItem = $this->cartService->addToCart($this->product, 2);

    $result = $this->cartService->updateQuantity($cartItem, 0);

    expect($result)->toBeTrue();
    expect(CartItem::find($cartItem->id))->toBeNull();
});

test('can remove cart item directly', function () {
    $cartItem = $this->cartService->addToCart($this->product, 2);

    $result = $this->cartService->removeFromCart($cartItem);

    expect($result)->toBeTrue();
    expect(CartItem::find($cartItem->id))->toBeNull();
});

test('can clear entire cart', function () {
    $this->cartService->addToCart($this->product, 2);
    $product2 = Product::factory()->create(['status' => 'published']);
    $this->cartService->addToCart($product2, 1);

    $cart = $this->cartService->getOrCreateCart();
    expect($cart->items->count())->toBe(2);

    $this->cartService->clearCart();

    expect($cart->fresh()->items->count())->toBe(0);
});

test('validates cart items correctly', function () {
    $cartItem = $this->cartService->addToCart($this->product, 5);

    // Reduce stock below cart quantity
    $this->product->update(['stock_quantity' => 2]);

    $errors = $this->cartService->validateCart();

    expect($errors)->toHaveKey($cartItem->id);
    expect($errors[$cartItem->id])->toContain('Only 2 items available in stock');
});

test('can get cart with items', function () {
    $this->cartService->addToCart($this->product, 2);

    $cart = $this->cartService->getCartWithItems();

    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->product)->not->toBeNull();
});

test('can get cart summary', function () {
    $this->cartService->addToCart($this->product, 2);

    $summary = $this->cartService->getCartSummary();

    expect($summary)->toHaveKeys([
        'id', 'total_quantity', 'total_price',
        'formatted_total', 'items_count', 'is_empty'
    ]);
    expect($summary['total_quantity'])->toBe(2);
    expect($summary['is_empty'])->toBeFalse();
});

test('cart caching works correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // First call should create cart
    $cart1 = $this->cartService->getOrCreateCart();

    // Second call should return cached cart (same instance)
    $cart2 = $this->cartService->getOrCreateCart();

    expect($cart1)->toBe($cart2); // Same object instance
});

test('cart cache clears after modification', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $cart1 = $this->cartService->getOrCreateCart();

    // Modify cart
    $this->cartService->addToCart($this->product, 1);

    // Get cart again - should be fresh instance
    $cart2 = $this->cartService->getOrCreateCart();

    expect($cart1)->not->toBe($cart2); // Different object instances
    expect($cart1->id)->toBe($cart2->id); // But same cart ID
});
