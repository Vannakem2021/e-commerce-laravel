<template>
    <div v-if="hasErrors" class="mb-6">
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <AlertTriangle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800">
                        Cart Validation Issues
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p class="mb-2">
                            Some items in your cart have issues that need attention:
                        </p>
                        <ul class="space-y-2">
                            <li v-for="(errors, itemId) in validationErrors" :key="itemId" class="flex flex-col">
                                <div class="font-medium">
                                    {{ getItemName(Number(itemId)) }}
                                </div>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li v-for="error in errors" :key="error" class="flex items-center">
                                        <div class="mr-2 h-1 w-1 rounded-full bg-red-400"></div>
                                        {{ error }}
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="mt-4 flex space-x-3">
                        <button
                            @click="refreshCart"
                            :disabled="isRefreshing"
                            class="inline-flex items-center rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-800 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <RefreshCw v-if="!isRefreshing" class="mr-2 h-4 w-4" />
                            <div v-else class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                            {{ isRefreshing ? 'Refreshing...' : 'Refresh Cart' }}
                        </button>
                        <button
                            @click="dismissErrors"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-red-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            <X class="mr-2 h-4 w-4" />
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useCartStore, type CartValidationErrors } from '@/stores/cart';
import { AlertTriangle, RefreshCw, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    validationErrors: CartValidationErrors;
}

const props = defineProps<Props>();

const cartStore = useCartStore();
const isRefreshing = ref(false);

const hasErrors = computed(() => Object.keys(props.validationErrors).length > 0);

const getItemName = (itemId: number): string => {
    const item = cartStore.cart?.items.find(item => item.id === itemId);
    if (!item) return 'Unknown Item';
    
    let name = item.product.name;
    if (item.variant?.name) {
        name += ` (${item.variant.name})`;
    }
    return name;
};

const refreshCart = async () => {
    isRefreshing.value = true;
    try {
        await cartStore.fetchCart();
        await cartStore.validateCartItems();
    } catch (error) {
        console.error('Error refreshing cart:', error);
    } finally {
        isRefreshing.value = false;
    }
};

const dismissErrors = () => {
    cartStore.clearValidationErrors();
};
</script>
