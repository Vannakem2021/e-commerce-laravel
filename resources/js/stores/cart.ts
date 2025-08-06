import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';
import { useErrorHandler } from '@/composables/useErrorHandler';

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

export const useCartStore = defineStore('cart', () => {
    // Composables
    const toast = useSonnerToast();
    const errorHandler = useErrorHandler();

    // State
    const cart = ref<Cart | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const validationErrors = ref<CartValidationErrors>({});

    // Getters
    const cartCount = computed(() => {
        const count = cart.value?.total_quantity || 0;
        console.log('Cart count computed:', count, 'from cart:', cart.value);
        return count;
    });
    const cartTotal = computed(() => cart.value?.formatted_total || '$0.00');
    const hasItems = computed(() => (cart.value?.items.length || 0) > 0);
    const isEmpty = computed(() => !hasItems.value);
    const hasValidationErrors = computed(() => Object.keys(validationErrors.value).length > 0);
    const validationErrorCount = computed(() => Object.keys(validationErrors.value).length);

    // Actions
    const setInitialData = (initialCart: Cart, initialSummary?: any, initialValidationErrors?: CartValidationErrors) => {
        if (initialCart) {
            cart.value = initialCart;
            console.log('Cart store initialized with data:', cart.value);
        }
        if (initialValidationErrors) {
            validationErrors.value = initialValidationErrors;
            console.log('Validation errors set:', validationErrors.value);
        }
        return !!initialCart;
    };

    const fetchCart = async (force = false) => {
        // Skip if we already have data and not forcing
        if (!force && cart.value && cart.value.items.length >= 0) {
            return;
        }

        // Since we removed the API endpoint, we can't fetch cart data anymore
        // This method is kept for compatibility but doesn't do anything
        console.warn('fetchCart called but API endpoint removed. Cart data should be provided via Inertia props.');
        return;
    };



    const addToCart = async (productId: number, quantity: number = 1, variantId?: number) => {
        isLoading.value = true;
        errorHandler.clearError();

        const operation = async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token:', csrfToken);
            console.log('Adding to cart:', { productId, quantity, variantId });

            const payload: any = {
                product_id: productId,
                quantity,
            };

            // Only include variant_id if it's not undefined/null
            if (variantId !== undefined && variantId !== null) {
                payload.variant_id = variantId;
            }

            const response = await fetch('/cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}: Failed to add item to cart`);
            }

            return await response.json();
        };

        try {
            const data = await errorHandler.retry(operation, 2);

            if (data.success) {
                // Update cart count from cart_summary if available
                if (data.cart_summary) {
                    // Update cart state with summary data
                    if (cart.value) {
                        cart.value.total_quantity = data.cart_summary.total_quantity;
                        cart.value.total_price = data.cart_summary.total_price;
                        cart.value.formatted_total = data.cart_summary.formatted_total;
                    }
                }

                toast.success('Added to cart!', {
                    description: `Item has been added to your cart successfully. You now have ${cartCount.value} item${cartCount.value !== 1 ? 's' : ''} in your cart.`
                });
                return { success: true, message: data.message };
            } else {
                errorHandler.handleCartError(new Error(data.message), 'add item');
                return { success: false, message: data.message };
            }
        } catch (err) {
            const errorState = errorHandler.handleCartError(err, 'add item');
            return { success: false, message: errorState.message };
        } finally {
            isLoading.value = false;
        }
    };

    const updateQuantity = async (itemId: number, quantity: number) => {
        isLoading.value = true;
        errorHandler.clearError();

        const operation = async () => {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ quantity }),
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}: Failed to update cart`);
            }

            return await response.json();
        };

        try {
            const data = await operation();

            if (data.success) {
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
                    if (data.cart_summary) {
                        cart.value.total_quantity = data.cart_summary.total_quantity;
                        cart.value.total_price = data.cart_summary.total_price;
                        cart.value.formatted_total = data.cart_summary.formatted_total;
                    } else {
                        // Fallback: recalculate cart totals
                        cart.value.total_quantity = cart.value.items.reduce((sum, item) => sum + item.quantity, 0);
                        cart.value.total_price = cart.value.items.reduce((sum, item) => sum + item.total_price, 0);
                        cart.value.formatted_total = '$' + (cart.value.total_price / 100).toFixed(2);
                    }
                }

                return { success: true, message: data.message };
            } else {
                errorHandler.handleCartError(new Error(data.message), 'update quantity');
                return { success: false, message: data.message };
            }
        } catch (err) {
            const errorState = errorHandler.handleCartError(err, 'update quantity');
            return { success: false, message: errorState.message };
        } finally {
            isLoading.value = false;
        }
    };

    const removeItem = async (itemId: number) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (data.success) {
                // Update cart state locally for immediate feedback
                if (cart.value) {
                    cart.value.items = cart.value.items.filter(item => item.id !== itemId);

                    // Use server-returned cart summary for accurate totals
                    if (data.cart_summary) {
                        cart.value.total_quantity = data.cart_summary.total_quantity;
                        cart.value.total_price = data.cart_summary.total_price;
                        cart.value.formatted_total = data.cart_summary.formatted_total;
                    } else {
                        // Fallback: recalculate cart totals
                        cart.value.total_quantity = cart.value.items.reduce((sum, item) => sum + item.quantity, 0);
                        cart.value.total_price = cart.value.items.reduce((sum, item) => sum + item.total_price, 0);
                        cart.value.formatted_total = '$' + (cart.value.total_price / 100).toFixed(2);
                    }
                }

                return { success: true, message: data.message };
            } else {
                error.value = data.message;
                return { success: false, message: data.message };
            }
        } catch (err) {
            const message = err instanceof Error ? err.message : 'Failed to remove item';
            error.value = message;
            return { success: false, message };
        } finally {
            isLoading.value = false;
        }
    };

    const clearCart = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/cart', {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (data.success) {
                // Reset cart state
                cart.value = null;
                return { success: true, message: data.message };
            } else {
                error.value = data.message;
                return { success: false, message: data.message };
            }
        } catch (err) {
            const message = err instanceof Error ? err.message : 'Failed to clear cart';
            error.value = message;
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
        setInitialData,
        fetchCart,
        addToCart,
        updateQuantity,
        removeItem,
        clearCart,
        validateCartItems,
        clearError,
        clearValidationErrors,
    };
});
