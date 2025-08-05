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
    const cartCount = computed(() => cart.value?.total_quantity || 0);
    const cartTotal = computed(() => cart.value?.formatted_total || '$0.00');
    const hasItems = computed(() => (cart.value?.items.length || 0) > 0);
    const isEmpty = computed(() => !hasItems.value);
    const hasValidationErrors = computed(() => Object.keys(validationErrors.value).length > 0);
    const validationErrorCount = computed(() => Object.keys(validationErrors.value).length);

    // Actions
    const fetchCart = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            // Fetch full cart data directly instead of summary first
            const response = await fetch('/cart/data', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch cart');
            }

            const data = await response.json();
            console.log('Cart data response:', data);

            if (data.success && data.cart) {
                cart.value = {
                    id: data.cart.id || 0,
                    total_quantity: data.cart.total_quantity || 0,
                    total_price: data.cart.total_price || 0,
                    formatted_total: data.cart.formatted_total || '$0.00',
                    items: data.cart.items || [],
                };
                console.log('Updated cart state:', cart.value);

                // Validate cart items after fetching (optional, don't fail if validation fails)
                try {
                    await validateCartItems();
                } catch (error) {
                    console.warn('Cart validation failed, but continuing:', error);
                }
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch cart';
            console.error('Error fetching cart:', err);
        } finally {
            isLoading.value = false;
        }
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
                // Update cart state (validation is included in fetchCart)
                await fetchCart();
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
                // Update cart state (validation is included in fetchCart)
                await fetchCart();
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
                // Update cart state (validation is included in fetchCart)
                await fetchCart();
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
        try {
            const response = await fetch('/cart/validate', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to validate cart');
            }

            const data = await response.json();

            if (data.success) {
                validationErrors.value = data.validation_errors || {};
                return data.validation_errors || {};
            } else {
                console.error('Cart validation failed:', data.message);
                return {};
            }
        } catch (err) {
            console.error('Error validating cart:', err);
            return {};
        }
    };

    const clearError = () => {
        error.value = null;
    };

    const clearValidationErrors = () => {
        validationErrors.value = {};
    };

    // Initialize cart on store creation
    fetchCart();

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
