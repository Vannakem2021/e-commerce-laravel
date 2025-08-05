<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import ProductCard from '@/components/product/ProductCard.vue';
import ProductFilters from '@/components/product/ProductFilters.vue';
import { Button } from '@/components/ui/button';

import { type Brand, type Category, type PaginatedResponse, type Product } from '@/types/product';
import { Head, router } from '@inertiajs/vue3';
import { Filter, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    products: PaginatedResponse<Product>;
    filters: {
        search?: string;
        category?: string;
        brand?: string;
        min_price?: number;
        max_price?: number;
        featured?: boolean;
        on_sale?: boolean;
        sort?: string;
    };
    brands: Brand[];
    categories: Category[];
    priceRange: {
        min: number;
        max: number;
    };
}

const props = defineProps<Props>();

// Mobile filter toggle
const isMobileFilterOpen = ref(false);

// Get page title based on filters
const pageTitle = computed(() => {
    if (props.filters.brand) {
        const brand = props.brands.find((b) => b.slug === props.filters.brand);
        return brand ? `${brand.name} Products` : 'Products';
    }
    if (props.filters.category) {
        const category = props.categories.find((c) => c.slug === props.filters.category);
        return category ? `${category.name} - Products` : 'Products';
    }
    return 'All Products';
});

// Get page description based on filters
const pageDescription = computed(() => {
    if (props.filters.brand) {
        const brand = props.brands.find((b) => b.slug === props.filters.brand);
        return brand ? `Browse ${brand.name} products with the latest technology and best prices.` : 'Browse our products.';
    }
    if (props.filters.category) {
        const category = props.categories.find((c) => c.slug === props.filters.category);
        return category ? `Browse our ${category.name} collection with the latest technology and best prices.` : 'Browse our products.';
    }
    return 'Browse our complete collection of premium electronics including smartphones, laptops, tablets, audio equipment and more.';
});

// Helper functions
const formatPrice = (price: number): string => {
    return `$${(price / 100).toFixed(2)}`;
};

const calculateDiscount = (originalPrice: number, currentPrice: number): number => {
    return Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
};

const getProductImage = (product: Product): string => {
    // Try primary image first, then first image, then placeholder
    if (product.primary_image?.image_path) {
        // If it's already a full URL (like placeholder), use it directly
        if (product.primary_image.image_path.startsWith('http')) {
            return product.primary_image.image_path;
        }
        // Otherwise, construct the storage URL
        return `/storage/${product.primary_image.image_path}`;
    }
    if (product.images?.[0]?.image_path) {
        if (product.images[0].image_path.startsWith('http')) {
            return product.images[0].image_path;
        }
        return `/storage/${product.images[0].image_path}`;
    }
    return 'https://via.placeholder.com/400x400/e5e7eb/6b7280?text=No+Image';
};

const hasDiscount = (product: Product): boolean => {
    return product.compare_price && product.compare_price > product.price;
};

// Note: This function is no longer needed as ProductCard components now handle their own cart operations
// using the global cart store. Keeping for reference but can be removed.
const addToCart = async (product: Product) => {
    console.log('This function is deprecated. ProductCard components now handle cart operations directly.');
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head :title="pageTitle">
            <meta name="description" :content="pageDescription" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <div class="container mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="mb-2 text-3xl font-bold text-gray-900">{{ pageTitle }}</h1>
                <p class="text-gray-600">{{ pageDescription }}</p>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row">
                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden">
                    <Button
                        @click="isMobileFilterOpen = !isMobileFilterOpen"
                        variant="outline"
                        class="flex w-full items-center justify-center space-x-2"
                    >
                        <Filter class="h-4 w-4" />
                        <span>Filters</span>
                    </Button>
                </div>

                <!-- Filters Sidebar -->
                <div :class="['lg:w-64 lg:flex-shrink-0', isMobileFilterOpen ? 'block' : 'hidden lg:block']">
                    <div class="lg:sticky lg:top-4">
                        <ProductFilters
                            :current-category="filters.category"
                            :current-brand="filters.brand"
                            :current-min-price="filters.min_price"
                            :current-max-price="filters.max_price"
                            :brands="brands"
                        />
                    </div>
                </div>

                <!-- Mobile Filter Overlay -->
                <div v-if="isMobileFilterOpen" class="bg-opacity-50 fixed inset-0 z-40 bg-black lg:hidden" @click="isMobileFilterOpen = false" />

                <!-- Products Grid -->
                <div class="flex-1">
                    <!-- Results Header -->
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-gray-600">Showing {{ products.from }}-{{ products.to }} of {{ products.total }} products</p>

                        <!-- Sort Dropdown -->
                        <select
                            :value="filters.sort || 'default'"
                            @change="(e) => router.get(route('products'), { ...filters, sort: e.target.value }, { preserveState: true })"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                            <option value="default">Default</option>
                            <option value="name">Name A-Z</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>

                    <!-- Products Grid -->
                    <div v-if="products.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                            variant="premium"
                            :show-quick-actions="true"
                            :show-description="true"
                            :show-brand="true"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center">
                        <div class="mx-auto max-w-md">
                            <div class="mb-4">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                                    <ShoppingCart class="h-8 w-8 text-gray-400" />
                                </div>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">No products found</h3>
                            <p class="mb-4 text-gray-600">We couldn't find any products matching your criteria. Try adjusting your filters.</p>
                            <Button @click="router.get(route('products'))" variant="outline"> Clear Filters </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="mt-8 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <Button
                                v-for="link in products.links"
                                :key="link.label"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                size="sm"
                                @click="link.url && router.get(link.url)"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
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
