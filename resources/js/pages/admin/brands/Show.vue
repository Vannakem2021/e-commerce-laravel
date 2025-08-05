<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type Brand } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Edit, ExternalLink, Globe } from 'lucide-vue-next';

interface Props {
    brand: Brand;
}

const props = defineProps<Props>();

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`${brand.name} - Admin`" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ brand.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">Brand details and information</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('admin.brands.edit', brand.id)">
                        <Button>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Brand
                        </Button>
                    </Link>
                    <Link :href="route('admin.brands.index')">
                        <Button variant="outline">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Brands
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Brand Information -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Information -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Brand Information</h2>

                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.name }}</dd>
                            </div>

                            <!-- Slug -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">URL Slug</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.slug }}</dd>
                            </div>

                            <!-- Description -->
                            <div v-if="brand.description">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.description }}</dd>
                            </div>

                            <!-- Website -->
                            <div v-if="brand.website">
                                <dt class="text-sm font-medium text-gray-500">Website</dt>
                                <dd class="mt-1">
                                    <a
                                        :href="brand.website"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center text-sm text-teal-600 hover:text-teal-500"
                                    >
                                        <Globe class="mr-1 h-4 w-4" />
                                        {{ brand.website }}
                                        <ExternalLink class="ml-1 h-3 w-3" />
                                    </a>
                                </dd>
                            </div>

                            <!-- Status -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            brand.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{ brand.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.sort_order }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="brand.meta_title || brand.meta_description" class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">SEO Information</h2>

                        <div class="space-y-4">
                            <!-- Meta Title -->
                            <div v-if="brand.meta_title">
                                <dt class="text-sm font-medium text-gray-500">Meta Title</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.meta_title }}</dd>
                            </div>

                            <!-- Meta Description -->
                            <div v-if="brand.meta_description">
                                <dt class="text-sm font-medium text-gray-500">Meta Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.meta_description }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Logo -->
                    <div v-if="brand.logo" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Brand Logo</h3>
                        <div class="flex justify-center">
                            <img :src="brand.logo" :alt="brand.name" class="max-h-32 w-auto rounded border" />
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Timestamps</h3>

                        <div class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(brand.created_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(brand.updated_at) }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Statistics</h3>

                        <div class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Products</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ brand.products?.length || 0 }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div v-if="brand.products && brand.products.length > 0" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-medium text-gray-900">Products ({{ brand.products.length }})</h2>

                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="product in brand.products" :key="product.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img
                                                v-if="product.primaryImage?.image_path"
                                                :src="`/storage/${product.primaryImage.image_path}`"
                                                :alt="product.name"
                                                class="h-10 w-10 rounded object-cover"
                                            />
                                            <div v-else class="h-10 w-10 rounded bg-gray-200"></div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                            <div class="text-sm text-gray-500">{{ product.sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900">${{ (product.price / 100).toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            product.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                        ]"
                                    >
                                        {{ product.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <Link :href="route('admin.products.show', product.id)" class="text-teal-600 hover:text-teal-900"> View </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
