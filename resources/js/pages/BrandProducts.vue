<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import ProductCard from '@/components/product/ProductCard.vue';
import ProductFilters from '@/components/product/ProductFilters.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

import { type Brand, type Category, type PaginatedResponse, type Product } from '@/types/product';
import { Head, Link, router } from '@inertiajs/vue3';
import { Filter, Search, ShoppingCart, Globe, ExternalLink } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    brand: Brand;
    products: PaginatedResponse<Product>;
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
        min_price?: number;
        max_price?: number;
        featured?: boolean;
        on_sale?: boolean;
        sort?: string;
    };
    priceRange: {
        min: number;
        max: number;
    };
    meta: {
        title: string;
        description: string;
    };
}

const props = defineProps<Props>();

// Reactive state
const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'default');

// Computed properties
const hasProducts = computed(() => props.products.data.length > 0);
const totalProducts = computed(() => props.products.total);

// Methods
const applySearch = () => {
    const params: Record<string, any> = { ...props.filters };

    if (searchQuery.value) {
        params.search = searchQuery.value;
    } else {
        delete params.search;
    }

    router.get(route('brands.show', props.brand.slug), params, {
        preserveState: true,
        replace: true,
    });
};

const applySorting = () => {
    const params: Record<string, any> = { ...props.filters };

    if (sortBy.value !== 'default') {
        params.sort = sortBy.value;
    } else {
        delete params.sort;
    }

    router.get(route('brands.show', props.brand.slug), params, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    router.get(route('brands.show', props.brand.slug), {}, {
        preserveState: true,
        replace: true,
    });
};

// Helper functions
const getBrandLogo = (brand: Brand) => {
    if (brand.logo) {
        return brand.logo.startsWith('http') ? brand.logo : `/storage/${brand.logo}`;
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(brand.name)}&size=200&background=f3f4f6&color=374151`;
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price / 100);
};
</script>

<template>
    <Head :title="meta.title">
        <meta name="description" :content="meta.description" />
        <meta property="og:title" :content="meta.title" />
        <meta property="og:description" :content="meta.description" />
        <meta property="og:type" content="website" />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <Navbar />

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Brand Header -->
            <div class="mb-8 rounded-lg bg-white p-6 shadow-sm">
                <div class="flex flex-col items-start gap-6 md:flex-row md:items-center">
                    <!-- Brand Logo -->
                    <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-50 p-2">
                        <img :src="getBrandLogo(brand)" :alt="brand.name" class="h-full w-full object-cover" />
                    </div>

                    <!-- Brand Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900">{{ brand.name }}</h1>
                        <p v-if="brand.description" class="mt-2 text-gray-600">{{ brand.description }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            <span class="text-sm text-gray-500">{{ totalProducts }} products</span>

                            <a
                                v-if="brand.website"
                                :href="brand.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-sm text-teal-600 hover:text-teal-500"
                            >
                                <Globe class="mr-1 h-4 w-4" />
                                Visit Website
                                <ExternalLink class="ml-1 h-3 w-3" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Sort Controls -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search products..."
                        class="pl-10"
                        @keyup.enter="applySearch"
                    />
                </div>

                <div class="flex items-center gap-4">
                    <!-- Filter Toggle -->
                    <Button
                        variant="outline"
                        @click="showFilters = !showFilters"
                        class="sm:hidden"
                    >
                        <Filter class="mr-2 h-4 w-4" />
                        Filters
                    </Button>

                    <!-- Sort -->
                    <Select v-model="sortBy" @update:model-value="applySorting">
                        <SelectTrigger class="w-48">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="default">Default</SelectItem>
                            <SelectItem value="name">Name A-Z</SelectItem>
                            <SelectItem value="price_low">Price: Low to High</SelectItem>
                            <SelectItem value="price_high">Price: High to Low</SelectItem>
                            <SelectItem value="newest">Newest First</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="flex gap-8">
                <!-- Filters Sidebar -->
                <aside
                    :class="[
                        'w-64 flex-shrink-0',
                        showFilters ? 'block' : 'hidden sm:block',
                    ]"
                >
                    <ProductFilters
                        :current-brand="brand.slug"
                        :current-category="filters.category"
                        :current-min-price="filters.min_price"
                        :current-max-price="filters.max_price"
                        :categories="categories"
                        :price-range="priceRange"
                    />
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    <div v-if="hasProducts" class="space-y-6">
                        <!-- Products Grid -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                            />
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.links" class="flex justify-center">
                            <nav class="flex items-center space-x-2">
                                <template v-for="link in products.links" :key="link.label">
                                    <component
                                        :is="link.url ? 'Link' : 'span'"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            link.active
                                                ? 'bg-teal-600 text-white'
                                                : link.url
                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border'
                                                : 'text-gray-400 cursor-not-allowed',
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>

                    <!-- No Products -->
                    <div v-else class="text-center py-12">
                        <ShoppingCart class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
                        <p class="mt-2 text-gray-500">
                            Try adjusting your search or filter criteria.
                        </p>
                        <Button @click="clearFilters" class="mt-4" variant="outline">
                            Clear Filters
                        </Button>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
