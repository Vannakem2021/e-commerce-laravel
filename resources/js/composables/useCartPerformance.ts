/**
 * Cart Performance Optimization Composable
 * 
 * Provides performance optimizations for cart operations including
 * debouncing, throttling, lazy loading, and caching.
 */

import { ref, computed, watch, nextTick } from 'vue';
import type { Cart, CartItem } from '@/types/cart';

export interface PerformanceConfig {
    debounceDelay: number;
    throttleDelay: number;
    maxCacheSize: number;
    cacheExpiration: number; // milliseconds
    enableLazyLoading: boolean;
    batchSize: number;
}

export interface CacheEntry<T> {
    data: T;
    timestamp: number;
    hits: number;
}

export interface PerformanceMetrics {
    cacheHits: number;
    cacheMisses: number;
    operationsDebounced: number;
    operationsThrottled: number;
    averageResponseTime: number;
    lastOperationTime: number;
}

export function useCartPerformance(config: Partial<PerformanceConfig> = {}) {
    // Configuration
    const performanceConfig: PerformanceConfig = {
        debounceDelay: 300,
        throttleDelay: 1000,
        maxCacheSize: 100,
        cacheExpiration: 5 * 60 * 1000, // 5 minutes
        enableLazyLoading: true,
        batchSize: 10,
        ...config,
    };

    // State
    const cache = ref<Map<string, CacheEntry<any>>>(new Map());
    const metrics = ref<PerformanceMetrics>({
        cacheHits: 0,
        cacheMisses: 0,
        operationsDebounced: 0,
        operationsThrottled: 0,
        averageResponseTime: 0,
        lastOperationTime: 0,
    });

    // Debounce timers
    const debounceTimers = new Map<string, NodeJS.Timeout>();
    const throttleTimers = new Map<string, NodeJS.Timeout>();
    const lastThrottleCall = new Map<string, number>();

    /**
     * Debounce function calls
     */
    const debounce = <T extends (...args: any[]) => any>(
        key: string,
        fn: T,
        delay: number = performanceConfig.debounceDelay
    ): ((...args: Parameters<T>) => void) => {
        return (...args: Parameters<T>) => {
            // Clear existing timer
            const existingTimer = debounceTimers.get(key);
            if (existingTimer) {
                clearTimeout(existingTimer);
            }

            // Set new timer
            const timer = setTimeout(() => {
                fn(...args);
                debounceTimers.delete(key);
                metrics.value.operationsDebounced++;
            }, delay);

            debounceTimers.set(key, timer);
        };
    };

    /**
     * Throttle function calls
     */
    const throttle = <T extends (...args: any[]) => any>(
        key: string,
        fn: T,
        delay: number = performanceConfig.throttleDelay
    ): ((...args: Parameters<T>) => void) => {
        return (...args: Parameters<T>) => {
            const now = Date.now();
            const lastCall = lastThrottleCall.get(key) || 0;

            if (now - lastCall >= delay) {
                // Execute immediately
                fn(...args);
                lastThrottleCall.set(key, now);
                metrics.value.operationsThrottled++;
            } else {
                // Schedule for later if not already scheduled
                if (!throttleTimers.has(key)) {
                    const remainingTime = delay - (now - lastCall);
                    const timer = setTimeout(() => {
                        fn(...args);
                        lastThrottleCall.set(key, Date.now());
                        throttleTimers.delete(key);
                        metrics.value.operationsThrottled++;
                    }, remainingTime);

                    throttleTimers.set(key, timer);
                }
            }
        };
    };

    /**
     * Cache management
     */
    const setCache = <T>(key: string, data: T): void => {
        // Clean expired entries if cache is full
        if (cache.value.size >= performanceConfig.maxCacheSize) {
            cleanExpiredCache();
        }

        // If still full, remove oldest entry
        if (cache.value.size >= performanceConfig.maxCacheSize) {
            const oldestKey = Array.from(cache.value.keys())[0];
            cache.value.delete(oldestKey);
        }

        cache.value.set(key, {
            data,
            timestamp: Date.now(),
            hits: 0,
        });
    };

    const getCache = <T>(key: string): T | null => {
        const entry = cache.value.get(key);
        
        if (!entry) {
            metrics.value.cacheMisses++;
            return null;
        }

        // Check if expired
        if (Date.now() - entry.timestamp > performanceConfig.cacheExpiration) {
            cache.value.delete(key);
            metrics.value.cacheMisses++;
            return null;
        }

        // Update hit count
        entry.hits++;
        metrics.value.cacheHits++;
        return entry.data as T;
    };

    const clearCache = (pattern?: string): void => {
        if (pattern) {
            // Clear entries matching pattern
            for (const key of cache.value.keys()) {
                if (key.includes(pattern)) {
                    cache.value.delete(key);
                }
            }
        } else {
            // Clear all
            cache.value.clear();
        }
    };

    const cleanExpiredCache = (): void => {
        const now = Date.now();
        for (const [key, entry] of cache.value.entries()) {
            if (now - entry.timestamp > performanceConfig.cacheExpiration) {
                cache.value.delete(key);
            }
        }
    };

    /**
     * Batch operations for better performance
     */
    const batchOperations = <T>(
        operations: (() => Promise<T>)[],
        batchSize: number = performanceConfig.batchSize
    ): Promise<T[]> => {
        const batches: (() => Promise<T>)[][] = [];
        
        for (let i = 0; i < operations.length; i += batchSize) {
            batches.push(operations.slice(i, i + batchSize));
        }

        return batches.reduce(async (acc, batch) => {
            const results = await acc;
            const batchResults = await Promise.all(batch.map(op => op()));
            return [...results, ...batchResults];
        }, Promise.resolve([] as T[]));
    };

    /**
     * Lazy load cart items with virtualization
     */
    const createVirtualizedList = (items: CartItem[], itemHeight: number = 80) => {
        const visibleItems = ref<CartItem[]>([]);
        const scrollTop = ref(0);
        const containerHeight = ref(400);

        const totalHeight = computed(() => items.length * itemHeight);
        const visibleStart = computed(() => Math.floor(scrollTop.value / itemHeight));
        const visibleEnd = computed(() => 
            Math.min(
                items.length,
                visibleStart.value + Math.ceil(containerHeight.value / itemHeight) + 1
            )
        );

        const updateVisibleItems = () => {
            visibleItems.value = items.slice(visibleStart.value, visibleEnd.value);
        };

        watch([visibleStart, visibleEnd], updateVisibleItems, { immediate: true });

        return {
            visibleItems,
            totalHeight,
            scrollTop,
            containerHeight,
            updateVisibleItems,
        };
    };

    /**
     * Optimize cart calculations
     */
    const optimizeCartCalculations = (cart: Cart) => {
        // Memoize expensive calculations
        const calculationKey = `cart_calc_${cart.id}_${cart.items.length}`;
        const cached = getCache<{
            totalQuantity: number;
            totalPrice: number;
            formattedTotal: string;
        }>(calculationKey);

        if (cached) {
            return cached;
        }

        // Perform calculations
        const totalQuantity = cart.items.reduce((sum, item) => sum + item.quantity, 0);
        const totalPrice = cart.items.reduce((sum, item) => sum + item.total_price, 0);
        const formattedTotal = `$${(totalPrice / 100).toFixed(2)}`;

        const result = { totalQuantity, totalPrice, formattedTotal };
        setCache(calculationKey, result);

        return result;
    };

    /**
     * Performance monitoring
     */
    const measurePerformance = async <T>(
        operation: () => Promise<T>,
        operationName: string
    ): Promise<T> => {
        const startTime = performance.now();
        
        try {
            const result = await operation();
            const endTime = performance.now();
            const duration = endTime - startTime;

            // Update metrics
            metrics.value.lastOperationTime = duration;
            metrics.value.averageResponseTime = 
                (metrics.value.averageResponseTime + duration) / 2;

            console.log(`Cart operation "${operationName}" took ${duration.toFixed(2)}ms`);
            
            return result;
        } catch (error) {
            const endTime = performance.now();
            const duration = endTime - startTime;
            
            console.warn(`Cart operation "${operationName}" failed after ${duration.toFixed(2)}ms:`, error);
            throw error;
        }
    };

    /**
     * Memory optimization
     */
    const optimizeMemory = () => {
        // Clean up timers
        debounceTimers.forEach(timer => clearTimeout(timer));
        debounceTimers.clear();
        
        throttleTimers.forEach(timer => clearTimeout(timer));
        throttleTimers.clear();
        
        lastThrottleCall.clear();

        // Clean cache
        cleanExpiredCache();
    };

    /**
     * Get performance statistics
     */
    const getPerformanceStats = () => {
        return {
            ...metrics.value,
            cacheSize: cache.value.size,
            cacheHitRate: metrics.value.cacheHits / (metrics.value.cacheHits + metrics.value.cacheMisses) || 0,
            activeTimers: debounceTimers.size + throttleTimers.size,
        };
    };

    // Computed properties
    const cacheHitRate = computed(() => 
        metrics.value.cacheHits / (metrics.value.cacheHits + metrics.value.cacheMisses) || 0
    );

    return {
        // Core functions
        debounce,
        throttle,
        
        // Cache management
        setCache,
        getCache,
        clearCache,
        
        // Batch operations
        batchOperations,
        
        // Virtualization
        createVirtualizedList,
        
        // Optimization
        optimizeCartCalculations,
        measurePerformance,
        optimizeMemory,
        
        // Metrics
        metrics: metrics.value,
        getPerformanceStats,
        cacheHitRate,
    };
}
