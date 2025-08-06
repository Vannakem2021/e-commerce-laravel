/**
 * Cart Persistence Composable
 * 
 * Vue composable for managing cart persistence with automatic
 * save/load functionality and session recovery.
 */

import { ref, watch, onMounted, onUnmounted } from 'vue';
import { CartPersistenceService, type PersistedCartData } from '@/services/cartPersistence';
import type { Cart, CartSummary, CartPersistenceOptions } from '@/types/cart';

export interface CartPersistenceState {
    isLoading: boolean;
    hasPersistedData: boolean;
    lastSaved: Date | null;
    sessionId: string | null;
    storageInfo: {
        hasLocalStorage: boolean;
        hasSessionStorage: boolean;
        localStorageSize: number;
        sessionStorageSize: number;
    };
}

export function useCartPersistence(options: Partial<CartPersistenceOptions> = {}) {
    // State
    const state = ref<CartPersistenceState>({
        isLoading: false,
        hasPersistedData: false,
        lastSaved: null,
        sessionId: null,
        storageInfo: {
            hasLocalStorage: false,
            hasSessionStorage: false,
            localStorageSize: 0,
            sessionStorageSize: 0,
        },
    });

    // Service instance
    const persistenceService = new CartPersistenceService(options);

    // Auto-save timer
    let autoSaveTimer: NodeJS.Timeout | null = null;
    const AUTO_SAVE_DELAY = 1000; // 1 second debounce

    /**
     * Initialize persistence system
     */
    const initialize = (): PersistedCartData | null => {
        state.value.isLoading = true;
        
        try {
            // Update storage info
            state.value.storageInfo = persistenceService.getStorageInfo();
            
            // Load persisted data
            const persistedData = persistenceService.loadCart();
            
            if (persistedData) {
                state.value.hasPersistedData = true;
                state.value.sessionId = persistedData.sessionId;
                state.value.lastSaved = new Date(persistedData.timestamp);
            }
            
            return persistedData;
        } catch (error) {
            console.warn('Failed to initialize cart persistence:', error);
            return null;
        } finally {
            state.value.isLoading = false;
        }
    };

    /**
     * Save cart data with debouncing
     */
    const saveCart = (cart: Cart | null, summary?: CartSummary | null): Promise<boolean> => {
        return new Promise((resolve) => {
            // Clear existing timer
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
            }

            // Set new timer for debounced save
            autoSaveTimer = setTimeout(() => {
                try {
                    const success = persistenceService.saveCart(cart, summary);
                    
                    if (success) {
                        state.value.lastSaved = new Date();
                        state.value.hasPersistedData = cart !== null;
                        
                        // Update storage info
                        state.value.storageInfo = persistenceService.getStorageInfo();
                    }
                    
                    resolve(success);
                } catch (error) {
                    console.warn('Failed to save cart:', error);
                    resolve(false);
                }
            }, AUTO_SAVE_DELAY);
        });
    };

    /**
     * Save cart immediately (no debouncing)
     */
    const saveCartImmediate = (cart: Cart | null, summary?: CartSummary | null): boolean => {
        try {
            const success = persistenceService.saveCart(cart, summary);
            
            if (success) {
                state.value.lastSaved = new Date();
                state.value.hasPersistedData = cart !== null;
                state.value.storageInfo = persistenceService.getStorageInfo();
            }
            
            return success;
        } catch (error) {
            console.warn('Failed to save cart immediately:', error);
            return false;
        }
    };

    /**
     * Load cart data from storage
     */
    const loadCart = (): PersistedCartData | null => {
        try {
            return persistenceService.loadCart();
        } catch (error) {
            console.warn('Failed to load cart:', error);
            return null;
        }
    };

    /**
     * Clear all persisted cart data
     */
    const clearPersistedData = (): void => {
        try {
            persistenceService.clearStorage();
            state.value.hasPersistedData = false;
            state.value.lastSaved = null;
            state.value.sessionId = null;
            state.value.storageInfo = persistenceService.getStorageInfo();
        } catch (error) {
            console.warn('Failed to clear persisted data:', error);
        }
    };

    /**
     * Watch for cart changes and auto-save
     */
    const watchCart = (cartRef: any, summaryRef?: any) => {
        // Watch cart changes
        const stopCartWatcher = watch(
            cartRef,
            (newCart) => {
                if (newCart !== null) {
                    saveCart(newCart, summaryRef?.value);
                }
            },
            { deep: true }
        );

        // Watch summary changes if provided
        let stopSummaryWatcher: (() => void) | null = null;
        if (summaryRef) {
            stopSummaryWatcher = watch(
                summaryRef,
                (newSummary) => {
                    saveCart(cartRef.value, newSummary);
                },
                { deep: true }
            );
        }

        return () => {
            stopCartWatcher();
            if (stopSummaryWatcher) {
                stopSummaryWatcher();
            }
        };
    };

    /**
     * Handle page visibility changes for saving
     */
    const handleVisibilityChange = () => {
        if (document.visibilityState === 'hidden') {
            // Page is being hidden, save immediately
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = null;
            }
        }
    };

    /**
     * Handle beforeunload for final save
     */
    const handleBeforeUnload = () => {
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = null;
        }
    };

    // Lifecycle hooks
    onMounted(() => {
        // Add event listeners for saving on page hide/unload
        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('beforeunload', handleBeforeUnload);
    });

    onUnmounted(() => {
        // Clean up timers and event listeners
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
        }
        
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        window.removeEventListener('beforeunload', handleBeforeUnload);
    });

    return {
        // State
        state: state.value,
        
        // Methods
        initialize,
        saveCart,
        saveCartImmediate,
        loadCart,
        clearPersistedData,
        watchCart,
        
        // Computed
        isLoading: () => state.value.isLoading,
        hasPersistedData: () => state.value.hasPersistedData,
        lastSaved: () => state.value.lastSaved,
        storageInfo: () => state.value.storageInfo,
    };
}
