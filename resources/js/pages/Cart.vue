<script setup lang="ts">
import CartValidationAlert from '@/components/cart/CartValidationAlert.vue';
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Toaster } from '@/components/ui/sonner';

import { useCartStore, type Cart, type CartValidationErrors } from '@/stores/cart';
import { Head, Link } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart, Trash2, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Props {
    cart: Cart;
    validation_errors?: CartValidationErrors;
    cart_summary?: any;
}

const props = defineProps<Props>();

// Store and state
const cartStore = useCartStore();
const isUpdating = ref<Record<number, boolean>>({});
const quantities = ref<Record<number, number>>({});

// Initialize cart data on mount
onMounted(async () => {
    // Use initial data from Inertia props
    const hasInitialData = cartStore.setInitialData(props.cart, props.cart_summary, props.validation_errors);

    if (!hasInitialData) {
        // Fallback: fetch if no initial data
        await cartStore.fetchCart();
    }

    // Initialize quantities from cart items
    if (cartStore.cart?.items) {
        cartStore.cart.items.forEach((item) => {
            quantities.value[item.id] = item.quantity;
        });
    }
});

// Computed
const hasItems = computed(() => cartStore.hasItems);
const subtotal = computed(() => cartStore.cartTotal);
const canCheckout = computed(() => hasItems.value && !cartStore.hasValidationErrors);

// Methods
const updateQuantity = async (itemId: number, newQuantity: number) => {
    if (newQuantity < 0) return;

    isUpdating.value[itemId] = true;

    try {
        const result = await cartStore.updateQuantity(itemId, newQuantity);

        if (result.success) {
            // Update local quantities
            if (newQuantity === 0) {
                delete quantities.value[itemId];
            } else {
                quantities.value[itemId] = newQuantity;
            }
            // Validate cart after update
            await cartStore.validateCartItems();
        }
        // Error handling is done by the cart store via toast notifications
    } catch (error) {
        // Error handling is done by the cart store via toast notifications
    } finally {
        isUpdating.value[itemId] = false;
    }
};

const removeItem = async (itemId: number) => {
    isUpdating.value[itemId] = true;

    try {
        const result = await cartStore.removeItem(itemId);

        if (result.success) {
            // Remove from local quantities
            delete quantities.value[itemId];
            // Validate cart after removal
            await cartStore.validateCartItems();
        }
        // Error handling is done by the cart store via toast notifications
    } catch (error) {
        // Error handling is done by the cart store via toast notifications
    } finally {
        isUpdating.value[itemId] = false;
    }
};

const clearCart = async () => {
    try {
        const result = await cartStore.clearCart();

        if (result.success) {
            // Clear local quantities
            quantities.value = {};
            // Clear validation errors since cart is empty
            cartStore.clearValidationErrors();
        }
        // Error handling is done by the cart store via toast notifications
    } catch (error) {
        // Error handling is done by the cart store via toast notifications
    }
};

const incrementQuantity = (itemId: number) => {
    const currentQuantity = quantities.value[itemId] || 0;
    quantities.value[itemId] = currentQuantity + 1;
    updateQuantity(itemId, quantities.value[itemId]);
};

const decrementQuantity = (itemId: number) => {
    const currentQuantity = quantities.value[itemId] || 0;
    if (currentQuantity > 1) {
        quantities.value[itemId] = currentQuantity - 1;
        updateQuantity(itemId, quantities.value[itemId]);
    }
};

const getImageUrl = (item: CartItem): string => {
    if (item.product.primaryImage?.image_path) {
        return `/storage/${item.product.primaryImage.image_path}`;
    }
    return 'https://via.placeholder.com/150x150/e5e7eb/6b7280?text=No+Image';
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="Shopping Cart - Electronics Store">
            <meta name="description" content="Review and manage items in your shopping cart" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <div class="container mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
                <p class="mt-2 text-gray-600">Review your items and proceed to checkout</p>
            </div>

            <div v-if="hasItems" class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <!-- Validation Errors -->
                    <CartValidationAlert :validation-errors="cartStore.validationErrors" />
                    <div class="rounded-lg border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Cart Items ({{ cartStore.cartCount }})</h2>
                                <Button variant="outline" size="sm" @click="clearCart">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Clear Cart
                                </Button>
                            </div>
                        </div>

                        <div class="divide-y">
                            <div v-for="item in cartStore.cart?.items || []" :key="item.id" class="flex items-center gap-4 p-6">
                                <!-- Product Image -->
                                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border">
                                    <img :src="getImageUrl(item)" :alt="item.product.name" class="h-full w-full object-cover" />
                                </div>

                                <!-- Product Details -->
                                <div class="min-w-0 flex-1">
                                    <Link :href="`/products/${item.product.slug}`" class="text-lg font-medium text-gray-900 hover:text-teal-600">
                                        {{ item.display_name }}
                                    </Link>
                                    <p v-if="item.product.brand" class="text-sm text-gray-500">
                                        {{ item.product.brand.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">SKU: {{ item.product.sku }}</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ item.formatted_price }}</p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="decrementQuantity(item.id)"
                                        :disabled="isUpdating[item.id] || quantities[item.id] <= 1"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </Button>

                                    <Input
                                        v-model.number="quantities[item.id]"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-16 text-center"
                                        @blur="updateQuantity(item.id, quantities[item.id])"
                                        :disabled="isUpdating[item.id]"
                                    />

                                    <Button variant="outline" size="sm" @click="incrementQuantity(item.id)" :disabled="isUpdating[item.id]">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>

                                <!-- Item Total -->
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">{{ item.formatted_total }}</p>
                                </div>

                                <!-- Remove Button -->
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="removeItem(item.id)"
                                    :disabled="isUpdating[item.id]"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg border bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold">Order Summary</h2>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ cartStore.cartCount }} items)</span>
                                <span class="font-medium">{{ subtotal }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">Calculated at checkout</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">Calculated at checkout</span>
                            </div>

                            <hr class="my-4" />

                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span>{{ subtotal }}</span>
                            </div>
                        </div>

                        <Link v-if="canCheckout" href="/checkout" class="mt-6 block">
                            <Button class="w-full" size="lg"> Proceed to Checkout </Button>
                        </Link>
                        <Button v-else class="mt-6 w-full" size="lg" disabled>
                            Proceed to Checkout
                        </Button>
                        <p v-if="!canCheckout && hasItems" class="mt-2 text-xs text-red-600 text-center">
                            Please resolve cart issues before proceeding to checkout
                        </p>

                        <Link href="/products" class="mt-4 block">
                            <Button variant="outline" class="w-full"> Continue Shopping </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else class="py-16 text-center">
                <ShoppingCart class="mx-auto h-24 w-24 text-gray-400" />
                <h2 class="mt-4 text-2xl font-semibold text-gray-900">Your cart is empty</h2>
                <p class="mt-2 text-gray-600">Start shopping to add items to your cart</p>
                <Link href="/products" class="mt-6 inline-block">
                    <Button size="lg"> Start Shopping </Button>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <Footer />

        <!-- Toast Notifications -->
        <Toaster position="bottom-right" />
    </div>
</template>
