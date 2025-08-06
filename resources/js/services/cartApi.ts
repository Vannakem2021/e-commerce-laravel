/**
 * Centralized Cart API Service
 * 
 * This service provides a unified interface for all cart-related API operations.
 * It handles authentication, error handling, and response formatting consistently.
 */

export interface CartApiResponse<T = any> {
    success: boolean;
    message: string;
    data?: T;
    cart_summary?: {
        id: number;
        total_quantity: number;
        total_price: number;
        formatted_total: string;
        items_count: number;
        is_empty: boolean;
    };
}

export class CartApiService {
    /**
     * Make authenticated API request with consistent headers and error handling
     */
    private static async makeRequest<T = any>(url: string, options: RequestInit = {}): Promise<CartApiResponse<T>> {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP ${response.status}: Request failed`);
        }
        
        return response.json();
    }

    /**
     * Add a product to the cart
     */
    static async addToCart(productId: number, quantity: number = 1, variantId?: number): Promise<CartApiResponse> {
        const payload: any = {
            product_id: productId,
            quantity,
        };

        // Only include variant_id if it's provided
        if (variantId !== undefined && variantId !== null) {
            payload.variant_id = variantId;
        }

        return this.makeRequest('/cart', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    }

    /**
     * Update cart item quantity
     */
    static async updateQuantity(itemId: number, quantity: number): Promise<CartApiResponse> {
        return this.makeRequest(`/cart/${itemId}`, {
            method: 'PUT',
            body: JSON.stringify({ quantity }),
        });
    }

    /**
     * Remove item from cart
     */
    static async removeItem(itemId: number): Promise<CartApiResponse> {
        return this.makeRequest(`/cart/${itemId}`, {
            method: 'DELETE',
        });
    }

    /**
     * Clear entire cart
     */
    static async clearCart(): Promise<CartApiResponse> {
        return this.makeRequest('/cart', {
            method: 'DELETE',
        });
    }

    /**
     * Get cart data (if needed for refresh operations)
     */
    static async getCart(): Promise<CartApiResponse> {
        return this.makeRequest('/cart', {
            method: 'GET',
        });
    }
}
