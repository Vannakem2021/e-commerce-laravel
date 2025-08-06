<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import ShippingForm from '@/components/checkout/ShippingForm.vue';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';

import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, MapPin, ShoppingBag, Truck } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

// State for shipping address
const shippingAddress = ref(null);

// Handle address saved
const onAddressSaved = (address: any) => {
    shippingAddress.value = address;
    console.log('Address saved:', address);
    // Here you can proceed to next step or update UI
};
</script>

<template>
    <div class="min-h-screen bg-background">
        <Head title="Checkout - Electronics Store">
            <meta name="description" content="Complete your purchase securely" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <div class="checkout-container">
            <!-- Page Header -->
            <div class="checkout-header">
                <div class="flex items-center gap-4 mb-6">
                    <Link href="/cart" class="checkout-back-link">
                        <ArrowLeft class="h-5 w-5 mr-2" />
                        Back to Cart
                    </Link>
                </div>
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">Checkout</h1>
                    <p class="text-muted-foreground">Complete your purchase securely</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Checkout Process -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Step 1: Shipping Information -->
                    <div class="checkout-section">
                        <div class="checkout-section-header">
                            <div class="checkout-progress">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full checkout-step-active">
                                    <MapPin class="h-5 w-5" />
                                </div>
                                <div class="space-y-1">
                                    <h2 class="text-lg font-semibold">Shipping Information</h2>
                                    <p class="text-sm text-muted-foreground">Enter your delivery address</p>
                                </div>
                            </div>
                        </div>
                        <div class="checkout-section-content">
                            <ShippingForm
                                @saved="onAddressSaved"
                                @cancel="() => {}"
                                :type="'shipping'"
                            />
                        </div>
                    </div>

                    <!-- Step 2: Payment Method -->
                    <div class="checkout-section">
                        <div class="checkout-section-header">
                            <div class="checkout-progress">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full checkout-step-inactive">
                                    <CreditCard class="h-5 w-5" />
                                </div>
                                <div class="space-y-1">
                                    <h2 class="text-lg font-semibold text-muted-foreground">Payment Method</h2>
                                    <p class="text-sm text-muted-foreground">Choose your payment option</p>
                                </div>
                            </div>
                        </div>
                        <div class="checkout-section-content">
                            <div class="space-y-4">
                                <p class="text-muted-foreground">Payment options will be implemented here</p>
                                <div class="p-4 bg-muted rounded-lg">
                                    <p class="text-sm text-muted-foreground">Coming soon: Stripe payment integration</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Order Review -->
                    <div class="checkout-section">
                        <div class="checkout-section-header">
                            <div class="checkout-progress">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full checkout-step-inactive">
                                    <ShoppingBag class="h-5 w-5" />
                                </div>
                                <div class="space-y-1">
                                    <h2 class="text-lg font-semibold text-muted-foreground">Order Review</h2>
                                    <p class="text-sm text-muted-foreground">Review your order details</p>
                                </div>
                            </div>
                        </div>
                        <div class="checkout-section-content">
                            <div class="space-y-4">
                                <p class="text-muted-foreground">Final order review will be implemented here</p>
                                <div class="p-4 bg-muted rounded-lg">
                                    <p class="text-sm text-muted-foreground">Coming soon: Final order confirmation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="checkout-summary">
                        <h2 class="mb-6 text-lg font-semibold">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-6">
                            <div v-for="item in cart.items" :key="item.id" class="flex items-center gap-4">
                                <!-- Product Image -->
                                <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg border">
                                    <img :src="getImageUrl(item)" :alt="item.product.name" class="h-full w-full object-cover" />
                                </div>

                                <!-- Product Details -->
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium truncate">{{ item.display_name }}</p>
                                    <p class="text-sm text-muted-foreground">Qty: {{ item.quantity }}</p>
                                </div>

                                <!-- Price -->
                                <div class="text-sm font-medium">
                                    {{ item.formatted_total }}
                                </div>
                            </div>
                        </div>

                        <!-- Order Totals -->
                        <div class="space-y-4 border-t pt-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal ({{ itemCount }} items)</span>
                                <span class="font-medium">{{ subtotal }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Shipping</span>
                                <span class="font-medium text-muted-foreground">Calculated at checkout</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Tax</span>
                                <span class="font-medium text-muted-foreground">Calculated at checkout</span>
                            </div>

                            <div class="border-t pt-4">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Total</span>
                                    <span>{{ subtotal }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Place Order Button (Disabled for now) -->
                        <div class="mt-8 space-y-3">
                            <Button class="w-full" size="lg" disabled>
                                <Truck class="mr-2 h-4 w-4" />
                                Place Order
                            </Button>
                            <p class="text-xs text-muted-foreground text-center">
                                Order placement will be enabled once checkout steps are implemented
                            </p>
                        </div>
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
