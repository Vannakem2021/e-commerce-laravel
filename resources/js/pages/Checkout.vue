<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';

import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, MapPin, ShoppingBag, Truck } from 'lucide-vue-next';
import { computed } from 'vue';

interface CartItem {
    id: number;
    product_id: number;
    product_variant_id?: number;
    quantity: number;
    price: number;
    total_price: number;
    formatted_price: string;
    formatted_total: string;
    display_name: string;
    product: {
        id: number;
        name: string;
        slug: string;
        sku: string;
        primaryImage?: {
            image_path: string;
        };
        brand?: {
            name: string;
        };
    };
    variant?: {
        name: string;
    };
}

interface Cart {
    id: number;
    total_quantity: number;
    total_price: number;
    formatted_total: string;
    items: CartItem[];
}

interface Props {
    cart: Cart;
    validation_errors?: string[];
}

const props = defineProps<Props>();

// Computed properties
const subtotal = computed(() => props.cart?.formatted_total || '$0.00');
const itemCount = computed(() => props.cart?.total_quantity || 0);

// Helper function to get product image
const getImageUrl = (item: CartItem): string => {
    if (item.product.primaryImage?.image_path) {
        return `/storage/${item.product.primaryImage.image_path}`;
    }
    return '/images/placeholder-product.jpg';
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head title="Checkout - Electronics Store">
            <meta name="description" content="Complete your purchase securely" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <div class="container mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <Link href="/cart" class="flex items-center text-gray-600 hover:text-teal-600 transition-colors">
                        <ArrowLeft class="h-5 w-5 mr-2" />
                        Back to Cart
                    </Link>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
                <p class="mt-2 text-gray-600">Complete your purchase securely</p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Checkout Process -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Step 1: Shipping Information -->
                    <div class="rounded-lg border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100">
                                    <MapPin class="h-4 w-4 text-teal-600" />
                                </div>
                                <h2 class="text-lg font-semibold">Shipping Information</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600">Shipping address form will be implemented here</p>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-500">Coming soon: Address collection form</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Payment Method -->
                    <div class="rounded-lg border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <CreditCard class="h-4 w-4 text-gray-400" />
                                </div>
                                <h2 class="text-lg font-semibold text-gray-400">Payment Method</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600">Payment options will be implemented here</p>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-500">Coming soon: Stripe payment integration</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Order Review -->
                    <div class="rounded-lg border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <ShoppingBag class="h-4 w-4 text-gray-400" />
                                </div>
                                <h2 class="text-lg font-semibold text-gray-400">Order Review</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600">Final order review will be implemented here</p>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-500">Coming soon: Final order confirmation</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg border bg-white p-6 shadow-sm sticky top-8">
                        <h2 class="mb-4 text-lg font-semibold">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-6">
                            <div v-for="item in cart.items" :key="item.id" class="flex items-center gap-3">
                                <!-- Product Image -->
                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg border">
                                    <img :src="getImageUrl(item)" :alt="item.product.name" class="h-full w-full object-cover" />
                                </div>

                                <!-- Product Details -->
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.display_name }}</p>
                                    <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                                </div>

                                <!-- Price -->
                                <div class="text-sm font-medium text-gray-900">
                                    {{ item.formatted_total }}
                                </div>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="space-y-3 border-t pt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ itemCount }} items)</span>
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

                        <!-- Place Order Button (Disabled for now) -->
                        <Button class="mt-6 w-full" size="lg" disabled>
                            <Truck class="mr-2 h-4 w-4" />
                            Place Order
                        </Button>
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            Order placement will be enabled once checkout steps are implemented
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />

        <!-- Toast Notifications -->
        <Toaster position="bottom-right" />
    </div>
</template>
