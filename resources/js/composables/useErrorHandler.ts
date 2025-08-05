import { useSonnerToast } from '@/composables/useSonnerToast';
import { ref } from 'vue';

export interface ErrorDetails {
    message: string;
    code?: string;
    field?: string;
    context?: Record<string, any>;
}

export interface ErrorState {
    hasError: boolean;
    message: string;
    details?: ErrorDetails;
    isRetryable: boolean;
    retryCount: number;
}

export const useErrorHandler = () => {
    const toast = useSonnerToast();
    const errorState = ref<ErrorState>({
        hasError: false,
        message: '',
        isRetryable: false,
        retryCount: 0,
    });

    const clearError = () => {
        errorState.value = {
            hasError: false,
            message: '',
            isRetryable: false,
            retryCount: 0,
        };
    };

    // Context-specific error messages
    const contextualMessages: Record<string, Record<number, string>> = {
        cart: {
            400: 'Unable to add item to cart. Please check the product details.',
            404: 'This product is no longer available.',
            409: 'This item is already in your cart or out of stock.',
            422: 'Please select all required options before adding to cart.',
        },
        auth: {
            401: 'Please log in to access this feature.',
            403: 'Your account doesn\'t have access to this area.',
            422: 'Please check your login credentials and try again.',
        },
        product: {
            404: 'This product is no longer available.',
            422: 'Please fill in all required product information.',
        },
        profile: {
            422: 'Please check your profile information and try again.',
            403: 'You can only edit your own profile.',
        },
        admin: {
            403: 'Admin access required for this action.',
            422: 'Please check all required fields and try again.',
        }
    };

    // Get contextual error message based on operation context
    const getContextualMessage = (context: string | undefined, status: number): string | undefined => {
        if (!context) return undefined;

        // Extract the main context (e.g., "Cart add" -> "cart")
        const mainContext = context.toLowerCase().split(' ')[0];

        return contextualMessages[mainContext]?.[status];
    };

    const handleError = (error: any, context?: string): ErrorState => {
        let errorMessage = 'An unexpected error occurred';
        let isRetryable = false;
        let errorCode = '';

        // Handle different error types
        if (error instanceof Error) {
            errorMessage = error.message;
        } else if (typeof error === 'string') {
            errorMessage = error;
        } else if (error?.response) {
            // HTTP response error
            const status = error.response.status;
            const data = error.response.data;

            switch (status) {
                case 400:
                    errorMessage = data?.message || getContextualMessage(context, 400) || 'Invalid request. Please check your input and try again.';
                    break;
                case 401:
                    errorMessage = getContextualMessage(context, 401) || 'Your session has expired. Please log in again.';
                    break;
                case 403:
                    errorMessage = getContextualMessage(context, 403) || 'You don\'t have permission to perform this action.';
                    break;
                case 404:
                    errorMessage = getContextualMessage(context, 404) || 'The requested item could not be found.';
                    break;
                case 409:
                    errorMessage = data?.message || getContextualMessage(context, 409) || 'This action conflicts with existing data. Please refresh and try again.';
                    break;
                case 422:
                    errorMessage = data?.message || getContextualMessage(context, 422) || 'Please check the form data and correct any errors.';
                    break;
                case 429:
                    errorMessage = 'You\'re doing that too often. Please wait a moment before trying again.';
                    isRetryable = true;
                    break;
                case 500:
                    errorMessage = 'Something went wrong on our end. Please try again later.';
                    isRetryable = true;
                    break;
                case 502:
                    errorMessage = 'Our servers are having trouble. Please try again in a few minutes.';
                    isRetryable = true;
                    break;
                case 503:
                    errorMessage = 'We\'re temporarily down for maintenance. Please try again shortly.';
                    isRetryable = true;
                    break;
                case 504:
                    errorMessage = 'The request took too long to process. Please try again.';
                    isRetryable = true;
                    break;
                default:
                    errorMessage = data?.message || `Request failed with status ${status}`;
                    isRetryable = status >= 500;
            }
            errorCode = status.toString();
        } else if (error?.message) {
            errorMessage = error.message;
        }

        // Add context to error message if provided
        if (context) {
            errorMessage = `${context}: ${errorMessage}`;
        }

        const newErrorState: ErrorState = {
            hasError: true,
            message: errorMessage,
            details: {
                message: errorMessage,
                code: errorCode,
                context: context ? { operation: context } : undefined,
            },
            isRetryable,
            retryCount: errorState.value.retryCount,
        };

        errorState.value = newErrorState;
        return newErrorState;
    };

    const handleCartError = (error: any, operation: string): ErrorState => {
        const context = `Cart ${operation}`;
        const errorState = handleError(error, context);
        
        // Show toast notification for cart errors
        if (errorState.isRetryable) {
            toast.warning('Cart Issue', { description: errorState.message });
        } else {
            toast.error('Cart Error', { description: errorState.message });
        }

        return errorState;
    };

    const handleNetworkError = (error: any): ErrorState => {
        let message = 'Network error. Please check your connection and try again.';
        
        if (error?.code === 'NETWORK_ERROR' || error?.message?.includes('fetch')) {
            message = 'Unable to connect. Please check your internet connection.';
        }

        const errorState = handleError(error, 'Network');
        errorState.isRetryable = true;
        
        toast.error('Connection Error', { description: message });
        return errorState;
    };

    const handleValidationError = (errors: Record<string, string[]>): ErrorState => {
        const firstField = Object.keys(errors)[0];
        const firstError = errors[firstField]?.[0] || 'Validation failed';
        
        const errorState: ErrorState = {
            hasError: true,
            message: firstError,
            details: {
                message: firstError,
                field: firstField,
                context: { validationErrors: errors },
            },
            isRetryable: false,
            retryCount: 0,
        };

        toast.error('Validation Error', { description: firstError });
        return errorState;
    };

    const retry = async (operation: () => Promise<any>, maxRetries: number = 3): Promise<any> => {
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                const result = await operation();
                clearError(); // Clear error on success
                return result;
            } catch (error) {
                errorState.value.retryCount = attempt;
                
                if (attempt === maxRetries) {
                    // Final attempt failed
                    handleError(error, `Failed after ${maxRetries} attempts`);
                    throw error;
                }
                
                // Wait before retrying (exponential backoff)
                const delay = Math.min(1000 * Math.pow(2, attempt - 1), 5000);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    };

    const withErrorHandling = async <T>(
        operation: () => Promise<T>,
        context?: string,
        showToast: boolean = true
    ): Promise<T | null> => {
        try {
            clearError();
            const result = await operation();
            return result;
        } catch (error) {
            const errorState = handleError(error, context);
            
            if (showToast) {
                if (errorState.isRetryable) {
                    toast.warning(errorState.message);
                } else {
                    toast.error(errorState.message);
                }
            }
            
            return null;
        }
    };

    return {
        errorState,
        clearError,
        handleError,
        handleCartError,
        handleNetworkError,
        handleValidationError,
        retry,
        withErrorHandling,
    };
};
