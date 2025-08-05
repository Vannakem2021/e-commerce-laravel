<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import ProductCard from '@/components/product/ProductCard.vue';
import { Button } from '@/components/ui/button';
import { type Category, type PaginatedResponse, type Product } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FolderOpen, Package } from 'lucide-vue-next';

interface Breadcrumb {
    name: string;
    href: string | null;
}

interface Props {
    category: Category & {
        children?: Category[];
    };
    products: PaginatedResponse<Product>;
    breadcrumbs: Breadcrumb[];
}

const props = defineProps<Props>();

// Helper function to get category icon
const getCategoryIcon = (slug: string) => {
    const iconMap: Record<string, string> = {
        smartphones: '📱',
        laptops: '💻',
        tablets: '📱',
        headphones: '🎧',
        cameras: '📷',
        gaming: '🎮',
        accessories: '🔌',
        wearables: '⌚',
        audio: '🔊',
        computers: '🖥️',
    };
    return iconMap[slug] || '📦';
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`${category.name} - Electronics Store`">
            <meta name="description" :content="`Browse ${category.name} products in our electronics store`" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-8 flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                        <Link
                            v-if="breadcrumb.href"
                            :href="breadcrumb.href"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            {{ breadcrumb.name }}
                        </Link>
                        <span v-else class="font-medium text-gray-900">{{ breadcrumb.name }}</span>
                        <span v-if="index < breadcrumbs.length - 1" class="ml-2 text-gray-400">/</span>
                    </li>
                </ol>
            </nav>

            <!-- Category Header -->
            <div class="mb-8 rounded-2xl bg-white p-8 shadow-lg">
                <div class="flex items-center space-x-6">
                    <!-- Category Icon -->
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-3xl">
                        {{ getCategoryIcon(category.slug) }}
                    </div>

                    <!-- Category Info -->
                    <div class="flex-1">
                        <h1 class="mb-2 text-3xl font-bold text-gray-900">{{ category.name }}</h1>
                        <p v-if="category.description" class="mb-4 text-lg text-gray-600">{{ category.description }}</p>
                        
                        <!-- Stats -->
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center space-x-1">
                                <Package class="h-4 w-4" />
                                <span>{{ products.meta?.total || 0 }} products</span>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <Link
                        href="/categories"
                        class="flex items-center space-x-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>All Categories</span>
                    </Link>
                </div>

                <!-- Subcategories -->
                <div v-if="category.children && category.children.length > 0" class="mt-6 border-t pt-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Subcategories</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        <Link
                            v-for="child in category.children"
                            :key="child.id"
                            :href="`/categories/${child.slug}`"
                            class="group rounded-lg border border-gray-200 p-4 text-center transition-all hover:border-gray-300 hover:shadow-md"
                        >
                            <div class="mb-2 text-2xl">{{ getCategoryIcon(child.slug) }}</div>
                            <div class="text-sm font-medium text-gray-900 group-hover:text-gray-700">{{ child.name }}</div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div v-if="products.data && products.data.length > 0">
                <!-- Products Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                        variant="standard"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="products.links && products.links.length > 3" class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <Link
                            v-for="link in products.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-gray-900 text-white'
                                    : link.url
                                    ? 'text-gray-700 hover:bg-gray-100'
                                    : 'text-gray-400 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <FolderOpen class="mx-auto h-16 w-16 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">No products found</h3>
                <p class="mt-2 text-gray-600">There are no products in this category yet.</p>
                <div class="mt-6 flex justify-center space-x-4">
                    <Link
                        href="/categories"
                        class="inline-flex items-center space-x-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>Browse Categories</span>
                    </Link>
                    <Link
                        href="/products"
                        class="inline-flex items-center space-x-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        <span>All Products</span>
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
