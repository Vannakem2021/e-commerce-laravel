<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Eye, Edit, Trash2, Plus, Search, Filter, ArrowLeft, Package, Zap } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    sku: string;
    brand?: { name: string };
}

interface ProductVariant {
    id: number;
    sku: string;
    name: string;
    price: number;
    compare_price?: number;
    stock_quantity: number;
    stock_status: string;
    is_active: boolean;
    sort_order: number;
    formatted_price: string;
    effective_price_in_dollars: number;
    display_name: string;
    created_at: string;
}

interface Props {
    product: Product;
    variants: {
        data: ProductVariant[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        active?: boolean;
        stock_status?: string;
        sort?: string;
        direction?: string;
    };
    stockStatuses: Record<string, string>;
}

const props = defineProps<Props>();

// Local state
const searchQuery = ref(props.filters.search || '');
const selectedActive = ref(props.filters.active?.toString() || '');
const selectedStockStatus = ref(props.filters.stock_status || '');

// Computed
const activeOptions = [
    { value: '', label: 'All Variants' },
    { value: 'true', label: 'Active' },
    { value: 'false', label: 'Inactive' }
];

const stockStatusOptions = computed(() => [
    { value: '', label: 'All Stock Status' },
    ...Object.entries(props.stockStatuses).map(([value, label]) => ({ value, label }))
]);

// Methods
const search = () => {
    router.get(route('admin.variants.index', props.product.id), {
        search: searchQuery.value,
        active: selectedActive.value,
        stock_status: selectedStockStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedActive.value = '';
    selectedStockStatus.value = '';
    search();
};

const deleteVariant = (variant: ProductVariant) => {
    if (confirm(`Are you sure you want to delete the variant "${variant.display_name}"?`)) {
        router.delete(route('admin.variants.destroy', [props.product.id, variant.id]));
    }
};

const getStockStatusBadge = (status: string, quantity: number) => {
    if (status === 'in_stock' && quantity > 0) {
        return { variant: 'default', text: 'In Stock' };
    } else if (status === 'out_of_stock' || quantity === 0) {
        return { variant: 'destructive', text: 'Out of Stock' };
    } else if (status === 'back_order') {
        return { variant: 'secondary', text: 'Back Order' };
    }
    return { variant: 'outline', text: status };
};


</script>

<template>
    <AdminLayout>
        <Head :title="`${product.name} - Variants`" />

        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <Link :href="route('admin.products.index')">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Products
                        </Button>
                    </Link>
                    <div class="flex items-center space-x-2">
                        <Package class="h-6 w-6 text-muted-foreground" />
                        <span class="text-muted-foreground">Product:</span>
                        <Link :href="route('admin.products.show', product.id)" class="font-medium hover:underline">
                            {{ product.name }}
                        </Link>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Product Variants</h1>
                        <p class="text-muted-foreground">Manage variants for {{ product.name }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Link :href="route('admin.variants.bulk-create', product.id)">
                            <Button variant="outline">
                                <Zap class="mr-2 h-4 w-4" />
                                Bulk Create
                            </Button>
                        </Link>
                        <Link :href="route('admin.variants.create', product.id)">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Add Variant
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 rounded-lg border bg-card p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search variants..."
                                class="pl-10"
                                @keyup.enter="search"
                            />
                        </div>
                    </div>

                    <!-- Active Filter -->
                    <div>
                        <Select v-model="selectedActive">
                            <SelectTrigger>
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in activeOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Stock Status Filter -->
                    <div>
                        <Select v-model="selectedStockStatus">
                            <SelectTrigger>
                                <SelectValue placeholder="Stock status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in stockStatusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <Button @click="search" variant="outline" size="sm">
                            <Filter class="mr-2 h-4 w-4" />
                            Filter
                        </Button>
                        <Button @click="clearFilters" variant="ghost" size="sm">
                            Clear
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Variants Table -->
            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Variant</TableHead>
                            <TableHead>SKU</TableHead>
                            <TableHead>Price</TableHead>
                            <TableHead>Stock</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-24">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="variant in variants.data" :key="variant.id">
                            <TableCell>
                                <div>
                                    <div class="font-medium">{{ variant.display_name }}</div>
                                    <div class="text-sm text-muted-foreground">ID: {{ variant.id }}</div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <code class="text-sm">{{ variant.sku }}</code>
                            </TableCell>
                            <TableCell>
                                <div>
                                    <div class="font-medium">${{ variant.effective_price_in_dollars.toFixed(2) }}</div>
                                    <div v-if="variant.compare_price" class="text-sm text-muted-foreground line-through">
                                        ${{ (variant.compare_price / 100).toFixed(2) }}
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div>
                                    <div class="font-medium">{{ variant.stock_quantity }}</div>
                                    <Badge 
                                        :variant="getStockStatusBadge(variant.stock_status, variant.stock_quantity).variant"
                                        class="text-xs"
                                    >
                                        {{ getStockStatusBadge(variant.stock_status, variant.stock_quantity).text }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="variant.is_active ? 'default' : 'secondary'">
                                    {{ variant.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center space-x-1">
                                    <Link :href="route('admin.variants.edit', [product.id, variant.id])">
                                        <Button variant="ghost" size="sm">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="deleteVariant(variant)"
                                        class="text-destructive hover:text-destructive"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Empty State -->
                <div v-if="variants.data.length === 0" class="py-12 text-center">
                    <div class="text-muted-foreground">
                        <div class="text-lg font-medium">No variants found</div>
                        <div class="text-sm mb-4">Create variants to offer different options for this product.</div>
                        <Link :href="route('admin.variants.create', product.id)">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Create First Variant
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="variants.links.length > 3" class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-1">
                    <Link
                        v-for="link in variants.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm rounded-md',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground hover:bg-muted',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>
