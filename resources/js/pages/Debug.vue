<script setup lang="ts">
import Navbar from '@/components/navigation/Navbar.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const sharedData = computed(() => page.props.shared || {});

// Debug information
const debugInfo = computed(() => ({
    hasSharedData: !!page.props.shared,
    hasCategories: !!(page.props.categories),
    hasFeaturedCategories: !!(page.props.featured_categories),
    categoriesCount: page.props.categories?.length || 0,
    featuredCategoriesCount: page.props.featured_categories?.length || 0,
    categories: page.props.categories || [],
    featuredCategories: page.props.featured_categories || [],
    sharedDataKeys: Object.keys(page.props.shared || {}),
    allProps: Object.keys(page.props || {}),
}));
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="Debug - Category Migration">
            <meta name="description" content="Debug page for testing category migration" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <div class="container mx-auto max-w-4xl px-4 py-8">
            <h1 class="mb-8 text-3xl font-bold text-gray-900">Phase 1 Debug Page</h1>

            <div class="space-y-8">
                <!-- Shared Data Status -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Shared Data Status</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center space-x-2">
                            <div :class="debugInfo.hasSharedData ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                 class="rounded-full px-2 py-1 text-xs font-medium">
                                {{ debugInfo.hasSharedData ? '✓' : '✗' }}
                            </div>
                            <span class="text-sm text-gray-600">Has Shared Data</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div :class="debugInfo.hasCategories ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                 class="rounded-full px-2 py-1 text-xs font-medium">
                                {{ debugInfo.hasCategories ? '✓' : '✗' }}
                            </div>
                            <span class="text-sm text-gray-600">Has Categories</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div :class="debugInfo.hasFeaturedCategories ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                 class="rounded-full px-2 py-1 text-xs font-medium">
                                {{ debugInfo.hasFeaturedCategories ? '✓' : '✗' }}
                            </div>
                            <span class="text-sm text-gray-600">Has Featured Categories</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <div class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                                {{ debugInfo.categoriesCount }}
                            </div>
                            <span class="text-sm text-gray-600">Categories Count</span>
                        </div>
                    </div>
                </div>

                <!-- Debug Information -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Debug Information</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-medium text-gray-900">All Props Keys:</h3>
                            <p class="text-sm text-gray-600">{{ debugInfo.allProps.join(', ') }}</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Shared Data Keys:</h3>
                            <p class="text-sm text-gray-600">{{ debugInfo.sharedDataKeys.join(', ') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Categories List -->
                <div v-if="debugInfo.hasCategories" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Categories ({{ debugInfo.categoriesCount }})</h2>
                    <div class="space-y-3">
                        <div v-for="category in debugInfo.categories" :key="category.id"
                             class="rounded border border-gray-100 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ category.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ category.slug }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">{{ category.product_count || 0 }} products</div>
                                    <div v-if="category.children && category.children.length > 0"
                                         class="text-xs text-blue-600">
                                        {{ category.children.length }} subcategories
                                    </div>
                                </div>
                            </div>

                            <!-- Subcategories -->
                            <div v-if="category.children && category.children.length > 0"
                                 class="mt-3 ml-4 space-y-1">
                                <div v-for="child in category.children" :key="child.id"
                                     class="flex items-center justify-between rounded bg-gray-50 px-3 py-1">
                                    <span class="text-sm text-gray-700">{{ child.name }}</span>
                                    <span class="text-xs text-gray-500">{{ child.product_count || 0 }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Categories List -->
                <div v-if="debugInfo.hasFeaturedCategories" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Featured Categories ({{ debugInfo.featuredCategoriesCount }})</h2>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div v-for="category in debugInfo.featuredCategories" :key="category.id"
                             class="rounded border border-gray-100 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ category.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ category.description }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">{{ category.product_count || 0 }} products</div>
                                    <div class="text-xs text-blue-600">{{ category.icon || 'package' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raw Data (for debugging) -->
                <details class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <summary class="cursor-pointer text-xl font-semibold text-gray-900">Raw Shared Data</summary>
                    <pre class="mt-4 overflow-auto rounded bg-gray-100 p-4 text-xs">{{ JSON.stringify(sharedData, null, 2) }}</pre>
                </details>

                <!-- Test Links -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">Test Navigation</h2>
                    <div class="space-y-2">
                        <a href="/" class="block text-blue-600 hover:text-blue-800">← Back to Home</a>
                        <a href="/products" class="block text-blue-600 hover:text-blue-800">View Products Page</a>
                        <a href="/api/categories" class="block text-orange-600 hover:text-orange-800">Test Deprecated Categories API</a>
                        <a href="/api/categories/featured" class="block text-orange-600 hover:text-orange-800">Test Deprecated Featured API</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
