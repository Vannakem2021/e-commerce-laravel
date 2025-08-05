<template>
    <div v-if="hasError" class="rounded-lg border border-red-200 bg-red-50 p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <AlertCircle class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-lg font-medium text-red-800">
                    {{ title }}
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>{{ message }}</p>
                    <div v-if="showDetails && details" class="mt-3">
                        <details class="cursor-pointer">
                            <summary class="font-medium hover:text-red-800">
                                Technical Details
                            </summary>
                            <div class="mt-2 space-y-1 text-xs">
                                <div v-if="details.code">
                                    <strong>Error Code:</strong> {{ details.code }}
                                </div>
                                <div v-if="details.field">
                                    <strong>Field:</strong> {{ details.field }}
                                </div>
                                <div v-if="details.context">
                                    <strong>Context:</strong> {{ JSON.stringify(details.context, null, 2) }}
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
                <div class="mt-4 flex space-x-3">
                    <button
                        v-if="isRetryable && onRetry"
                        @click="handleRetry"
                        :disabled="isRetrying"
                        class="inline-flex items-center rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        <RefreshCw v-if="!isRetrying" class="mr-2 h-4 w-4" />
                        <div v-else class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                        {{ isRetrying ? 'Retrying...' : 'Retry' }}
                        <span v-if="retryCount > 0" class="ml-1">({{ retryCount }})</span>
                    </button>
                    <button
                        v-if="onDismiss"
                        @click="onDismiss"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-red-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        <X class="mr-2 h-4 w-4" />
                        Dismiss
                    </button>
                    <button
                        v-if="onReload"
                        @click="onReload"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-red-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Reload Page
                    </button>
                </div>
            </div>
        </div>
    </div>
    <slot v-else />
</template>

<script setup lang="ts">
import type { ErrorDetails } from '@/composables/useErrorHandler';
import { AlertCircle, RefreshCw, RotateCcw, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    hasError: boolean;
    message: string;
    title?: string;
    details?: ErrorDetails;
    isRetryable?: boolean;
    retryCount?: number;
    showDetails?: boolean;
    onRetry?: () => Promise<void> | void;
    onDismiss?: () => void;
    onReload?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Something went wrong',
    isRetryable: false,
    retryCount: 0,
    showDetails: false,
});

const isRetrying = ref(false);

const handleRetry = async () => {
    if (!props.onRetry || isRetrying.value) return;
    
    isRetrying.value = true;
    try {
        await props.onRetry();
    } catch (error) {
        console.error('Retry failed:', error);
    } finally {
        isRetrying.value = false;
    }
};
</script>
