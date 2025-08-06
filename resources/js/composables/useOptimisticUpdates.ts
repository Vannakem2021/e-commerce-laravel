/**
 * Optimistic Updates Composable
 * 
 * Provides optimistic UI updates for cart operations to improve
 * perceived performance and user experience.
 */

import { ref, computed } from 'vue';
import type { Cart, CartItem, CartOperationResult } from '@/types/cart';

export interface OptimisticOperation {
    id: string;
    type: 'add' | 'update' | 'remove' | 'clear';
    timestamp: number;
    productId?: number;
    variantId?: number;
    itemId?: number;
    quantity?: number;
    originalState?: any;
    rollbackFn?: () => void;
}

export interface OptimisticState {
    pendingOperations: OptimisticOperation[];
    isOptimistic: boolean;
    lastOperation: OptimisticOperation | null;
}

export function useOptimisticUpdates() {
    // State
    const state = ref<OptimisticState>({
        pendingOperations: [],
        isOptimistic: false,
        lastOperation: null,
    });

    // Computed
    const hasPendingOperations = computed(() => state.value.pendingOperations.length > 0);
    const pendingOperationsCount = computed(() => state.value.pendingOperations.length);

    /**
     * Generate unique operation ID
     */
    const generateOperationId = (): string => {
        return `opt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    };

    /**
     * Add optimistic operation to pending list
     */
    const addPendingOperation = (operation: Omit<OptimisticOperation, 'id' | 'timestamp'>): string => {
        const operationId = generateOperationId();
        const fullOperation: OptimisticOperation = {
            ...operation,
            id: operationId,
            timestamp: Date.now(),
        };

        state.value.pendingOperations.push(fullOperation);
        state.value.isOptimistic = true;
        state.value.lastOperation = fullOperation;

        return operationId;
    };

    /**
     * Remove operation from pending list
     */
    const removePendingOperation = (operationId: string): void => {
        const index = state.value.pendingOperations.findIndex(op => op.id === operationId);
        if (index !== -1) {
            state.value.pendingOperations.splice(index, 1);
        }

        if (state.value.pendingOperations.length === 0) {
            state.value.isOptimistic = false;
            state.value.lastOperation = null;
        }
    };

    /**
     * Rollback operation if it fails
     */
    const rollbackOperation = (operationId: string): void => {
        const operation = state.value.pendingOperations.find(op => op.id === operationId);
        if (operation?.rollbackFn) {
            operation.rollbackFn();
        }
        removePendingOperation(operationId);
    };

    /**
     * Clear all pending operations
     */
    const clearPendingOperations = (): void => {
        state.value.pendingOperations = [];
        state.value.isOptimistic = false;
        state.value.lastOperation = null;
    };

    /**
     * Optimistically add item to cart
     */
    const optimisticAddToCart = (
        cart: Cart | null,
        productId: number,
        quantity: number = 1,
        variantId?: number,
        productData?: Partial<CartItem>
    ): { operationId: string; updatedCart: Cart } => {
        const originalCart = cart ? JSON.parse(JSON.stringify(cart)) : null;

        // Create optimistic cart item
        const optimisticItem: CartItem = {
            id: -Date.now(), // Temporary negative ID
            product_id: productId,
            product_variant_id: variantId,
            quantity,
            price: productData?.price || 0,
            total_price: (productData?.price || 0) * quantity,
            formatted_price: productData?.formatted_price || '$0.00',
            formatted_total: productData?.formatted_total || '$0.00',
            display_name: productData?.display_name || 'Loading...',
            product: productData?.product || {
                id: productId,
                name: 'Loading...',
                slug: '',
                sku: '',
            },
            variant: productData?.variant,
        };

        // Create or update cart
        let updatedCart: Cart;
        if (cart) {
            // Check if item already exists
            const existingItemIndex = cart.items.findIndex(
                item => item.product_id === productId && item.product_variant_id === variantId
            );

            if (existingItemIndex !== -1) {
                // Update existing item
                updatedCart = {
                    ...cart,
                    items: cart.items.map((item, index) =>
                        index === existingItemIndex
                            ? { ...item, quantity: item.quantity + quantity }
                            : item
                    ),
                };
            } else {
                // Add new item
                updatedCart = {
                    ...cart,
                    items: [...cart.items, optimisticItem],
                };
            }

            // Update totals optimistically
            updatedCart.total_quantity = updatedCart.items.reduce((sum, item) => sum + item.quantity, 0);
            updatedCart.total_price = updatedCart.items.reduce((sum, item) => sum + item.total_price, 0);
            updatedCart.formatted_total = `$${(updatedCart.total_price / 100).toFixed(2)}`;
        } else {
            // Create new cart
            updatedCart = {
                id: -1, // Temporary ID
                total_quantity: quantity,
                total_price: optimisticItem.total_price,
                formatted_total: optimisticItem.formatted_total,
                items: [optimisticItem],
            };
        }

        // Add to pending operations
        const operationId = addPendingOperation({
            type: 'add',
            productId,
            variantId,
            quantity,
            originalState: originalCart,
            rollbackFn: () => {
                // Rollback function would be handled by the calling component
            },
        });

        return { operationId, updatedCart };
    };

    /**
     * Optimistically update item quantity
     */
    const optimisticUpdateQuantity = (
        cart: Cart,
        itemId: number,
        newQuantity: number
    ): { operationId: string; updatedCart: Cart } => {
        const originalCart = JSON.parse(JSON.stringify(cart));

        let updatedCart: Cart;
        if (newQuantity === 0) {
            // Remove item
            updatedCart = {
                ...cart,
                items: cart.items.filter(item => item.id !== itemId),
            };
        } else {
            // Update quantity
            updatedCart = {
                ...cart,
                items: cart.items.map(item =>
                    item.id === itemId
                        ? {
                            ...item,
                            quantity: newQuantity,
                            total_price: item.price * newQuantity,
                            formatted_total: `$${(item.price * newQuantity / 100).toFixed(2)}`,
                        }
                        : item
                ),
            };
        }

        // Update totals
        updatedCart.total_quantity = updatedCart.items.reduce((sum, item) => sum + item.quantity, 0);
        updatedCart.total_price = updatedCart.items.reduce((sum, item) => sum + item.total_price, 0);
        updatedCart.formatted_total = `$${(updatedCart.total_price / 100).toFixed(2)}`;

        const operationId = addPendingOperation({
            type: 'update',
            itemId,
            quantity: newQuantity,
            originalState: originalCart,
        });

        return { operationId, updatedCart };
    };

    /**
     * Optimistically remove item from cart
     */
    const optimisticRemoveItem = (
        cart: Cart,
        itemId: number
    ): { operationId: string; updatedCart: Cart } => {
        const originalCart = JSON.parse(JSON.stringify(cart));

        const updatedCart: Cart = {
            ...cart,
            items: cart.items.filter(item => item.id !== itemId),
        };

        // Update totals
        updatedCart.total_quantity = updatedCart.items.reduce((sum, item) => sum + item.quantity, 0);
        updatedCart.total_price = updatedCart.items.reduce((sum, item) => sum + item.total_price, 0);
        updatedCart.formatted_total = `$${(updatedCart.total_price / 100).toFixed(2)}`;

        const operationId = addPendingOperation({
            type: 'remove',
            itemId,
            originalState: originalCart,
        });

        return { operationId, updatedCart };
    };

    /**
     * Optimistically clear cart
     */
    const optimisticClearCart = (cart: Cart): { operationId: string; updatedCart: Cart | null } => {
        const originalCart = JSON.parse(JSON.stringify(cart));

        const operationId = addPendingOperation({
            type: 'clear',
            originalState: originalCart,
        });

        return {
            operationId,
            updatedCart: null,
        };
    };

    /**
     * Handle operation completion
     */
    const handleOperationComplete = (
        operationId: string,
        result: CartOperationResult,
        onSuccess?: (result: CartOperationResult) => void,
        onError?: (error: string) => void
    ): void => {
        if (result.success) {
            // Operation succeeded, remove from pending
            removePendingOperation(operationId);
            onSuccess?.(result);
        } else {
            // Operation failed, rollback
            rollbackOperation(operationId);
            onError?.(result.message);
        }
    };

    return {
        // State
        state: state.value,
        hasPendingOperations,
        pendingOperationsCount,

        // Methods
        optimisticAddToCart,
        optimisticUpdateQuantity,
        optimisticRemoveItem,
        optimisticClearCart,
        handleOperationComplete,
        rollbackOperation,
        clearPendingOperations,
    };
}
