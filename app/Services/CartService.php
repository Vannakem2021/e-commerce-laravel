<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Maximum number of items allowed in cart
     */
    private const MAX_CART_ITEMS = 50;

    /**
     * Maximum quantity per cart item
     */
    private const MAX_ITEM_QUANTITY = 10;

    /**
     * Cache for current cart to avoid repeated queries
     */
    private ?Cart $cachedCart = null;
    /**
     * Get or create a cart for the current user/session.
     */
    public function getOrCreateCart(): Cart
    {
        // Return cached cart if available and still valid
        if ($this->cachedCart && $this->isCartValid($this->cachedCart)) {
            return $this->cachedCart;
        }

        if (Auth::check()) {
            // For authenticated users, find by user_id
            $cart = Cart::where('user_id', Auth::id())
                ->active()
                ->notExpired()
                ->first();

            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'status' => 'active',
                    'expires_at' => now()->addDays(30), // 30 days for authenticated users
                ]);
            }
        } else {
            // For guest users, find by session_id
            $sessionId = Session::getId();
            $cart = Cart::where('session_id', $sessionId)
                ->active()
                ->notExpired()
                ->first();

            if (!$cart) {
                $cart = Cart::create([
                    'session_id' => $sessionId,
                    'status' => 'active',
                    'expires_at' => now()->addDays(7), // 7 days for guest users
                ]);
            }
        }

        // Cache the cart
        $this->cachedCart = $cart;
        return $cart;
    }

    /**
     * Check if cached cart is still valid for current user/session.
     */
    private function isCartValid(Cart $cart): bool
    {
        if (Auth::check()) {
            return $cart->user_id === Auth::id() && $cart->status === 'active';
        } else {
            return $cart->session_id === Session::getId() && $cart->status === 'active';
        }
    }

    /**
     * Clear the cached cart (call when cart is modified).
     */
    private function clearCartCache(): void
    {
        $this->cachedCart = null;
    }

    /**
     * Add a product to the cart.
     */
    public function addToCart(
        Product $product,
        int $quantity = 1,
        ?ProductVariant $variant = null
    ): CartItem {
        return DB::transaction(function () use ($product, $quantity, $variant) {
            // Validate quantity limits
            if ($quantity > self::MAX_ITEM_QUANTITY) {
                throw CartException::limitExceeded("Maximum quantity per item is " . self::MAX_ITEM_QUANTITY, [
                    'max_quantity' => self::MAX_ITEM_QUANTITY,
                    'requested_quantity' => $quantity
                ]);
            }

            $cart = $this->getOrCreateCart();

            // Lock the cart for update to prevent race conditions
            $cart = Cart::where('id', $cart->id)->lockForUpdate()->first();

            // Check if item already exists in cart
            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                // Validate total quantity after increment
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > self::MAX_ITEM_QUANTITY) {
                    throw CartException::limitExceeded("Maximum quantity per item is " . self::MAX_ITEM_QUANTITY, [
                        'max_quantity' => self::MAX_ITEM_QUANTITY,
                        'current_quantity' => $existingItem->quantity,
                        'requested_quantity' => $quantity,
                        'total_quantity' => $newQuantity
                    ]);
                }

                // Update quantity if item exists
                $existingItem->incrementQuantity($quantity);
                return $existingItem;
            }

            // Check cart item limit for new items
            if ($cart->items->count() >= self::MAX_CART_ITEMS) {
                throw CartException::limitExceeded("Maximum cart items exceeded (" . self::MAX_CART_ITEMS . ")", [
                    'max_items' => self::MAX_CART_ITEMS,
                    'current_items' => $cart->items->count()
                ]);
            }

            // Create new cart item
            $price = $variant ? $variant->price : $product->price;

            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price' => $price,
                'product_snapshot' => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'image' => $product->primaryImage?->image_path,
                ],
            ]);

            // Clear cache since cart was modified
            $this->clearCartCache();

            return $cartItem;
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(CartItem $cartItem, int $quantity): bool
    {
        return DB::transaction(function () use ($cartItem, $quantity) {
            // Validate quantity limits (allow 0 for deletion)
            if ($quantity > self::MAX_ITEM_QUANTITY) {
                throw CartException::limitExceeded("Maximum quantity per item is " . self::MAX_ITEM_QUANTITY, [
                    'max_quantity' => self::MAX_ITEM_QUANTITY,
                    'requested_quantity' => $quantity
                ]);
            }

            // Lock the cart item for update to prevent race conditions
            $lockedItem = CartItem::where('id', $cartItem->id)->lockForUpdate()->first();

            if (!$lockedItem) {
                return false;
            }

            $result = $lockedItem->updateQuantity($quantity);

            // Clear cache since cart was modified
            $this->clearCartCache();

            return $result;
        });
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(CartItem $cartItem): bool
    {
        $result = $cartItem->delete();

        // Clear cache since cart was modified
        $this->clearCartCache();

        return $result;
    }

    /**
     * Clear the entire cart.
     */
    public function clearCart(?Cart $cart = null): void
    {
        $cart = $cart ?: $this->getOrCreateCart();
        $cart->clear();

        // Clear cache since cart was modified
        $this->clearCartCache();
    }

    /**
     * Get cart with items for display.
     */
    public function getCartWithItems(): Cart
    {
        $cart = $this->getOrCreateCart();
        $cart->load([
            'items.product.primaryImage',
            'items.product.brand',
            'items.variant'
        ]);

        return $cart;
    }

    /**
     * Transfer guest cart to authenticated user.
     */
    public function transferGuestCart(string $sessionId, int $userId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)
            ->active()
            ->notExpired()
            ->first();

        if (!$guestCart || $guestCart->isEmpty()) {
            return;
        }

        // Get or create user cart
        $userCart = Cart::where('user_id', $userId)
            ->active()
            ->notExpired()
            ->first();

        if (!$userCart) {
            // Transfer guest cart to user
            $guestCart->update([
                'user_id' => $userId,
                'session_id' => null,
                'expires_at' => now()->addDays(30),
            ]);
        } else {
            // Merge guest cart items into user cart
            foreach ($guestCart->items as $guestItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->incrementQuantity($guestItem->quantity);
                } else {
                    $guestItem->update(['cart_id' => $userCart->id]);
                }
            }

            // Delete guest cart
            $guestCart->delete();
        }
    }

    /**
     * Get cart summary for API responses.
     */
    public function getCartSummary(): array
    {
        $cart = $this->getCartWithItems();

        return [
            'id' => $cart->id,
            'total_quantity' => $cart->total_quantity,
            'total_price' => $cart->total_price,
            'formatted_total' => $cart->formatted_total,
            'items_count' => $cart->items->count(),
            'is_empty' => $cart->isEmpty(),
        ];
    }

    /**
     * Validate cart item availability.
     */
    public function validateCartItem(CartItem $cartItem): array
    {
        $product = $cartItem->product;
        $variant = $cartItem->variant;
        $errors = [];

        // Check if product still exists and is published
        if (!$product || $product->status !== 'published') {
            $errors[] = 'Product is no longer available';
        }

        // Check stock availability
        $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($cartItem->quantity > $availableStock) {
            $errors[] = "Only {$availableStock} items available in stock";
        }

        // Check if variant still exists and is active
        if ($cartItem->product_variant_id && (!$variant || !$variant->is_active)) {
            $errors[] = 'Selected variant is no longer available';
        }

        return $errors;
    }

    /**
     * Validate entire cart.
     */
    public function validateCart(?Cart $cart = null): array
    {
        $cart = $cart ?: $this->getCartWithItems();

        // Eager load all required relationships at once to prevent N+1 queries
        $cart->load(['items.product', 'items.variant']);

        $errors = [];

        foreach ($cart->items as $item) {
            $itemErrors = $this->validateCartItem($item);
            if (!empty($itemErrors)) {
                $errors[$item->id] = $itemErrors;
            }
        }

        return $errors;
    }
}
