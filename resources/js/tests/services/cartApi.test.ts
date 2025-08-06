/**
 * Cart API Service Integration Tests
 * 
 * Tests for the CartApiService including HTTP requests,
 * error handling, and response formatting.
 */

import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { CartApiService } from '@/services/cartApi';

// Mock fetch globally
const mockFetch = vi.fn();
global.fetch = mockFetch;

// Mock CSRF token
Object.defineProperty(document, 'querySelector', {
    value: vi.fn().mockReturnValue({
        getAttribute: vi.fn().mockReturnValue('mock-csrf-token'),
    }),
    writable: true,
});

describe('CartApiService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    describe('addToCart', () => {
        it('should make correct API call for adding item', async () => {
            const mockResponse = {
                success: true,
                message: 'Item added to cart',
                cart_summary: {
                    total_quantity: 1,
                    total_price: 1999,
                    formatted_total: '$19.99',
                },
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            });

            const result = await CartApiService.addToCart(1, 2);

            expect(mockFetch).toHaveBeenCalledWith('/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': 'mock-csrf-token',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    product_id: 1,
                    quantity: 2,
                }),
            });

            expect(result).toEqual(mockResponse);
        });

        it('should include variant_id when provided', async () => {
            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({ success: true }),
            });

            await CartApiService.addToCart(1, 2, 5);

            expect(mockFetch).toHaveBeenCalledWith('/cart', {
                method: 'POST',
                headers: expect.any(Object),
                body: JSON.stringify({
                    product_id: 1,
                    quantity: 2,
                    variant_id: 5,
                }),
            });
        });

        it('should handle API errors', async () => {
            mockFetch.mockResolvedValue({
                ok: false,
                status: 422,
                json: () => Promise.resolve({
                    message: 'Validation failed',
                }),
            });

            await expect(CartApiService.addToCart(1, 1)).rejects.toThrow('Validation failed');
        });

        it('should handle network errors', async () => {
            mockFetch.mockRejectedValue(new Error('Network error'));

            await expect(CartApiService.addToCart(1, 1)).rejects.toThrow('Network error');
        });
    });

    describe('updateQuantity', () => {
        it('should make correct API call for updating quantity', async () => {
            const mockResponse = {
                success: true,
                message: 'Quantity updated',
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            });

            const result = await CartApiService.updateQuantity(1, 3);

            expect(mockFetch).toHaveBeenCalledWith('/cart/1', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': 'mock-csrf-token',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ quantity: 3 }),
            });

            expect(result).toEqual(mockResponse);
        });
    });

    describe('removeItem', () => {
        it('should make correct API call for removing item', async () => {
            const mockResponse = {
                success: true,
                message: 'Item removed',
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            });

            const result = await CartApiService.removeItem(1);

            expect(mockFetch).toHaveBeenCalledWith('/cart/1', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': 'mock-csrf-token',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            expect(result).toEqual(mockResponse);
        });
    });

    describe('clearCart', () => {
        it('should make correct API call for clearing cart', async () => {
            const mockResponse = {
                success: true,
                message: 'Cart cleared',
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            });

            const result = await CartApiService.clearCart();

            expect(mockFetch).toHaveBeenCalledWith('/cart', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': 'mock-csrf-token',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            expect(result).toEqual(mockResponse);
        });
    });

    describe('getCart', () => {
        it('should make correct API call for getting cart', async () => {
            const mockResponse = {
                success: true,
                data: {
                    id: 1,
                    items: [],
                    total_quantity: 0,
                },
            };

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve(mockResponse),
            });

            const result = await CartApiService.getCart();

            expect(mockFetch).toHaveBeenCalledWith('/cart', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': 'mock-csrf-token',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            expect(result).toEqual(mockResponse);
        });
    });

    describe('Error Handling', () => {
        it('should handle missing CSRF token', async () => {
            // Mock missing CSRF token
            document.querySelector = vi.fn().mockReturnValue(null);

            mockFetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({ success: true }),
            });

            await CartApiService.addToCart(1, 1);

            expect(mockFetch).toHaveBeenCalledWith('/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: expect.any(String),
            });
        });

        it('should handle malformed JSON response', async () => {
            mockFetch.mockResolvedValue({
                ok: false,
                status: 500,
                json: () => Promise.reject(new Error('Invalid JSON')),
            });

            await expect(CartApiService.addToCart(1, 1)).rejects.toThrow('HTTP 500: Request failed');
        });
    });
});
