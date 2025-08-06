/**
 * Cart Persistence Service
 * 
 * Handles cart data persistence across browser sessions using localStorage
 * and sessionStorage with automatic cleanup and validation.
 */

import type { Cart, CartPersistenceOptions, CartSummary } from '@/types/cart';

export interface PersistedCartData {
    cart: Cart | null;
    summary: CartSummary | null;
    timestamp: number;
    sessionId: string;
    version: string;
}

export class CartPersistenceService {
    private static readonly STORAGE_KEY = 'ecommerce_cart';
    private static readonly SESSION_KEY = 'ecommerce_cart_session';
    private static readonly VERSION = '1.0.0';
    private static readonly DEFAULT_EXPIRATION_HOURS = 24;

    private options: CartPersistenceOptions;

    constructor(options: Partial<CartPersistenceOptions> = {}) {
        this.options = {
            enableLocalStorage: true,
            enableSessionStorage: true,
            storageKey: CartPersistenceService.STORAGE_KEY,
            expirationHours: CartPersistenceService.DEFAULT_EXPIRATION_HOURS,
            ...options,
        };
    }

    /**
     * Save cart data to persistent storage
     */
    saveCart(cart: Cart | null, summary?: CartSummary | null): boolean {
        try {
            const data: PersistedCartData = {
                cart,
                summary: summary || null,
                timestamp: Date.now(),
                sessionId: this.getSessionId(),
                version: CartPersistenceService.VERSION,
            };

            const serializedData = JSON.stringify(data);

            // Save to localStorage for persistence across sessions
            if (this.options.enableLocalStorage && this.isStorageAvailable('localStorage')) {
                localStorage.setItem(this.options.storageKey, serializedData);
            }

            // Save to sessionStorage for current session
            if (this.options.enableSessionStorage && this.isStorageAvailable('sessionStorage')) {
                sessionStorage.setItem(this.options.storageKey, serializedData);
            }

            return true;
        } catch (error) {
            console.warn('Failed to save cart to storage:', error);
            return false;
        }
    }

    /**
     * Load cart data from persistent storage
     */
    loadCart(): PersistedCartData | null {
        try {
            // Try sessionStorage first (current session)
            let data = this.loadFromStorage('sessionStorage');
            
            // Fallback to localStorage if sessionStorage is empty
            if (!data && this.options.enableLocalStorage) {
                data = this.loadFromStorage('localStorage');
            }

            if (!data) {
                return null;
            }

            // Validate data structure and version
            if (!this.isValidCartData(data)) {
                this.clearStorage();
                return null;
            }

            // Check if data has expired
            if (this.isExpired(data)) {
                this.clearStorage();
                return null;
            }

            return data;
        } catch (error) {
            console.warn('Failed to load cart from storage:', error);
            this.clearStorage();
            return null;
        }
    }

    /**
     * Clear all cart data from storage
     */
    clearStorage(): void {
        try {
            if (this.isStorageAvailable('localStorage')) {
                localStorage.removeItem(this.options.storageKey);
            }
            if (this.isStorageAvailable('sessionStorage')) {
                sessionStorage.removeItem(this.options.storageKey);
            }
        } catch (error) {
            console.warn('Failed to clear cart storage:', error);
        }
    }

    /**
     * Get or create session ID
     */
    private getSessionId(): string {
        try {
            if (this.isStorageAvailable('sessionStorage')) {
                let sessionId = sessionStorage.getItem(CartPersistenceService.SESSION_KEY);
                if (!sessionId) {
                    sessionId = this.generateSessionId();
                    sessionStorage.setItem(CartPersistenceService.SESSION_KEY, sessionId);
                }
                return sessionId;
            }
        } catch (error) {
            console.warn('Failed to get session ID:', error);
        }
        return this.generateSessionId();
    }

    /**
     * Generate unique session ID
     */
    private generateSessionId(): string {
        return `cart_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Load data from specific storage type
     */
    private loadFromStorage(storageType: 'localStorage' | 'sessionStorage'): PersistedCartData | null {
        if (!this.isStorageAvailable(storageType)) {
            return null;
        }

        const storage = storageType === 'localStorage' ? localStorage : sessionStorage;
        const serializedData = storage.getItem(this.options.storageKey);

        if (!serializedData) {
            return null;
        }

        return JSON.parse(serializedData);
    }

    /**
     * Validate cart data structure
     */
    private isValidCartData(data: any): data is PersistedCartData {
        return (
            data &&
            typeof data === 'object' &&
            typeof data.timestamp === 'number' &&
            typeof data.sessionId === 'string' &&
            typeof data.version === 'string' &&
            (data.cart === null || (typeof data.cart === 'object' && typeof data.cart.id === 'number'))
        );
    }

    /**
     * Check if cart data has expired
     */
    private isExpired(data: PersistedCartData): boolean {
        const expirationTime = data.timestamp + (this.options.expirationHours * 60 * 60 * 1000);
        return Date.now() > expirationTime;
    }

    /**
     * Check if storage is available
     */
    private isStorageAvailable(type: 'localStorage' | 'sessionStorage'): boolean {
        try {
            const storage = type === 'localStorage' ? localStorage : sessionStorage;
            const testKey = '__storage_test__';
            storage.setItem(testKey, 'test');
            storage.removeItem(testKey);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Get storage statistics
     */
    getStorageInfo(): {
        hasLocalStorage: boolean;
        hasSessionStorage: boolean;
        localStorageSize: number;
        sessionStorageSize: number;
    } {
        return {
            hasLocalStorage: this.isStorageAvailable('localStorage'),
            hasSessionStorage: this.isStorageAvailable('sessionStorage'),
            localStorageSize: this.getStorageSize('localStorage'),
            sessionStorageSize: this.getStorageSize('sessionStorage'),
        };
    }

    /**
     * Get storage size in bytes
     */
    private getStorageSize(type: 'localStorage' | 'sessionStorage'): number {
        try {
            if (!this.isStorageAvailable(type)) {
                return 0;
            }

            const storage = type === 'localStorage' ? localStorage : sessionStorage;
            const data = storage.getItem(this.options.storageKey);
            return data ? new Blob([data]).size : 0;
        } catch (error) {
            return 0;
        }
    }
}
