/**
 * Cart Error Handling Composable
 * 
 * Provides standardized error handling for cart operations
 * with user-friendly feedback and recovery options.
 */

import { useSonnerToast } from '@/composables/useSonnerToast';
import { router } from '@inertiajs/vue3';

export interface CartErrorOptions {
    showToast?: boolean;
    redirectOnError?: string;
    retryCallback?: () => void;
}

export function useCartError() {
    const toast = useSonnerToast();

    /**
     * Handle cart operation errors with user feedback
     */
    const handleCartError = (
        error: Error, 
        operation: string, 
        options: CartErrorOptions = {}
    ) => {
        const {
            showToast = true,
            redirectOnError,
            retryCallback
        } = options;

        // Log error for debugging
        console.error(`Cart ${operation} error:`, error);

        // Show user-friendly toast notification
        if (showToast) {
            const errorMessage = getErrorMessage(error, operation);
            
            toast.error(`Failed to ${operation}`, {
                description: errorMessage,
                action: retryCallback ? {
                    label: 'Retry',
                    onClick: retryCallback,
                } : undefined,
            });
        }

        // Redirect if specified
        if (redirectOnError) {
            setTimeout(() => {
                router.visit(redirectOnError);
            }, 2000);
        }

        return {
            message: error.message,
            operation,
            timestamp: new Date().toISOString(),
        };
    };

    /**
     * Handle cart operation success with user feedback
     */
    const handleCartSuccess = (
        message: string, 
        description?: string,
        options: { showToast?: boolean } = {}
    ) => {
        const { showToast = true } = options;

        if (showToast) {
            toast.success(message, {
                description,
            });
        }
    };

    /**
     * Handle cart validation errors
     */
    const handleValidationErrors = (
        errors: Record<string, string[]>,
        operation: string = 'validate cart'
    ) => {
        const errorMessages = Object.values(errors).flat();
        const primaryError = errorMessages[0] || 'Validation failed';

        toast.error(`Cart validation failed`, {
            description: primaryError,
        });

        return {
            message: primaryError,
            operation,
            errors,
            timestamp: new Date().toISOString(),
        };
    };

    /**
     * Get user-friendly error message based on error type
     */
    const getErrorMessage = (error: Error, operation: string): string => {
        const message = error.message.toLowerCase();

        // Network errors
        if (message.includes('network') || message.includes('fetch')) {
            return 'Please check your internet connection and try again.';
        }

        // Authentication errors
        if (message.includes('unauthorized') || message.includes('401')) {
            return 'Please log in to continue.';
        }

        // Server errors
        if (message.includes('500') || message.includes('server')) {
            return 'Server error. Please try again in a moment.';
        }

        // Cart-specific errors
        if (message.includes('out of stock')) {
            return 'This item is currently out of stock.';
        }

        if (message.includes('quantity')) {
            return 'Invalid quantity. Please check the amount and try again.';
        }

        if (message.includes('not found')) {
            return 'Item not found. It may have been removed.';
        }

        // Default fallback
        return error.message || `An error occurred while trying to ${operation}.`;
    };

    /**
     * Create error boundary handler for cart components
     */
    const createErrorBoundary = (componentName: string) => {
        return (error: Error) => {
            return handleCartError(error, `load ${componentName}`, {
                showToast: true,
                redirectOnError: '/cart',
            });
        };
    };

    return {
        handleCartError,
        handleCartSuccess,
        handleValidationErrors,
        getErrorMessage,
        createErrorBoundary,
    };
}
