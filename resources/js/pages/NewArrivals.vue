<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';
import { type FeaturedProduct } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { Clock, ShoppingCart } from 'lucide-vue-next';

// Sample new arrivals data - in a real app, this would come from props or API
const newArrivals: FeaturedProduct[] = [
    {
        id: 1,
        name: 'iPhone 15 Pro Max',
        price: 1199,
        originalPrice: 1299,
        image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=400&fit=crop&crop=center',
        category: 'Smartphones',

        isOnSale: true,
        href: '/products/iphone-15-pro-max',
    },
    {
        id: 2,
        name: 'MacBook Pro 16" M3',
        price: 2499,
        image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop&crop=center',
        category: 'Laptops',

        href: '/products/macbook-pro-16-m3',
    },
    {
        id: 3,
        name: 'Sony WH-1000XM6',
        price: 399,
        originalPrice: 449,
        image: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=400&fit=crop&crop=center',
        category: 'Audio',

        isOnSale: true,
        href: '/products/sony-wh-1000xm6',
    },
    {
        id: 4,
        name: 'Samsung Galaxy S24 Ultra',
        price: 1299,
        image: 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400&h=400&fit=crop&crop=center',
        category: 'Smartphones',

        href: '/products/samsung-galaxy-s24-ultra',
    },
    {
        id: 5,
        name: 'Dell XPS 15 OLED',
        price: 1899,
        image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=400&fit=crop&crop=center',
        category: 'Laptops',

        href: '/products/dell-xps-15-oled',
    },
    {
        id: 6,
        name: 'AirPods Pro 3',
        price: 279,
        image: 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=400&fit=crop&crop=center',
        category: 'Audio',

        href: '/products/airpods-pro-3',
    },
];

// Helper function to calculate discount percentage
const calculateDiscount = (originalPrice: number, currentPrice: number): number => {
    return Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="New Arrivals - Electronics Store">
            <meta
                name="description"
                content="Discover the latest electronics and technology products. Shop new arrivals with cutting-edge features and innovative designs."
            />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- New Arrivals Page -->
        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb Navigation -->
                <nav class="mb-8 flex items-center space-x-2 text-sm text-gray-600">
                    <Link href="/" class="font-medium hover:text-gray-800">Home</Link>
                    <span>/</span>
                    <span class="font-semibold text-gray-800">New Arrivals</span>
                </nav>

                <!-- Page Header -->
                <div class="mb-12 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <Clock class="mr-3 h-8 w-8 text-teal-600" />
                        <h1 class="text-4xl font-bold text-gray-900 lg:text-5xl">New Arrivals</h1>
                    </div>
                    <p class="mt-4 text-lg font-medium text-gray-700">Discover the latest technology and innovations just added to our collection</p>
                    <div class="mt-6 inline-flex items-center rounded-full bg-teal-100 px-4 py-2 text-sm font-semibold text-teal-800">
                        <Clock class="mr-2 h-4 w-4" />
                        Updated daily with the newest products
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="product in newArrivals"
                        :key="product.id"
                        class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >
                        <!-- New Badge -->
                        <div class="absolute top-3 left-3 z-10 rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">NEW</div>

                        <!-- Sale Badge -->
                        <div
                            v-if="product.isOnSale && product.originalPrice"
                            class="absolute top-3 right-3 z-10 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white"
                        >
                            -{{ calculateDiscount(product.originalPrice, product.price) }}%
                        </div>

                        <!-- Product Image -->
                        <Link :href="product.href" class="block">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    :src="product.image"
                                    :alt="product.name"
                                    class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
                                />
                            </div>
                        </Link>

                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Category -->
                            <p class="mb-2 text-sm font-semibold text-teal-600">
                                {{ product.category }}
                            </p>

                            <!-- Product Name -->
                            <Link :href="product.href">
                                <h3 class="mb-2 line-clamp-2 text-lg font-semibold text-gray-900 transition-colors hover:text-teal-600">
                                    {{ product.name }}
                                </h3>
                            </Link>

                            <!-- Price -->
                            <div class="mb-3 flex items-center space-x-2">
                                <span class="text-xl font-bold text-gray-900">${{ product.price.toLocaleString() }}</span>
                                <span v-if="product.originalPrice" class="text-sm text-gray-500 line-through">
                                    ${{ product.originalPrice.toLocaleString() }}
                                </span>
                            </div>

                            <!-- Product Description -->
                            <p class="mb-4 line-clamp-2 text-sm text-gray-600">
                                {{ product.description || 'High-quality product with excellent features and modern design.' }}
                            </p>

                            <!-- Action Icons -->
                            <div class="flex items-center justify-center space-x-4">
                                <!-- Add to Cart -->
                                <button
                                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-white transition-all hover:bg-gray-800"
                                    style="background-color: #111827 !important; color: white !important"
                                    title="Add to Cart"
                                >
                                    <ShoppingCart class="h-4 w-4" />
                                </button>

                                <!-- View Product -->
                                <Link :href="product.href">
                                    <button
                                        class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-gray-200 hover:text-gray-800"
                                        title="View Product"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                    </button>
                                </Link>

                                <!-- Add to Wishlist -->
                                <button
                                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-red-50 hover:text-red-500"
                                    title="Add to Wishlist"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More Button -->
                <div class="mt-12 text-center">
                    <Button
                        variant="outline"
                        size="lg"
                        class="border-gray-400 px-8 py-3 font-semibold text-gray-800 hover:bg-gray-100 hover:text-gray-900"
                    >
                        Load More Products
                    </Button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
