<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartErrorResource;
use App\Http\Resources\CartSuccessResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    use AuthorizesRequests, ApiResponseTrait;
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the cart page.
     */
    public function index(): Response
    {
        $cart = $this->cartService->getCartWithItems();
        $errors = $this->cartService->validateCart($cart);

        return Inertia::render('Cart', [
            'cart' => $cart,
            'validation_errors' => $errors,
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        \Log::info('Add to cart request', [
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'variant_id' => $request->variant_id,
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            \Log::info('Product found', ['product' => $product->toArray()]);

            $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;
            if ($variant) {
                \Log::info('Variant found', ['variant' => $variant->toArray()]);
            }

            $quantity = $request->quantity ?? 1;

            // Validate product availability
            if ($product->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available'
                ], 400);
            }

            // Validate stock
            $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
            if ($quantity > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$availableStock} items available in stock"
                ], 400);
            }

            $cartItem = $this->cartService->addToCart(
                $product,
                $quantity,
                $variant
            );

            $cartSummary = $this->cartService->getCartSummary();

            return (new CartSuccessResource([
                'message' => 'Item added to cart successfully',
                'data' => $cartItem,
                'cart_summary' => $cartSummary,
            ]))->response()->setStatusCode(201);

        } catch (CartException $e) {
            \Log::error('Cart exception in add to cart', [
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
                'data' => $e->getErrorData(),
            ]);
            return (new CartErrorResource([
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
                'errors' => $e->getErrorData(),
            ]))->response()->setStatusCode(400);
        } catch (\Exception $e) {
            \Log::error('General exception in add to cart', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return (new CartErrorResource([
                'message' => 'Failed to add item to cart',
                'code' => 'INTERNAL_ERROR',
            ]))->response()->setStatusCode(500);
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        // Authorize the action
        $this->authorize('update', $cartItem);

        try {
            $quantity = $request->quantity;

            // Validate stock availability
            $errors = $this->cartService->validateCartItem($cartItem);
            if (!empty($errors) && $quantity > 0) {
                return response()->json([
                    'success' => false,
                    'message' => implode(', ', $errors)
                ], 400);
            }

            $this->cartService->updateQuantity($cartItem, $quantity);
            $cartSummary = $this->cartService->getCartSummary();

            return (new CartSuccessResource([
                'message' => $quantity > 0 ? 'Cart updated successfully' : 'Item removed from cart',
                'cart_summary' => $cartSummary,
            ]))->response();

        } catch (CartException $e) {
            return (new CartErrorResource([
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
                'errors' => $e->getErrorData(),
            ]))->response()->setStatusCode(400);
        } catch (\Exception $e) {
            return (new CartErrorResource([
                'message' => 'Failed to update cart',
                'code' => 'INTERNAL_ERROR',
            ]))->response()->setStatusCode(500);
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy(CartItem $cartItem): JsonResponse
    {
        // Authorize the action
        $this->authorize('delete', $cartItem);

        try {
            $this->cartService->removeFromCart($cartItem);
            $cartSummary = $this->cartService->getCartSummary();

            return (new CartSuccessResource([
                'message' => 'Item removed from cart',
                'cart_summary' => $cartSummary,
            ]))->response();

        } catch (\Exception $e) {
            return (new CartErrorResource([
                'message' => 'Failed to remove item from cart',
                'code' => 'INTERNAL_ERROR',
            ]))->response()->setStatusCode(500);
        }
    }

    /**
     * Clear entire cart.
     */
    public function clear(): JsonResponse
    {
        try {
            $this->cartService->clearCart();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'cart_summary' => $this->cartService->getCartSummary(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart'
            ], 500);
        }
    }

    /**
     * Get cart summary for AJAX requests.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'cart_summary' => $this->cartService->getCartSummary(),
        ]);
    }

    /**
     * Get full cart data for AJAX requests.
     */
    public function data(): JsonResponse
    {
        try {
            $cart = $this->cartService->getCartWithItems();

            return response()->json([
                'success' => true,
                'cart' => [
                    'id' => $cart->id,
                    'total_quantity' => $cart->total_quantity,
                    'total_price' => $cart->total_price,
                    'formatted_total' => $cart->formatted_total,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total_price' => $item->price * $item->quantity,
                            'formatted_price' => '$' . number_format($item->price / 100, 2),
                            'formatted_total' => '$' . number_format(($item->price * $item->quantity) / 100, 2),
                            'display_name' => $item->product->name . ($item->variant ? ' - ' . $item->variant->name : ''),
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'slug' => $item->product->slug,
                                'sku' => $item->product->sku,
                                'formatted_price' => $item->product->formatted_price,
                                'primaryImage' => $item->product->primaryImage ? [
                                    'image_path' => $item->product->primaryImage->image_path,
                                ] : null,
                                'brand' => $item->product->brand ? [
                                    'id' => $item->product->brand->id,
                                    'name' => $item->product->brand->name,
                                ] : null,
                            ],
                            'variant' => $item->variant ? [
                                'id' => $item->variant->id,
                                'name' => $item->variant->name,
                                'sku' => $item->variant->sku,
                                'formatted_price' => $item->variant->formatted_price,
                            ] : null,
                        ];
                    }),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart data'
            ], 500);
        }
    }

    /**
     * Validate cart items for availability and stock.
     */
    public function validate(): JsonResponse
    {
        try {
            $validationErrors = $this->cartService->validateCart();

            return response()->json([
                'success' => true,
                'validation_errors' => $validationErrors,
                'has_errors' => !empty($validationErrors),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate cart'
            ], 500);
        }
    }
}
