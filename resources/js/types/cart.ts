/**
 * Shared Cart Type Definitions
 *
 * These types ensure consistency across all cart-related components
 * and prevent duplicate interface definitions.
 */

export interface CartItem {
    id: number;
    product_id: number;
    product_variant_id?: number;
    quantity: number;
    price: number;
    total_price: number;
    formatted_price: string;
    formatted_total: string;
    display_name: string;
    product: {
        id: number;
        name: string;
        slug: string;
        sku: string;
        primaryImage?: {
            image_path: string;
        };
        brand?: {
            id: number;
            name: string;
        };
    };
    variant?: {
        id: number;
        name: string;
    };
}

export interface Cart {
    id: number;
    total_quantity: number;
    total_price: number;
    formatted_total: string;
    items: CartItem[];
}

export interface CartValidationErrors {
    [itemId: number]: string[];
}

export interface CartSummary {
    id: number;
    total_quantity: number;
    total_price: number;
    formatted_total: string;
    items_count: number;
    is_empty: boolean;
}

/**
 * Cart operation result interface
 */
export interface CartOperationResult {
    success: boolean;
    message: string;
    data?: unknown;
}

/**
 * Cart state interface for the store
 */
export interface CartState {
    cart: Cart | null;
    isLoading: boolean;
    error: string | null;
    validationErrors: CartValidationErrors;
}

/**
 * Cart API error interface
 */
export interface CartApiError {
    message: string;
    code?: string;
    field?: string;
    details?: Record<string, unknown>;
}

/**
 * Cart operation context for error handling
 */
export interface CartOperationContext {
    operation: 'add' | 'update' | 'remove' | 'clear' | 'validate' | 'fetch';
    productId?: number;
    variantId?: number;
    quantity?: number;
    itemId?: number;
}

/**
 * Cart metrics interface for analytics
 */
export interface CartMetrics {
    totalItems: number;
    totalValue: number;
    averageItemValue: number;
    lastUpdated: string;
    sessionId?: string;
}

/**
 * Cart persistence options
 */
export interface CartPersistenceOptions {
    enableLocalStorage: boolean;
    enableSessionStorage: boolean;
    storageKey: string;
    expirationHours: number;
}
