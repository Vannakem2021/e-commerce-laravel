<template>
    <div v-if="hasError" class="rounded-lg border border-destructive/20 bg-destructive/5 p-6">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-destructive" />
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-destructive mb-2">
                    Cart Error
                </h3>
                <div class="text-sm text-destructive/80 mb-4">
                    <p>{{ errorMessage }}</p>
                    <p v-if="showDetails" class="mt-2 text-xs font-mono bg-destructive/10 p-2 rounded">
                        {{ errorDetails }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Button @click="retry" variant="outline" size="sm">
                        <RotateCcw class="h-4 w-4 mr-2" />
                        Try Again
                    </Button>
                    <Button @click="goToHome" variant="ghost" size="sm">
                        <Home class="h-4 w-4 mr-2" />
                        Go to Home
                    </Button>
                    <Button 
                        @click="showDetails = !showDetails" 
                        variant="ghost" 
                        size="sm"
                        class="text-xs"
                    >
                        {{ showDetails ? 'Hide' : 'Show' }} Details
                    </Button>
                </div>
            </div>
        </div>
    </div>
    <slot v-else />
</template>

<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ExclamationTriangleIcon, RotateCcw, Home } from 'lucide-vue-next';

interface Props {
    fallbackMessage?: string;
    onRetry?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    fallbackMessage: 'Something went wrong with the cart functionality.',
});

const hasError = ref(false);
const errorMessage = ref('');
const errorDetails = ref('');
const showDetails = ref(false);

// Capture errors from child components
onErrorCaptured((error: Error) => {
    hasError.value = true;
    errorMessage.value = error.message || props.fallbackMessage;
    errorDetails.value = error.stack || 'No additional details available';
    
    // Log error for debugging
    console.error('Cart Error Boundary caught error:', error);
    
    // Prevent error from propagating
    return false;
});

const retry = () => {
    hasError.value = false;
    errorMessage.value = '';
    errorDetails.value = '';
    showDetails.value = false;
    
    if (props.onRetry) {
        props.onRetry();
    } else {
        // Default retry: reload the page
        window.location.reload();
    }
};

const goToHome = () => {
    router.visit('/');
};
</script>
