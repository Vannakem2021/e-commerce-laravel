<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Category, Product } from '@/types/product';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, FolderOpen, Package, Trash2 } from 'lucide-vue-next';

interface Props {
    category: Category & {
        parent?: Category;
        children?: Category[];
        products?: Product[];
    };
}

const props = defineProps<Props>();

// Methods
const deleteCategory = () => {
    if (confirm(`Are you sure you want to delete "${props.category.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.categories.destroy', props.category.id), {
            onSuccess: () => {
                console.log('Category deleted successfully');
                // Redirect to categories index after successful deletion
                router.visit(route('admin.categories.index'));
            },
            onError: (errors) => {
                console.error('Deletion failed:', errors);
                alert('Failed to delete category. Check console for details.');
            },
            onFinish: () => {
                console.log('Deletion request completed');
            },
        });
    }
};

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
    <Head :title="`${category.name} - Categories - Admin`" />

    <AdminLayout>
        <div class="container mx-auto max-w-6xl px-4 py-8">
            <!-- Breadcrumb -->
            <nav class="mb-6" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-600">
                    <li>
                        <Link :href="route('admin.categories.index')" class="hover:text-gray-900">Categories</Link>
                    </li>
                    <li v-if="category.parent" class="flex items-center">
                        <span class="mx-2">/</span>
                        <Link :href="route('admin.categories.show', category.parent.id)" class="hover:text-gray-900">
                            {{ category.parent.name }}
                        </Link>
                    </li>
                    <li class="flex items-center">
                        <span class="mx-2">/</span>
                        <span class="font-medium text-gray-900">{{ category.name }}</span>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                        <FolderOpen class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">{{ category.name }}</h1>
                        <p class="text-muted-foreground">Category Details</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('admin.categories.index')" class="flex items-center space-x-2 text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Back to Categories</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Category Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Category Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <h3 class="mb-1 font-medium text-gray-900">Name</h3>
                                    <p class="text-gray-600">{{ category.name }}</p>
                                </div>
                                <div>
                                    <h3 class="mb-1 font-medium text-gray-900">Slug</h3>
                                    <code class="rounded bg-gray-100 px-2 py-1 text-sm">{{ category.slug }}</code>
                                </div>
                            </div>

                            <div v-if="category.description">
                                <h3 class="mb-1 font-medium text-gray-900">Description</h3>
                                <p class="leading-relaxed text-gray-600">{{ category.description }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <h3 class="mb-2 font-medium text-gray-900">Status</h3>
                                    <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                                <div>
                                    <h3 class="mb-1 font-medium text-gray-900">Sort Order</h3>
                                    <p class="text-gray-600">{{ category.sort_order }}</p>
                                </div>
                                <div v-if="category.parent">
                                    <h3 class="mb-1 font-medium text-gray-900">Parent Category</h3>
                                    <Link
                                        :href="route('admin.categories.show', category.parent.id)"
                                        class="font-medium text-blue-600 hover:text-blue-800"
                                    >
                                        {{ category.parent.name }}
                                    </Link>
                                </div>
                                <div v-else>
                                    <h3 class="mb-1 font-medium text-gray-900">Category Type</h3>
                                    <Badge variant="outline">Root Category</Badge>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div v-if="category.image" class="border-t pt-4">
                                <h3 class="mb-2 font-medium text-gray-900">Category Image</h3>
                                <div class="flex items-center space-x-3">
                                    <img :src="category.image" :alt="category.name" class="h-16 w-16 rounded-lg border object-cover" />
                                    <div>
                                        <p class="text-sm text-gray-600">Image URL:</p>
                                        <code class="rounded bg-gray-100 px-2 py-1 text-xs">{{ category.image }}</code>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SEO Information -->
                    <Card v-if="category.meta_title || category.meta_description">
                        <CardHeader>
                            <CardTitle>SEO Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="category.meta_title">
                                <h3 class="font-medium text-gray-900">Meta Title</h3>
                                <p class="text-gray-600">{{ category.meta_title }}</p>
                            </div>
                            <div v-if="category.meta_description">
                                <h3 class="font-medium text-gray-900">Meta Description</h3>
                                <p class="text-gray-600">{{ category.meta_description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subcategories -->
                    <Card v-if="category.children && category.children.length > 0">
                        <CardHeader>
                            <CardTitle>Subcategories ({{ category.children.length }})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div
                                    v-for="child in category.children"
                                    :key="child.id"
                                    class="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div>
                                        <h4 class="font-medium">{{ child.name }}</h4>
                                        <p class="text-sm text-gray-600">{{ child.slug }}</p>
                                    </div>
                                    <Link :href="route('admin.categories.show', child.id)" class="text-blue-600 hover:text-blue-800"> View </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Products -->
                    <Card v-if="category.products && category.products.length > 0">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Products ({{ category.products.length }})</CardTitle>
                            <Link
                                :href="route('admin.products.index', { category_id: category.id })"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                View All →
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="product in category.products.slice(0, 10)"
                                    :key="product.id"
                                    class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50"
                                >
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img
                                                v-if="product.primaryImage?.image_path"
                                                :src="product.primaryImage.image_path"
                                                :alt="product.name"
                                                class="h-12 w-12 rounded-lg border object-cover"
                                            />
                                            <div v-else class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100">
                                                <Package class="h-6 w-6 text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="truncate font-medium text-gray-900">{{ product.name }}</h4>
                                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                <span>{{ product.sku }}</span>
                                                <span v-if="product.price" class="font-medium">${{ (product.price / 100).toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <Badge :variant="product.status === 'published' ? 'default' : 'secondary'">
                                            {{ product.status }}
                                        </Badge>
                                        <Link :href="route('admin.products.show', product.id)" class="font-medium text-blue-600 hover:text-blue-800">
                                            View
                                        </Link>
                                    </div>
                                </div>
                                <div v-if="category.products.length > 10" class="border-t pt-2 text-center">
                                    <Link
                                        :href="route('admin.products.index', { category_id: category.id })"
                                        class="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View {{ category.products.length - 10 }} more products →
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Empty Products State -->
                    <Card v-else>
                        <CardHeader>
                            <CardTitle>Products (0)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="py-8 text-center">
                                <Package class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                                <h3 class="mb-2 text-lg font-medium text-gray-900">No products assigned</h3>
                                <p class="mb-4 text-gray-600">This category doesn't have any products assigned to it yet.</p>
                                <Link
                                    :href="route('admin.products.create')"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                >
                                    Add Product
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link :href="route('admin.categories.edit', category.id)" class="w-full">
                                <Button class="w-full">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Category
                                </Button>
                            </Link>

                            <!-- Add Subcategory Button -->
                            <Link :href="route('admin.categories.create', { parent_id: category.id })" class="w-full">
                                <Button variant="outline" class="w-full">
                                    <FolderOpen class="mr-2 h-4 w-4" />
                                    Add Subcategory
                                </Button>
                            </Link>

                            <!-- View Products Button -->
                            <Link :href="route('admin.products.index', { category_id: category.id })" class="w-full">
                                <Button variant="outline" class="w-full">
                                    <Package class="mr-2 h-4 w-4" />
                                    View Products
                                </Button>
                            </Link>

                            <div class="border-t pt-3">
                                <Button
                                    variant="destructive"
                                    class="w-full"
                                    @click="deleteCategory"
                                    :disabled="
                                        (category.children && category.children.length > 0) || (category.products && category.products.length > 0)
                                    "
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete Category
                                </Button>
                                <p
                                    v-if="(category.children && category.children.length > 0) || (category.products && category.products.length > 0)"
                                    class="mt-2 text-xs text-muted-foreground"
                                >
                                    Cannot delete category with subcategories or products
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Statistics -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Statistics</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Subcategories</span>
                                <span class="font-medium">{{ category.children?.length || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Products</span>
                                <span class="font-medium">{{ category.products?.length || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Created</span>
                                <span class="text-sm font-medium">{{ formatDate(category.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Updated</span>
                                <span class="text-sm font-medium">{{ formatDate(category.updated_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
