/**
 * Cart Store Unit Tests
 * 
 * Tests for the Pinia cart store functionality including
 * state management, API integration, and error handling.
 */

import { describe, it, expect, beforeEach, vi, type Mock } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useCartStore } from '@/stores/cart';
import { CartApiService } from '@/services/cartApi';
import type { Cart, CartItem } from '@/types/cart';

// Mock the CartApiService
vi.mock('@/services/cartApi');
const mockCartApiService = CartApiService as {
    addToCart: Mock;
    updateQuantity: Mock;
    removeItem: Mock;
    clearCart: Mock;
    getCart: Mock;
};

// Mock composables
vi.mock('@/composables/useSonnerToast', () => ({
    useSonnerToast: () => ({
        success: vi.fn(),
        error: vi.fn(),
    }),
}));

vi.mock('@/composables/useErrorHandler', () => ({
    useErrorHandler: () => ({
        clearError: vi.fn(),
        handleCartError: vi.fn(),
        retry: vi.fn(),
    }),
}));

vi.mock('@/composables/useCartError', () => ({
    useCartError: () => ({
        handleCartError: vi.fn(),
        handleCartSuccess: vi.fn(),
    }),
}));

describe('Cart Store', () => {
    let cartStore: ReturnType<typeof useCartStore>;

    const mockCart: Cart = {
        id: 1,
        total_quantity: 2,
        total_price: 2999,
        formatted_total: '$29.99',
        items: [
            {
                id: 1,
                product_id: 1,
                quantity: 1,
                price: 1999,
                total_price: 1999,
                formatted_price: '$19.99',
                formatted_total: '$19.99',
                display_name: 'Test Product',
                product: {
                    id: 1,
                    name: 'Test Product',
                    slug: 'test-product',
                    sku: 'TEST-001',
                },
            },
        ],
    };

    beforeEach(() => {
        setActivePinia(createPinia());
        cartStore = useCartStore();
        vi.clearAllMocks();
    });

    describe('Initial State', () => {
        it('should have correct initial state', () => {
            expect(cartStore.cart).toBeNull();
            expect(cartStore.isLoading).toBe(false);
            expect(cartStore.cartCount).toBe(0);
            expect(cartStore.cartTotal).toBe('$0.00');
            expect(cartStore.hasItems).toBe(false);
            expect(cartStore.isEmpty).toBe(true);
        });
    });

    describe('setInitialData', () => {
        it('should set cart data correctly', () => {
            const result = cartStore.setInitialData(mockCart);
            
            expect(result).toBe(true);
            expect(cartStore.cart).toEqual(mockCart);
            expect(cartStore.cartCount).toBe(2);
            expect(cartStore.cartTotal).toBe('$29.99');
            expect(cartStore.hasItems).toBe(true);
            expect(cartStore.isEmpty).toBe(false);
        });

        it('should return false when no cart data provided', () => {
            const result = cartStore.setInitialData(null as any);
            
            expect(result).toBe(false);
            expect(cartStore.cart).toBeNull();
        });
    });

    describe('addToCart', () => {
        it('should add item to cart successfully', async () => {
            mockCartApiService.addToCart.mockResolvedValue({
                success: true,
                message: 'Item added to cart',
                cart_summary: {
                    total_quantity: 1,
                    total_price: 1999,
                    formatted_total: '$19.99',
                },
            });

            cartStore.setInitialData(mockCart);
            const result = await cartStore.addToCart(2, 1);

            expect(result.success).toBe(true);
            expect(mockCartApiService.addToCart).toHaveBeenCalledWith(2, 1, undefined);
        });

        it('should handle add to cart error', async () => {
            mockCartApiService.addToCart.mockRejectedValue(new Error('Network error'));

            const result = await cartStore.addToCart(1, 1);

            expect(result.success).toBe(false);
            expect(result.message).toBe('Network error');
        });

        it('should add item with variant', async () => {
            mockCartApiService.addToCart.mockResolvedValue({
                success: true,
                message: 'Item added to cart',
            });

            await cartStore.addToCart(1, 2, 5);

            expect(mockCartApiService.addToCart).toHaveBeenCalledWith(1, 2, 5);
        });
    });

    describe('updateQuantity', () => {
        beforeEach(() => {
            cartStore.setInitialData(mockCart);
        });

        it('should update item quantity successfully', async () => {
            mockCartApiService.updateQuantity.mockResolvedValue({
                success: true,
                message: 'Quantity updated',
                cart_summary: {
                    total_quantity: 3,
                    total_price: 5997,
                    formatted_total: '$59.97',
                },
            });

            const result = await cartStore.updateQuantity(1, 3);

            expect(result.success).toBe(true);
            expect(mockCartApiService.updateQuantity).toHaveBeenCalledWith(1, 3);
            expect(cartStore.cart?.items[0].quantity).toBe(3);
        });

        it('should remove item when quantity is 0', async () => {
            mockCartApiService.updateQuantity.mockResolvedValue({
                success: true,
                message: 'Item removed',
                cart_summary: {
                    total_quantity: 0,
                    total_price: 0,
                    formatted_total: '$0.00',
                },
            });

            await cartStore.updateQuantity(1, 0);

            expect(cartStore.cart?.items).toHaveLength(0);
        });
    });

    describe('removeItem', () => {
        beforeEach(() => {
            cartStore.setInitialData(mockCart);
        });

        it('should remove item successfully', async () => {
            mockCartApiService.removeItem.mockResolvedValue({
                success: true,
                message: 'Item removed',
                cart_summary: {
                    total_quantity: 0,
                    total_price: 0,
                    formatted_total: '$0.00',
                },
            });

            const result = await cartStore.removeItem(1);

            expect(result.success).toBe(true);
            expect(mockCartApiService.removeItem).toHaveBeenCalledWith(1);
            expect(cartStore.cart?.items).toHaveLength(0);
        });
    });

    describe('clearCart', () => {
        beforeEach(() => {
            cartStore.setInitialData(mockCart);
        });

        it('should clear cart successfully', async () => {
            mockCartApiService.clearCart.mockResolvedValue({
                success: true,
                message: 'Cart cleared',
            });

            const result = await cartStore.clearCart();

            expect(result.success).toBe(true);
            expect(mockCartApiService.clearCart).toHaveBeenCalled();
            expect(cartStore.cart).toBeNull();
        });
    });

    describe('Error Handling', () => {
        it('should handle API errors gracefully', async () => {
            mockCartApiService.addToCart.mockRejectedValue(new Error('Server error'));

            const result = await cartStore.addToCart(1, 1);

            expect(result.success).toBe(false);
            expect(result.message).toBe('Server error');
            expect(cartStore.isLoading).toBe(false);
        });
    });
});
