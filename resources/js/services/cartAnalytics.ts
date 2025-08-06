/**
 * Cart Analytics Service
 * 
 * Tracks cart-related user behavior and metrics for analytics
 * and business intelligence purposes.
 */

import type { Cart, CartItem, CartMetrics } from '@/types/cart';

export interface CartEvent {
    type: 'cart_add' | 'cart_update' | 'cart_remove' | 'cart_clear' | 'cart_view' | 'cart_abandon' | 'checkout_start';
    timestamp: number;
    sessionId: string;
    userId?: number;
    productId?: number;
    variantId?: number;
    quantity?: number;
    value?: number;
    cartTotal?: number;
    itemCount?: number;
    metadata?: Record<string, any>;
}

export interface CartSession {
    sessionId: string;
    startTime: number;
    lastActivity: number;
    events: CartEvent[];
    totalValue: number;
    itemsAdded: number;
    itemsRemoved: number;
    isAbandoned: boolean;
    checkoutStarted: boolean;
}

export interface CartAnalyticsConfig {
    enableTracking: boolean;
    sessionTimeout: number; // minutes
    batchSize: number;
    flushInterval: number; // milliseconds
    endpoint?: string;
}

export class CartAnalyticsService {
    private config: CartAnalyticsConfig;
    private currentSession: CartSession | null = null;
    private eventQueue: CartEvent[] = [];
    private flushTimer: NodeJS.Timeout | null = null;
    private sessionTimer: NodeJS.Timeout | null = null;

    constructor(config: Partial<CartAnalyticsConfig> = {}) {
        this.config = {
            enableTracking: true,
            sessionTimeout: 30, // 30 minutes
            batchSize: 10,
            flushInterval: 5000, // 5 seconds
            ...config,
        };

        if (this.config.enableTracking) {
            this.initializeSession();
            this.startFlushTimer();
        }
    }

    /**
     * Initialize analytics session
     */
    private initializeSession(): void {
        const sessionId = this.generateSessionId();
        const now = Date.now();

        this.currentSession = {
            sessionId,
            startTime: now,
            lastActivity: now,
            events: [],
            totalValue: 0,
            itemsAdded: 0,
            itemsRemoved: 0,
            isAbandoned: false,
            checkoutStarted: false,
        };

        this.resetSessionTimer();
    }

