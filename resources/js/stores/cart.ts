import { defineStore } from 'pinia';
import { computed, ref, onMounted } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';
import { useErrorHandler } from '@/composables/useErrorHandler';
import { useCartError } from '@/composables/useCartError';
import { useCartPersistence } from '@/composables/useCartPersistence';
import { useOptimisticUpdates } from '@/composables/useOptimisticUpdates';
import { useCartAnalytics } from '@/composables/useCartAnalytics';
import { useCartPerformance } from '@/composables/useCartPerformance';
import { CartApiService } from '@/services/cartApi';
import {
    type Cart,
    type CartItem,
    type CartValidationErrors,
    type CartOperationResult,
    type CartSummary,
    type CartOperationContext
} from '@/types/cart';

export const useCartStore = defineStore('cart', () => {
    // Composables
    const toast = useSonnerToast();
    const errorHandler = useErrorHandler();
    const cartError = useCartError();
    const persistence = useCartPersistence({
        enableLocalStorage: true,
        enableSessionStorage: true,
        expirationHours: 24,
    });
    const optimistic = useOptimisticUpdates();
    const analytics = useCartAnalytics({
        enableTracking: true,
        sessionTimeout: 30,
        batchSize: 5,
    });
    const performance = useCartPerformance({
        debounceDelay: 300,
        enableLazyLoading: true,
        maxCacheSize: 50,
    });

    // State
    const cart = ref<Cart | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const validationErrors = ref<CartValidationErrors>({});

    // Getters
    const cartCount = computed(() => {
        if (!cart.value) return 0;

        // If we have items, calculate from items for accuracy
        if (cart.value.items && cart.value.items.length > 0) {
            return cart.value.items.reduce((sum, item) => sum + item.quantity, 0);
        }

        // Otherwise use the total_quantity property
        return cart.value.total_quantity || 0;
    });
    const cartTotal = computed(() => cart.value?.formatted_total || '$0.00');
    const hasItems = computed(() => (cart.value?.items.length || 0) > 0);
    const isEmpty = computed(() => !hasItems.value);
    const hasValidationErrors = computed(() => Object.keys(validationErrors.value).length > 0);
    const validationErrorCount = computed(() => Object.keys(validationErrors.value).length);

    // Initialize persistence and auto-save
    const initializePersistence = () => {
        // Try to load persisted cart data
        const persistedData = persistence.initialize();

        if (persistedData?.cart && !cart.value) {
            // Only use persisted data if no cart is currently set
            cart.value = persistedData.cart;

            toast.success('Cart restored', {
                description: 'Your previous cart has been restored from your last session.',
            });
        }

        // Set up auto-save watching
        persistence.watchCart(cart);

        return !!persistedData;
    };

    // Actions
    const setInitialData = (initialCart: Cart, initialSummary?: any, initialValidationErrors?: CartValidationErrors) => {
        if (initialCart) {
            cart.value = initialCart;
            console.log('Cart store initialized with data:', {
                id: initialCart.id,
                total_quantity: initialCart.total_quantity,
                items_count: initialCart.items?.length || 0,
                computed_count: cartCount.value
            });
            // Save to persistence immediately
            persistence.saveCartImmediate(initialCart, initialSummary);
        }
        if (initialValidationErrors) {
            validationErrors.value = initialValidationErrors;
        }
        return !!initialCart;
    };

    const fetchCart = async (force = false): Promise<CartOperationResult> => {
        // Skip if we already have data and not forcing
        if (!force && cart.value && cart.value.items.length >= 0) {
            return { success: true, message: 'Cart already loaded' };
        }

        isLoading.value = true;
        errorHandler.clearError();

        try {
            const response = await CartApiService.getCart();

            if (response.success && response.data) {
                cart.value = response.data;
                return { success: true, message: 'Cart loaded successfully' };
            } else {
                throw new Error(response.message || 'Failed to load cart');
            }
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Failed to load cart';
            errorHandler.handleCartError(new Error(message), 'load cart');
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };



    const addToCart = async (productId: number, quantity: number = 1, variantId?: number): Promise<CartOperationResult> => {
        isLoading.value = true;
        errorHandler.clearError();

        // Store original cart state for rollback
        const originalCart = cart.value ? JSON.parse(JSON.stringify(cart.value)) : null;

        try {
            const response = await CartApiService.addToCart(productId, quantity, variantId);

            if (response.success) {
                // Update cart state with server response
                if (response.cart_summary) {
                    console.log('Updating cart with server response:', response.cart_summary);

                    if (cart.value) {
                        // Update existing cart totals with server data
                        cart.value.total_quantity = response.cart_summary.total_quantity;
                        cart.value.total_price = response.cart_summary.total_price;
                        cart.value.formatted_total = response.cart_summary.formatted_total;
                    } else {
                        // Create new cart if none exists
                        cart.value = {
                            id: response.cart_summary.id || 0,
                            total_quantity: response.cart_summary.total_quantity,
                            total_price: response.cart_summary.total_price,
                            formatted_total: response.cart_summary.formatted_total,
                            items: [], // Items will be loaded when needed
                        };
                    }

                    console.log('Cart after update:', {
                        id: cart.value.id,
                        total_quantity: cart.value.total_quantity,
                        computed_count: cartCount.value
                    });
                }

                toast.success('Added to cart!', {
                    description: `Item has been added to your cart successfully. You now have ${cartCount.value} item${cartCount.value !== 1 ? 's' : ''} in your cart.`
                });

                // Save to persistence
                persistence.saveCart(cart.value, response.cart_summary);

                // Track analytics
                analytics.trackAddToCart(
                    { id: productId, name: 'Product', price: 0 }, // Product data would come from API
                    quantity,
                    variantId
                );

                return { success: true, message: response.message };
            } else {
                throw new Error(response.message || 'Failed to add item to cart');
            }
        } catch (error) {
            // Rollback to original state on error
            cart.value = originalCart;

            const message = error instanceof Error ? error.message : 'Failed to add item to cart';
            errorHandler.handleCartError(new Error(message), 'add item');
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };

    const updateQuantity = async (itemId: number, quantity: number): Promise<CartOperationResult> => {
        isLoading.value = true;
        errorHandler.clearError();

        try {
            const response = await CartApiService.updateQuantity(itemId, quantity);

            if (response.success) {
                // Update cart state locally for immediate feedback
                if (cart.value) {
                    if (quantity === 0) {
                        // Remove item from cart
                        cart.value.items = cart.value.items.filter(item => item.id !== itemId);
                    } else {
                        // Update item quantity
                        const item = cart.value.items.find(item => item.id === itemId);
                        if (item) {
                            item.quantity = quantity;
                            item.total_price = item.price * quantity;
                            item.formatted_total = '$' + (item.total_price / 100).toFixed(2);
                        }
                    }

                    // Use server-returned cart summary for accurate totals
                    if (response.cart_summary) {
                        cart.value.total_quantity = response.cart_summary.total_quantity;
                        cart.value.total_price = response.cart_summary.total_price;
                        cart.value.formatted_total = response.cart_summary.formatted_total;
                    } else {
                        // Fallback: recalculate cart totals
                        cart.value.total_quantity = cart.value.items.reduce((sum, item) => sum + item.quantity, 0);
                        cart.value.total_price = cart.value.items.reduce((sum, item) => sum + item.total_price, 0);
                        cart.value.formatted_total = '$' + (cart.value.total_price / 100).toFixed(2);
                    }
                }

                return { success: true, message: response.message };
            } else {
                throw new Error(response.message || 'Failed to update cart item');
            }
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Failed to update cart item';
            errorHandler.handleCartError(new Error(message), 'update quantity');
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };

    const removeItem = async (itemId: number): Promise<CartOperationResult> => {
        isLoading.value = true;
        errorHandler.clearError();

        try {
            const response = await CartApiService.removeItem(itemId);

            if (response.success) {
                // Update cart state locally for immediate feedback
                if (cart.value) {
                    cart.value.items = cart.value.items.filter(item => item.id !== itemId);

                    // Use server-returned cart summary for accurate totals
                    if (response.cart_summary) {
                        cart.value.total_quantity = response.cart_summary.total_quantity;
                        cart.value.total_price = response.cart_summary.total_price;
                        cart.value.formatted_total = response.cart_summary.formatted_total;
                    } else {
                        // Fallback: recalculate cart totals
                        cart.value.total_quantity = cart.value.items.reduce((sum, item) => sum + item.quantity, 0);
                        cart.value.total_price = cart.value.items.reduce((sum, item) => sum + item.total_price, 0);
                        cart.value.formatted_total = '$' + (cart.value.total_price / 100).toFixed(2);
                    }
                }

                toast.success('Item removed', {
                    description: 'Item has been removed from your cart.'
                });

                return { success: true, message: response.message };
            } else {
                throw new Error(response.message || 'Failed to remove item from cart');
            }
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Failed to remove item';
            errorHandler.handleCartError(new Error(message), 'remove item');
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };

    const clearCart = async (): Promise<CartOperationResult> => {
        isLoading.value = true;
        errorHandler.clearError();

        try {
            const response = await CartApiService.clearCart();

            if (response.success) {
                // Reset cart state
                cart.value = null;

                toast.success('Cart cleared', {
                    description: 'All items have been removed from your cart.'
                });

                return { success: true, message: response.message };
            } else {
                throw new Error(response.message || 'Failed to clear cart');
            }
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Failed to clear cart';
            errorHandler.handleCartError(new Error(message), 'clear cart');
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };

    const validateCartItems = async () => {
        // Validation is now handled server-side and passed via Inertia props
        // This method is kept for compatibility but doesn't make API calls
        return validationErrors.value;
    };

    const clearError = () => {
        error.value = null;
    };

    const clearValidationErrors = () => {
        validationErrors.value = {};
    };

    // Don't initialize cart on store creation - let components decide when to fetch
    // fetchCart();

    return {
        // State
        cart,
        isLoading,
        error,
        validationErrors,

        // Getters
        cartCount,
        cartTotal,
        hasItems,
        isEmpty,
        hasValidationErrors,
        validationErrorCount,

        // Actions
        initializePersistence,
        setInitialData,
        fetchCart,
        addToCart,
        updateQuantity,
        removeItem,
        clearCart,
        validateCartItems,
        clearError,
        clearValidationErrors,

        // Persistence
        clearPersistedData: persistence.clearPersistedData,
        persistenceState: persistence.state,

        // Analytics
        trackCartView: analytics.trackCartView,
        trackCheckoutStart: analytics.trackCheckoutStart,
        getAnalyticsSummary: analytics.getAnalyticsSummary,

        // Performance
        optimizeCartCalculations: performance.optimizeCartCalculations,
        getPerformanceStats: performance.getPerformanceStats,
        optimizeMemory: performance.optimizeMemory,
    };
});
