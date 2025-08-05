<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the checkout page.
     */
    public function index(): Response
    {
        $cart = $this->cartService->getCartWithItems();
        $errors = $this->cartService->validateCart($cart);

        // Redirect to cart if cart is empty
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add items before proceeding to checkout.');
        }

        // Check for validation errors that would prevent checkout
        if (!empty($errors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Please resolve cart issues before proceeding to checkout.');
        }

        return Inertia::render('Checkout', [
            'cart' => $cart,
            'validation_errors' => $errors,
        ]);
    }
}
