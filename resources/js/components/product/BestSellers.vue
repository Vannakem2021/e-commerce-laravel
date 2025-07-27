<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { type FeaturedProduct } from '@/types/product';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Best selling products data with Unsplash images
const bestSellerProducts = ref<FeaturedProduct[]>([
    {
        id: 1,
        name: 'iPhone 15 Pro Max',
        price: 1199,
        originalPrice: 1299,
        image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=400&fit=crop&crop=center',
        category: 'Smartphones',
        rating: 4.9,
        isOnSale: true,
        href: '/products/iphone-15-pro-max',
    },
    {
        id: 2,
        name: 'MacBook Pro 16"',
        price: 2499,
        image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop&crop=center',
        category: 'Laptops',
        rating: 4.8,
        href: '/products/macbook-pro-16',
    },
    {
        id: 3,
        name: 'Sony WH-1000XM5',
        price: 399,
        originalPrice: 449,
        image: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=400&fit=crop&crop=center',
        category: 'Audio',
        rating: 4.7,
        isOnSale: true,
        href: '/products/sony-wh-1000xm5',
    },
    {
        id: 4,
        name: 'iPad Pro 12.9"',
        price: 1099,
        image: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=400&fit=crop&crop=center',
        category: 'Tablets',
        rating: 4.6,
        href: '/products/ipad-pro-12-9',
    },
    {
        id: 5,
        name: 'Samsung Galaxy Watch',
        price: 329,
        originalPrice: 399,
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop&crop=center',
        category: 'Wearables',
        rating: 4.5,
        isOnSale: true,
        href: '/products/samsung-galaxy-watch',
    },
    {
        id: 6,
        name: 'Dell XPS 13',
        price: 1299,
        image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=400&fit=crop&crop=center',
        category: 'Laptops',
        rating: 4.4,
        href: '/products/dell-xps-13',
    },
    {
        id: 7,
        name: 'AirPods Pro 2',
        price: 249,
        originalPrice: 279,
        image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=400&fit=crop&crop=center',
        category: 'Audio',
        rating: 4.8,
        isOnSale: true,
        href: '/products/airpods-pro-2',
    },
    {
        id: 8,
        name: 'Nintendo Switch OLED',
        price: 349,
        image: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&crop=center',
        category: 'Gaming',
        rating: 4.6,
        href: '/products/nintendo-switch-oled',
    },
]);

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const calculateDiscount = (originalPrice: number, currentPrice: number): number => {
    return Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
};

const generateReviewCount = (rating: number): number => {
    // Generate realistic review counts based on rating
    const baseCount = Math.floor(rating * 200);
    return baseCount + Math.floor(Math.random() * 100);
};
</script>

<template>
    <section class="bg-gray-50 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">
                    Best
                    <span class="text-teal-600">Sellers</span>
                </h2>
                <p class="mt-4 text-lg text-gray-700">Our most popular products loved by customers</p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                <div
                    v-for="product in bestSellerProducts"
                    :key="product.id"
                    class="group relative h-full overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    <!-- Best Seller Badge -->
                    <div class="absolute top-3 left-3 z-10 rounded-full bg-orange-500 px-3 py-1 text-xs font-semibold text-white">Best Seller</div>

                    <!-- Sale Badge -->
                    <div
                        v-if="product.isOnSale && product.originalPrice"
                        class="absolute top-3 right-3 z-10 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white"
                    >
                        -{{ calculateDiscount(product.originalPrice, product.price) }}%
                    </div>

                    <!-- Product Image -->
                    <div class="aspect-square overflow-hidden">
                        <img
                            :src="product.image"
                            :alt="product.name"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        />
                    </div>

                    <!-- Product Info -->
                    <div class="p-3 sm:p-4">
                        <!-- Category -->
                        <p class="mb-2 text-sm font-medium text-teal-600">
                            {{ product.category }}
                        </p>

                        <!-- Product Name -->
                        <h3 class="mb-2 line-clamp-2 text-sm font-semibold text-gray-900 sm:text-lg">
                            {{ product.name }}
                        </h3>

                        <!-- Rating -->
                        <div class="mb-3 flex items-center">
                            <div class="flex items-center">
                                <span
                                    v-for="star in 5"
                                    :key="star"
                                    :class="star <= Math.floor(product.rating) ? 'text-yellow-400' : 'text-gray-300'"
                                    class="text-sm"
                                >
                                    ★
                                </span>
                            </div>
                            <span class="ml-2 text-sm text-gray-600"> {{ product.rating }} ({{ generateReviewCount(product.rating) }}) </span>
                        </div>

                        <!-- Price -->
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-xl font-bold text-gray-900">
                                    {{ formatPrice(product.price) }}
                                </span>
                                <span v-if="product.originalPrice" class="text-sm text-gray-500 line-through">
                                    {{ formatPrice(product.originalPrice) }}
                                </span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <Button as-child class="w-full bg-teal-600 text-black hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            <Link :href="product.href" class="flex items-center justify-center">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6m0 0h15M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"
                                    />
                                </svg>
                                Add to Cart
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- View All Products Link -->
            <div class="mt-12 text-center">
                <Link
                    href="/products"
                    class="inline-flex items-center rounded-lg bg-teal-600 px-6 py-3 text-base font-medium text-white transition-all duration-200 hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                >
                    View All Products
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Enhanced hover effects */
.group:hover .product-image {
    transform: scale(1.05);
}

/* Smooth transitions for all interactive elements */
.group * {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .group {
        max-width: 400px;
        margin: 0 auto;
    }

    .group h3 {
        font-size: 1rem;
    }

    .group .text-xl {
        font-size: 1.25rem;
    }

    .group .aspect-square {
        aspect-ratio: 1;
    }
}

/* Desktop enhancements */
@media (min-width: 1024px) {
    .group:hover {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
}
</style>
