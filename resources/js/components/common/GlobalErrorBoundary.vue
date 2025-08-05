<template>
    <div v-if="hasError" class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <AlertTriangle class="h-6 w-6 text-red-600" />
                </div>
                
                <h1 class="text-lg font-semibold text-gray-900 mb-2">
                    Something went wrong
                </h1>
                
                <p class="text-sm text-gray-600 mb-6">
                    We're sorry, but something unexpected happened. Please try refreshing the page or contact support if the problem persists.
                </p>

                <div class="space-y-3">
                    <button
                        @click="handleReload"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        <RotateCcw class="h-4 w-4 mr-2" />
                        Reload Page
                    </button>
                    
                    <button
                        @click="handleGoHome"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        <Home class="h-4 w-4 mr-2" />
                        Go to Homepage
                    </button>
                </div>

                <!-- Error Details (only in development) -->
                <div v-if="showDetails && errorDetails" class="mt-6 text-left">
                    <details class="cursor-pointer">
                        <summary class="text-sm font-medium text-gray-700 hover:text-gray-900">
                            Technical Details
                        </summary>
                        <div class="mt-2 p-3 bg-gray-50 rounded text-xs font-mono text-gray-600 overflow-auto max-h-40">
                            <div><strong>Error:</strong> {{ errorDetails.message }}</div>
                            <div v-if="errorDetails.componentName"><strong>Component:</strong> {{ errorDetails.componentName }}</div>
                            <div v-if="errorDetails.stack" class="mt-2">
                                <strong>Stack:</strong>
                                <pre class="whitespace-pre-wrap">{{ errorDetails.stack }}</pre>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
    
    <slot v-else />
</template>

<script setup lang="ts">
import { AlertTriangle, Home, RotateCcw } from 'lucide-vue-next';
import { onErrorCaptured, ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';

interface ErrorDetails {
    message: string;
    componentName?: string;
    stack?: string;
}

const toast = useSonnerToast();
const hasError = ref(false);
const errorDetails = ref<ErrorDetails | null>(null);
const showDetails = ref(import.meta.env.DEV); // Only show in development

// Capture errors from child components
onErrorCaptured((error, instance, info) => {
    console.error('Vue Error Boundary caught an error:', error);
    console.error('Component instance:', instance);
    console.error('Error info:', info);

    hasError.value = true;
    errorDetails.value = {
        message: error.message || 'Unknown error',
        componentName: instance?.$options.name || instance?.$options.__name || 'Unknown Component',
        stack: error.stack,
    };

    // Show toast notification
    toast.error('Application Error', {
        description: 'An unexpected error occurred. Please reload the page.'
    });

    // Prevent the error from propagating further
    return false;
});

const handleReload = () => {
    window.location.reload();
};

const handleGoHome = () => {
    window.location.href = '/';
};

// Reset error state (useful for testing)
const resetError = () => {
    hasError.value = false;
    errorDetails.value = null;
};

// Expose reset function for parent components
defineExpose({
    resetError
});
</script>