    /**
     * Generate unique session ID
     */
    private generateSessionId(): string {
        return `cart_session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Track cart event
     */
    trackEvent(event: Omit<CartEvent, 'timestamp' | 'sessionId'>): void {
        if (!this.config.enableTracking || !this.currentSession) {
            return;
        }

        const fullEvent: CartEvent = {
            ...event,
            timestamp: Date.now(),
            sessionId: this.currentSession.sessionId,
        };

        // Add to current session
        this.currentSession.events.push(fullEvent);
        this.currentSession.lastActivity = fullEvent.timestamp;

        // Update session metrics
        this.updateSessionMetrics(fullEvent);

        // Add to queue for batching
        this.eventQueue.push(fullEvent);

        // Reset session timer
        this.resetSessionTimer();

        // Flush if batch is full
        if (this.eventQueue.length >= this.config.batchSize) {
            this.flushEvents();
        }
    }

    /**
     * Track item added to cart
     */
    trackAddToCart(productId: number, quantity: number, value: number, variantId?: number, metadata?: Record<string, any>): void {
        this.trackEvent({
            type: 'cart_add',
            productId,
            variantId,
            quantity,
            value,
            metadata,
        });
    }

    /**
     * Track cart item quantity update
     */
    trackUpdateQuantity(productId: number, oldQuantity: number, newQuantity: number, value: number, variantId?: number): void {
        this.trackEvent({
            type: 'cart_update',
            productId,
            variantId,
            quantity: newQuantity,
            value,
            metadata: {
                oldQuantity,
                quantityChange: newQuantity - oldQuantity,
            },
        });
    }

    /**
     * Track item removed from cart
     */
    trackRemoveFromCart(productId: number, quantity: number, value: number, variantId?: number): void {
        this.trackEvent({
            type: 'cart_remove',
            productId,
            variantId,
            quantity,
            value,
        });
    }

    /**
     * Track cart cleared
     */
    trackClearCart(cartTotal: number, itemCount: number): void {
        this.trackEvent({
            type: 'cart_clear',
            cartTotal,
            itemCount,
        });
    }

    /**
     * Track cart viewed
     */
    trackCartView(cartTotal: number, itemCount: number): void {
        this.trackEvent({
            type: 'cart_view',
            cartTotal,
            itemCount,
        });
    }

    /**
     * Track checkout started
     */
    trackCheckoutStart(cartTotal: number, itemCount: number): void {
        if (this.currentSession) {
            this.currentSession.checkoutStarted = true;
        }

        this.trackEvent({
            type: 'checkout_start',
            cartTotal,
            itemCount,
        });
    }

    /**
     * Track cart abandonment
     */
    trackCartAbandon(): void {
        if (this.currentSession && !this.currentSession.isAbandoned) {
            this.currentSession.isAbandoned = true;
            
            this.trackEvent({
                type: 'cart_abandon',
                cartTotal: this.currentSession.totalValue,
                itemCount: this.currentSession.itemsAdded - this.currentSession.itemsRemoved,
            });
        }
    }

    /**
     * Update session metrics based on event
     */
    private updateSessionMetrics(event: CartEvent): void {
        if (!this.currentSession) return;

        switch (event.type) {
            case 'cart_add':
                this.currentSession.itemsAdded += event.quantity || 0;
                this.currentSession.totalValue += event.value || 0;
                break;
            case 'cart_remove':
                this.currentSession.itemsRemoved += event.quantity || 0;
                this.currentSession.totalValue -= event.value || 0;
                break;
            case 'cart_clear':
                this.currentSession.totalValue = 0;
                break;
        }
    }

    /**
     * Get current session metrics
     */
    getSessionMetrics(): CartMetrics | null {
        if (!this.currentSession) return null;

        return {
            totalItems: this.currentSession.itemsAdded - this.currentSession.itemsRemoved,
            totalValue: this.currentSession.totalValue,
            averageItemValue: this.currentSession.itemsAdded > 0 
                ? this.currentSession.totalValue / this.currentSession.itemsAdded 
                : 0,
            lastUpdated: new Date(this.currentSession.lastActivity).toISOString(),
            sessionId: this.currentSession.sessionId,
        };
    }

    /**
     * Start flush timer for batching events
     */
    private startFlushTimer(): void {
        if (this.flushTimer) {
            clearInterval(this.flushTimer);
        }

        this.flushTimer = setInterval(() => {
            if (this.eventQueue.length > 0) {
                this.flushEvents();
            }
        }, this.config.flushInterval);
    }

    /**
     * Reset session timeout timer
     */
    private resetSessionTimer(): void {
        if (this.sessionTimer) {
            clearTimeout(this.sessionTimer);
        }

        this.sessionTimer = setTimeout(() => {
            this.trackCartAbandon();
            this.endSession();
        }, this.config.sessionTimeout * 60 * 1000);
    }

    /**
     * Flush events to analytics endpoint
     */
    private async flushEvents(): Promise<void> {
        if (this.eventQueue.length === 0) return;

        const events = [...this.eventQueue];
        this.eventQueue = [];

        try {
            if (this.config.endpoint) {
                await fetch(this.config.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        events,
                        session: this.currentSession,
                    }),
                });
            } else {
                // Fallback: log to console in development
                console.log('Cart Analytics Events:', events);
            }
        } catch (error) {
            console.warn('Failed to send analytics events:', error);
            // Re-queue events for retry
            this.eventQueue.unshift(...events);
        }
    }

    /**
     * End current session
     */
    private endSession(): void {
        if (this.currentSession) {
            // Flush any remaining events
            this.flushEvents();
            this.currentSession = null;
        }

        if (this.sessionTimer) {
            clearTimeout(this.sessionTimer);
            this.sessionTimer = null;
        }
    }

    /**
     * Cleanup and destroy analytics service
     */
    destroy(): void {
        this.endSession();

        if (this.flushTimer) {
            clearInterval(this.flushTimer);
            this.flushTimer = null;
        }
    }
}
