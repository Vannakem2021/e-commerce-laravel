<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Product } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BarChart3, Calendar, DollarSign, Edit, Eye, Package, Tag, Truck } from 'lucide-vue-next';

interface Props {
    product: Product;
}

const props = defineProps<Props>();

// Methods
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price / 100);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'published':
            return 'default';
        case 'draft':
            return 'secondary';
        case 'archived':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStockStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'in_stock':
            return 'default';
        case 'out_of_stock':
            return 'destructive';
        case 'back_order':
            return 'secondary';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <Head :title="`${product.name} - Admin`" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.products.index')">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Products
                        </Button>
                    </Link>
                    <div class="flex items-center space-x-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <Package class="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-foreground">{{ product.name }}</h1>
                            <p class="text-muted-foreground">Product Details</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm">
                        <Eye class="mr-2 h-4 w-4" />
                        View on Store
                    </Button>
                    <Link :href="route('admin.products.edit', product.slug)">
                        <Button size="sm">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Product
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Product Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Product Overview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Product Image -->
                                <div class="space-y-4">
                                    <div class="flex aspect-square items-center justify-center rounded-lg bg-muted">
                                        <Package v-if="!product.primaryImage" class="h-16 w-16 text-muted-foreground" />
                                        <img
                                            v-else
                                            :src="product.primaryImage.image_path"
                                            :alt="product.primaryImage.alt_text || product.name"
                                            class="h-full w-full rounded-lg object-cover"
                                        />
                                    </div>
                                    <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-2">
                                        <div
                                            v-for="image in product.images.slice(0, 4)"
                                            :key="image.id"
                                            class="aspect-square overflow-hidden rounded-md bg-muted"
                                        >
                                            <img :src="image.image_path" :alt="image.alt_text || product.name" class="h-full w-full object-cover" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="space-y-4">
                                    <div>
                                        <h3 class="mb-2 text-lg font-semibold">Basic Information</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">SKU:</span>
                                                <code class="text-sm">{{ product.sku || 'N/A' }}</code>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Status:</span>
                                                <Badge :variant="getStatusBadgeVariant(product.status)">
                                                    {{ product.status }}
                                                </Badge>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Type:</span>
                                                <span class="capitalize">{{ product.product_type }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Brand:</span>
                                                <span>{{ product.brand?.name || 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="mb-2 text-lg font-semibold">Pricing</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Price:</span>
                                                <span class="font-medium">{{ formatPrice(product.price) }}</span>
                                            </div>
                                            <div v-if="product.compare_price" class="flex justify-between">
                                                <span class="text-muted-foreground">Compare Price:</span>
                                                <span class="text-muted-foreground line-through">
                                                    {{ formatPrice(product.compare_price) }}
                                                </span>
                                            </div>
                                            <div v-if="product.cost_price" class="flex justify-between">
                                                <span class="text-muted-foreground">Cost Price:</span>
                                                <span>{{ formatPrice(product.cost_price) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="mb-2 text-lg font-semibold">Inventory</h3>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Stock Quantity:</span>
                                                <span>{{ product.stock_quantity }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Stock Status:</span>
                                                <Badge :variant="getStockStatusBadgeVariant(product.stock_status)">
                                                    {{ product.stock_status.replace('_', ' ') }}
                                                </Badge>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-muted-foreground">Track Inventory:</span>
                                                <span>{{ product.track_inventory ? 'Yes' : 'No' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Product Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Product Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Tabs default-value="description" class="w-full">
                                <TabsList class="grid w-full grid-cols-3">
                                    <TabsTrigger value="description">Description</TabsTrigger>
                                    <TabsTrigger value="features">Features</TabsTrigger>
                                    <TabsTrigger value="specifications">Specifications</TabsTrigger>
                                </TabsList>

                                <TabsContent value="description" class="mt-4">
                                    <div class="space-y-4">
                                        <div v-if="product.short_description">
                                            <h4 class="mb-2 font-medium">Short Description</h4>
                                            <p class="text-muted-foreground">{{ product.short_description }}</p>
                                        </div>
                                        <div v-if="product.description">
                                            <h4 class="mb-2 font-medium">Full Description</h4>
                                            <div class="prose prose-sm max-w-none">
                                                <p class="whitespace-pre-wrap text-muted-foreground">{{ product.description }}</p>
                                            </div>
                                        </div>
                                        <div v-if="!product.short_description && !product.description">
                                            <p class="text-muted-foreground italic">No description available</p>
                                        </div>
                                    </div>
                                </TabsContent>

                                <TabsContent value="features" class="mt-4">
                                    <div v-if="product.features">
                                        <div class="prose prose-sm max-w-none">
                                            <p class="whitespace-pre-wrap text-muted-foreground">{{ product.features }}</p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p class="text-muted-foreground italic">No features listed</p>
                                    </div>
                                </TabsContent>

                                <TabsContent value="specifications" class="mt-4">
                                    <div v-if="product.specifications">
                                        <div class="prose prose-sm max-w-none">
                                            <p class="whitespace-pre-wrap text-muted-foreground">{{ product.specifications }}</p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p class="text-muted-foreground italic">No specifications available</p>
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center space-x-2">
                                <BarChart3 class="h-4 w-4" />
                                <span>Quick Stats</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Eye class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Views</span>
                                </div>
                                <span class="font-medium">0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Sales</span>
                                </div>
                                <span class="font-medium">0</span>
                            </div>
                            <!-- Reviews section removed as per user preference -->
                        </CardContent>
                    </Card>

                    <!-- Product Attributes -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Product Attributes</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <Tag class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Featured:</span>
                                    <Badge :variant="product.is_featured ? 'default' : 'secondary'">
                                        {{ product.is_featured ? 'Yes' : 'No' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Tag class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">On Sale:</span>
                                    <Badge :variant="product.is_on_sale ? 'default' : 'secondary'">
                                        {{ product.is_on_sale ? 'Yes' : 'No' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Package class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Digital:</span>
                                    <Badge :variant="product.is_digital ? 'default' : 'secondary'">
                                        {{ product.is_digital ? 'Yes' : 'No' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Truck class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Requires Shipping:</span>
                                    <Badge :variant="product.requires_shipping ? 'default' : 'secondary'">
                                        {{ product.requires_shipping ? 'Yes' : 'No' }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Categories -->
                    <Card v-if="product.categories && product.categories.length > 0">
                        <CardHeader>
                            <CardTitle>Categories</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="category in product.categories" :key="category.id" variant="outline">
                                    {{ category.name }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Physical Properties -->
                    <Card v-if="product.weight || product.length || product.width || product.height">
                        <CardHeader>
                            <CardTitle>Physical Properties</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div v-if="product.weight" class="flex justify-between">
                                <span class="text-muted-foreground">Weight:</span>
                                <span>{{ product.weight }} kg</span>
                            </div>
                            <div v-if="product.length" class="flex justify-between">
                                <span class="text-muted-foreground">Length:</span>
                                <span>{{ product.length }} cm</span>
                            </div>
                            <div v-if="product.width" class="flex justify-between">
                                <span class="text-muted-foreground">Width:</span>
                                <span>{{ product.width }} cm</span>
                            </div>
                            <div v-if="product.height" class="flex justify-between">
                                <span class="text-muted-foreground">Height:</span>
                                <span>{{ product.height }} cm</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Metadata</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Created:</span>
                                </div>
                                <span class="text-sm">{{ formatDate(product.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Updated:</span>
                                </div>
                                <span class="text-sm">{{ formatDate(product.updated_at) }}</span>
                            </div>
                            <div v-if="product.published_at" class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Published:</span>
                                </div>
                                <span class="text-sm">{{ formatDate(product.published_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
