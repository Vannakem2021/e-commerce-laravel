<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Session;

class CartItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view cart items (for their own cart)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, CartItem $cartItem): bool
    {
        return $this->owns($user, $cartItem);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Anyone can create cart items
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, CartItem $cartItem): bool
    {
        return $this->owns($user, $cartItem);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, CartItem $cartItem): bool
    {
        return $this->owns($user, $cartItem);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, CartItem $cartItem): bool
    {
        return $this->owns($user, $cartItem);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, CartItem $cartItem): bool
    {
        return $this->owns($user, $cartItem);
    }

    /**
     * Check if the user owns the cart item.
     */
    private function owns(?User $user, CartItem $cartItem): bool
    {
        $cart = $cartItem->cart;

        if ($user && $cart->user_id) {
            // Authenticated user - check user_id
            return $cart->user_id === $user->id;
        } elseif (!$user && $cart->session_id) {
            // Guest user - check session_id
            return $cart->session_id === Session::getId();
        }

        return false;
    }
}
