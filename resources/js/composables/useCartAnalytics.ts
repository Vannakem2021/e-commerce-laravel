/**
 * Cart Analytics Composable
 * 
 * Vue composable for integrating cart analytics tracking
 * with reactive state management.
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';
import { CartAnalyticsService, type CartAnalyticsConfig } from '@/services/cartAnalytics';
import type { Cart, CartItem, CartMetrics } from '@/types/cart';

export interface AnalyticsState {
    isEnabled: boolean;
    sessionId: string | null;
    metrics: CartMetrics | null;
    lastEvent: string | null;
    eventCount: number;
}

export function useCartAnalytics(config: Partial<CartAnalyticsConfig> = {}) {
    // State
    const state = ref<AnalyticsState>({
        isEnabled: false,
        sessionId: null,
        metrics: null,
        lastEvent: null,
        eventCount: 0,
    });

    // Analytics service instance
    let analyticsService: CartAnalyticsService | null = null;

    // Computed
    const isTracking = computed(() => state.value.isEnabled && analyticsService !== null);
    const sessionMetrics = computed(() => state.value.metrics);

    /**
     * Initialize analytics service
     */
    const initialize = (userId?: number): void => {
        try {
            analyticsService = new CartAnalyticsService({
                enableTracking: true,
                sessionTimeout: 30,
                batchSize: 5,
                flushInterval: 3000,
                endpoint: '/api/analytics/cart', // Optional endpoint
                ...config,
            });

            state.value.isEnabled = true;
            updateMetrics();

            // Track initial cart view if analytics is enabled
            if (analyticsService) {
                const metrics = analyticsService.getSessionMetrics();
                if (metrics) {
                    state.value.sessionId = metrics.sessionId;
                }
            }
        } catch (error) {
            console.warn('Failed to initialize cart analytics:', error);
            state.value.isEnabled = false;
        }
    };

    /**
     * Update metrics from analytics service
     */
    const updateMetrics = (): void => {
        if (analyticsService) {
            state.value.metrics = analyticsService.getSessionMetrics();
        }
    };

    /**
     * Track add to cart event
     */
    const trackAddToCart = (
        product: { id: number; name: string; price: number },
        quantity: number = 1,
        variantId?: number
    ): void => {
        if (!analyticsService) return;

        analyticsService.trackAddToCart(
            product.id,
            quantity,
            product.price * quantity,
            variantId,
            {
                productName: product.name,
                unitPrice: product.price,
            }
        );

        state.value.lastEvent = 'add_to_cart';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track quantity update event
     */
    const trackUpdateQuantity = (
        item: CartItem,
        oldQuantity: number,
        newQuantity: number
    ): void => {
        if (!analyticsService) return;

        analyticsService.trackUpdateQuantity(
            item.product_id,
            oldQuantity,
            newQuantity,
            item.price * newQuantity,
            item.product_variant_id,
        );

        state.value.lastEvent = 'update_quantity';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track remove from cart event
     */
    const trackRemoveFromCart = (item: CartItem): void => {
        if (!analyticsService) return;

        analyticsService.trackRemoveFromCart(
            item.product_id,
            item.quantity,
            item.total_price,
            item.product_variant_id
        );

        state.value.lastEvent = 'remove_from_cart';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track clear cart event
     */
    const trackClearCart = (cart: Cart): void => {
        if (!analyticsService) return;

        analyticsService.trackClearCart(
            cart.total_price,
            cart.total_quantity
        );

        state.value.lastEvent = 'clear_cart';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track cart view event
     */
    const trackCartView = (cart: Cart | null): void => {
        if (!analyticsService || !cart) return;

        analyticsService.trackCartView(
            cart.total_price,
            cart.total_quantity
        );

        state.value.lastEvent = 'cart_view';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track checkout start event
     */
    const trackCheckoutStart = (cart: Cart): void => {
        if (!analyticsService) return;

        analyticsService.trackCheckoutStart(
            cart.total_price,
            cart.total_quantity
        );

        state.value.lastEvent = 'checkout_start';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Track cart abandonment
     */
    const trackCartAbandon = (): void => {
        if (!analyticsService) return;

        analyticsService.trackCartAbandon();

        state.value.lastEvent = 'cart_abandon';
        state.value.eventCount++;
        updateMetrics();
    };

    /**
     * Get analytics summary for debugging
     */
    const getAnalyticsSummary = () => {
        return {
            isEnabled: state.value.isEnabled,
            sessionId: state.value.sessionId,
            eventCount: state.value.eventCount,
            lastEvent: state.value.lastEvent,
            metrics: state.value.metrics,
        };
    };

    /**
     * Enable analytics tracking
     */
    const enableTracking = (): void => {
        if (!analyticsService) {
            initialize();
        }
        state.value.isEnabled = true;
    };

    /**
     * Disable analytics tracking
     */
    const disableTracking = (): void => {
        state.value.isEnabled = false;
        if (analyticsService) {
            analyticsService.destroy();
            analyticsService = null;
        }
    };

    /**
     * Auto-track cart operations
     */
    const autoTrackCart = (cartRef: any) => {
        // This would watch cart changes and automatically track events
        // Implementation depends on specific requirements
        return () => {
            // Cleanup function
        };
    };

    // Lifecycle hooks
    onMounted(() => {
        // Auto-initialize if not already done
        if (!analyticsService && config.enableTracking !== false) {
            initialize();
        }
    });

    onUnmounted(() => {
        // Cleanup analytics service
        if (analyticsService) {
            analyticsService.destroy();
        }
    });

    return {
        // State
        state: state.value,
        isTracking,
        sessionMetrics,

        // Methods
        initialize,
        trackAddToCart,
        trackUpdateQuantity,
        trackRemoveFromCart,
        trackClearCart,
        trackCartView,
        trackCheckoutStart,
        trackCartAbandon,
        getAnalyticsSummary,
        enableTracking,
        disableTracking,
        autoTrackCart,
    };
}
