<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { Product } from '@/types/product';
import { Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Image, MoreHorizontal, Package, Trash2 } from 'lucide-vue-next';

interface Props {
    products: Product[];
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
});

// Methods
const deleteProduct = (product: Product) => {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
        router.delete(route('admin.products.destroy', product.slug));
    }
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

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price / 100);
};
</script>

<template>
    <!-- Mobile Card View -->
    <div class="block sm:hidden">
        <div class="space-y-4">
            <!-- Loading State -->
            <div v-if="loading" class="py-8 text-center">
                <div class="flex items-center justify-center space-x-2">
                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 border-primary"></div>
                    <span class="text-muted-foreground">Loading products...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="products.length === 0" class="py-8 text-center">
                <div class="flex flex-col items-center space-y-2">
                    <Package class="h-8 w-8 text-muted-foreground" />
                    <p class="text-muted-foreground">No products found</p>
                </div>
            </div>

            <!-- Product Cards -->
            <div v-for="product in products" :key="product.id" v-else class="rounded-lg border bg-card p-4">
                <div class="flex items-start space-x-4">
                    <!-- Product Image -->
                    <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted">
                        <Image v-if="!product.primaryImage" class="h-6 w-6 text-muted-foreground" />
                        <img
                            v-else
                            :src="product.primaryImage.image_path"
                            :alt="product.primaryImage.alt_text || product.name"
                            class="h-16 w-16 rounded-lg object-cover"
                        />
                    </div>

                    <!-- Product Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-medium text-foreground">{{ product.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ product.sku }}</p>
                                <p v-if="product.brand" class="text-sm text-muted-foreground">{{ product.brand.name }}</p>
                            </div>

                            <!-- Actions -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('admin.products.show', product.slug)" class="flex items-center">
                                            <Eye class="mr-2 h-4 w-4" />
                                            View
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('admin.products.edit', product.slug)" class="flex items-center">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="deleteProduct(product)" class="flex items-center text-destructive">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Price and Status -->
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="font-medium text-foreground">{{ formatPrice(product.price) }}</span>
                                <Badge :variant="getStockStatusBadgeVariant(product.stock_status)">
                                    {{ product.stock_status.replace('_', ' ') }}
                                </Badge>
                            </div>
                            <Badge :variant="getStatusBadgeVariant(product.status)">
                                {{ product.status }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden overflow-x-auto sm:block">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-16"></TableHead>
                    <TableHead>Product</TableHead>
                    <TableHead class="hidden md:table-cell">SKU</TableHead>
                    <TableHead class="hidden lg:table-cell">Brand</TableHead>
                    <TableHead>Price</TableHead>
                    <TableHead class="hidden md:table-cell">Stock</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="w-16"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <!-- Loading State -->
                <TableRow v-if="loading">
                    <TableCell colspan="8" class="py-8 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <div class="h-4 w-4 animate-spin rounded-full border-b-2 border-primary"></div>
                            <span class="text-muted-foreground">Loading products...</span>
                        </div>
                    </TableCell>
                </TableRow>

                <!-- Empty State -->
                <TableRow v-else-if="products.length === 0">
                    <TableCell colspan="8" class="py-8 text-center">
                        <div class="flex flex-col items-center space-y-2">
                            <Package class="h-8 w-8 text-muted-foreground" />
                            <p class="text-muted-foreground">No products found</p>
                        </div>
                    </TableCell>
                </TableRow>

                <!-- Product Rows -->
                <TableRow v-for="product in products" :key="product.id" v-else>
                    <!-- Product Image -->
                    <TableCell>
                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-muted">
                            <Image v-if="!product.primaryImage" class="h-6 w-6 text-muted-foreground" />
                            <img
                                v-else
                                :src="product.primaryImage.image_path"
                                :alt="product.primaryImage.alt_text || product.name"
                                class="h-12 w-12 rounded-lg object-cover"
                            />
                        </div>
                    </TableCell>

                    <!-- Product Info -->
                    <TableCell>
                        <div>
                            <div class="text-sm font-medium sm:text-base">{{ product.name }}</div>
                            <div v-if="product.short_description" class="line-clamp-1 hidden text-xs text-muted-foreground sm:block sm:text-sm">
                                {{ product.short_description }}
                            </div>
                        </div>
                    </TableCell>

                    <!-- SKU -->
                    <TableCell class="hidden md:table-cell">
                        <code v-if="product.sku" class="rounded bg-muted px-2 py-1 text-sm">
                            {{ product.sku }}
                        </code>
                        <span v-else class="text-sm text-muted-foreground">N/A</span>
                    </TableCell>

                    <!-- Brand -->
                    <TableCell class="hidden lg:table-cell">
                        <span v-if="product.brand">{{ product.brand.name }}</span>
                        <span v-else class="text-muted-foreground">N/A</span>
                    </TableCell>

                    <!-- Price -->
                    <TableCell>
                        <div class="space-y-1">
                            <div class="font-medium">{{ formatPrice(product.price) }}</div>
                            <div v-if="product.compare_price" class="text-sm text-muted-foreground line-through">
                                {{ formatPrice(product.compare_price) }}
                            </div>
                        </div>
                    </TableCell>

                    <!-- Stock -->
                    <TableCell class="hidden md:table-cell">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">{{ product.stock_quantity }}</div>
                            <Badge :variant="getStockStatusBadgeVariant(product.stock_status)" class="text-xs">
                                {{ product.stock_status.replace('_', ' ') }}
                            </Badge>
                        </div>
                    </TableCell>

                    <!-- Status -->
                    <TableCell>
                        <div class="space-y-1">
                            <Badge :variant="getStatusBadgeVariant(product.status)">
                                {{ product.status }}
                            </Badge>
                            <div v-if="product.is_featured || product.is_on_sale" class="flex space-x-1">
                                <Badge v-if="product.is_featured" variant="outline" class="text-xs"> Featured </Badge>
                                <Badge v-if="product.is_on_sale" variant="outline" class="text-xs"> Sale </Badge>
                            </div>
                        </div>
                    </TableCell>

                    <!-- Actions -->
                    <TableCell>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child>
                                    <Link :href="route('admin.products.show', product.slug)" class="flex items-center">
                                        <Eye class="mr-2 h-4 w-4" />
                                        View
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link :href="route('admin.products.edit', product.slug)" class="flex items-center">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="deleteProduct(product)" class="text-destructive focus:text-destructive">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
