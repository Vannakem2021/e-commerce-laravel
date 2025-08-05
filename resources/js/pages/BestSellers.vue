<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';
import { type Product } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart, TrendingUp } from 'lucide-vue-next';

interface Props {
    products: Product[];
}

const props = defineProps<Props>();

// Helper function to calculate discount percentage
const calculateDiscount = (originalPrice: number, currentPrice: number): number => {
    return Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
};

// Helper function to generate sales count
const generateSalesCount = (): number => {
    return Math.floor(Math.random() * 500) + 100;
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="Best Sellers - Electronics Store">
            <meta
                name="description"
                content="Shop our most popular electronics and technology products. Discover what customers love most with our best-selling items."
            />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Best Sellers Page -->
        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb Navigation -->
                <nav class="mb-8 flex items-center space-x-2 text-sm text-gray-600">
                    <Link href="/" class="font-medium hover:text-gray-800">Home</Link>
                    <span>/</span>
                    <span class="font-semibold text-gray-800">Best Sellers</span>
                </nav>

                <!-- Page Header -->
                <div class="mb-12 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <TrendingUp class="mr-3 h-8 w-8 text-teal-600" />
                        <h1 class="text-4xl font-bold text-gray-900 lg:text-5xl">Best Sellers</h1>
                    </div>
                    <p class="mt-4 text-lg font-medium text-gray-700">Our most popular products loved by thousands of customers</p>
                    <div class="mt-6 inline-flex items-center rounded-full bg-orange-100 px-4 py-2 text-sm font-semibold text-orange-800">
                        <TrendingUp class="mr-2 h-4 w-4" />
                        Ranked by customer purchases and reviews
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="(product, index) in products"
                        :key="product.id"
                        class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >
                        <!-- Ranking Badge -->
                        <div class="absolute top-3 left-3 z-10 rounded-full bg-orange-500 px-3 py-1 text-xs font-semibold text-white">
                            #{{ index + 1 }} Best Seller
                        </div>

                        <!-- Sale Badge -->
                        <div
                            v-if="product.is_on_sale && product.compare_price"
                            class="absolute top-3 right-3 z-10 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white"
                        >
                            -{{ calculateDiscount(product.compare_price, product.price) }}%
                        </div>

                        <!-- Product Image -->
                        <Link :href="`/products/${product.slug}`" class="block">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img
                                    :src="
                                        product.primaryImage?.image_path
                                            ? `/storage/${product.primaryImage.image_path}`
                                            : 'https://via.placeholder.com/400x400/e5e7eb/6b7280?text=No+Image'
                                    "
                                    :alt="product.name"
                                    class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
                                />
                            </div>
                        </Link>

                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Category -->
                            <p v-if="product.categories && product.categories.length > 0" class="mb-2 text-sm font-semibold text-teal-600">
                                {{ product.categories[0].name }}
                            </p>

                            <!-- Product Name -->
                            <Link :href="`/products/${product.slug}`">
                                <h3 class="mb-2 line-clamp-2 text-lg font-semibold text-gray-900 transition-colors hover:text-teal-600">
                                    {{ product.name }}
                                </h3>
                            </Link>

                            <!-- Sales Count -->
                            <div class="mb-3 text-sm text-gray-600">
                                <TrendingUp class="mr-1 inline h-4 w-4" />
                                {{ generateSalesCount() }}+ sold this month
                            </div>

                            <!-- Price -->
                            <div class="mb-4 flex items-center space-x-2">
                                <span class="text-xl font-bold text-gray-900">${{ (product.price / 100).toLocaleString() }}</span>
                                <span v-if="product.compare_price" class="text-sm text-gray-500 line-through">
                                    ${{ (product.compare_price / 100).toLocaleString() }}
                                </span>
                            </div>

                            <!-- Product Description -->
                            <p class="mb-4 line-clamp-2 text-sm text-gray-600">
                                {{ product.short_description || 'High-quality product with excellent features and modern design.' }}
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
                                <Link :href="`/products/${product.slug}`">
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
                        Load More Best Sellers
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
