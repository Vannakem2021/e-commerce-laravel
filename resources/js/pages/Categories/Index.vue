<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';

import { type Category } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, FolderOpen, Package } from 'lucide-vue-next';

interface Props {
    categories: Category[];
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

// Helper function to get category color
const getCategoryColor = (slug: string) => {
    const colorMap: Record<string, string> = {
        smartphones: 'from-blue-500 to-blue-600',
        laptops: 'from-purple-500 to-purple-600',
        tablets: 'from-green-500 to-green-600',
        headphones: 'from-red-500 to-red-600',
        cameras: 'from-yellow-500 to-yellow-600',
        gaming: 'from-pink-500 to-pink-600',
        accessories: 'from-indigo-500 to-indigo-600',
        wearables: 'from-teal-500 to-teal-600',
        audio: 'from-orange-500 to-orange-600',
        computers: 'from-gray-500 to-gray-600',
    };
    return colorMap[slug] || 'from-gray-500 to-gray-600';
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head title="Categories - Electronics Store">
            <meta name="description" content="Browse all product categories in our electronics store" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="mb-4 text-4xl font-bold text-gray-900">Product Categories</h1>
                <p class="text-lg text-gray-600">Explore our wide range of electronics and tech products</p>
            </div>

            <!-- Breadcrumb -->
            <nav class="mb-8 flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <Link href="/" class="text-gray-500 hover:text-gray-700">Home</Link>
                    </li>
                    <li>
                        <span class="text-gray-400">/</span>
                    </li>
                    <li>
                        <span class="font-medium text-gray-900">Categories</span>
                    </li>
                </ol>
            </nav>

            <!-- Categories Grid -->
            <div v-if="categories.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
                >
                    <!-- Background Gradient -->
                    <div
                        class="absolute inset-0 bg-gradient-to-br opacity-5 transition-opacity duration-300 group-hover:opacity-10"
                        :class="getCategoryColor(category.slug)"
                    ></div>

                    <!-- Content -->
                    <div class="relative p-6">
                        <!-- Icon -->
                        <div class="mb-4 flex justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-2xl">
                                {{ getCategoryIcon(category.slug) }}
                            </div>
                        </div>

                        <!-- Category Info -->
                        <div class="text-center">
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ category.name }}</h3>
                            <p v-if="category.description" class="mb-4 text-sm text-gray-600">{{ category.description }}</p>
                            
                            <!-- Product Count -->
                            <div class="mb-4 flex items-center justify-center space-x-1 text-sm text-gray-500">
                                <Package class="h-4 w-4" />
                                <span>{{ category.products?.length || 0 }} products</span>
                            </div>

                            <!-- Browse Button -->
                            <Link
                                :href="`/categories/${category.slug}`"
                                class="inline-flex items-center space-x-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800"
                            >
                                <span>Browse</span>
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>

                        <!-- Subcategories -->
                        <div v-if="category.children && category.children.length > 0" class="mt-4 border-t pt-4">
                            <h4 class="mb-2 text-sm font-medium text-gray-700">Subcategories:</h4>
                            <div class="flex flex-wrap gap-1">
                                <Link
                                    v-for="child in category.children"
                                    :key="child.id"
                                    :href="`/categories/${child.slug}`"
                                    class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200"
                                >
                                    {{ child.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <FolderOpen class="mx-auto h-16 w-16 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">No categories found</h3>
                <p class="mt-2 text-gray-600">Categories will appear here once they are added.</p>
                <Link
                    href="/products"
                    class="mt-4 inline-flex items-center space-x-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                >
                    <span>Browse All Products</span>
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
