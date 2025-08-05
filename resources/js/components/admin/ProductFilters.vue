<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Brand, Category, ProductFilters as ProductFiltersType } from '@/types/product';
import { router } from '@inertiajs/vue3';
import { Filter, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Props {
    filters: ProductFiltersType;
    brands: Brand[];
    categories: Category[];
}

const props = defineProps<Props>();

// Reactive filters
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedBrand = ref(props.filters.brand_id?.toString() || 'all');
const selectedCategory = ref(props.filters.category_id?.toString() || 'all');
const selectedStockStatus = ref(props.filters.stock_status || 'all');
const priceMin = ref(props.filters.price_min?.toString() || '');
const priceMax = ref(props.filters.price_max?.toString() || '');
const isFeatured = ref(props.filters.is_featured || false);
const isOnSale = ref(props.filters.is_on_sale || false);

// Computed properties
const hasFilters = computed(() => {
    return (
        searchQuery.value ||
        (selectedStatus.value && selectedStatus.value !== 'all') ||
        (selectedBrand.value && selectedBrand.value !== 'all') ||
        (selectedCategory.value && selectedCategory.value !== 'all') ||
        (selectedStockStatus.value && selectedStockStatus.value !== 'all') ||
        priceMin.value ||
        priceMax.value ||
        isFeatured.value ||
        isOnSale.value
    );
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (searchQuery.value) count++;
    if (selectedStatus.value && selectedStatus.value !== 'all') count++;
    if (selectedBrand.value && selectedBrand.value !== 'all') count++;
    if (selectedCategory.value && selectedCategory.value !== 'all') count++;
    if (selectedStockStatus.value && selectedStockStatus.value !== 'all') count++;
    if (priceMin.value) count++;
    if (priceMax.value) count++;
    if (isFeatured.value) count++;
    if (isOnSale.value) count++;
    return count;
});

// Methods
const applyFilters = () => {
    const filters: Record<string, any> = {};

    if (searchQuery.value) filters.search = searchQuery.value;
    if (selectedStatus.value && selectedStatus.value !== 'all') filters.status = selectedStatus.value;
    if (selectedBrand.value && selectedBrand.value !== 'all') filters.brand_id = selectedBrand.value;
    if (selectedCategory.value && selectedCategory.value !== 'all') filters.category_id = selectedCategory.value;
    if (selectedStockStatus.value && selectedStockStatus.value !== 'all') filters.stock_status = selectedStockStatus.value;
    if (priceMin.value) filters.price_min = parseFloat(priceMin.value);
    if (priceMax.value) filters.price_max = parseFloat(priceMax.value);
    if (isFeatured.value) filters.is_featured = true;
    if (isOnSale.value) filters.is_on_sale = true;

    router.get(route('admin.products.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedBrand.value = 'all';
    selectedCategory.value = 'all';
    selectedStockStatus.value = 'all';
    priceMin.value = '';
    priceMax.value = '';
    isFeatured.value = false;
    isOnSale.value = false;

    router.get(
        route('admin.products.index'),
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilter = (filterName: string) => {
    switch (filterName) {
        case 'search':
            searchQuery.value = '';
            break;
        case 'status':
            selectedStatus.value = 'all';
            break;
        case 'brand':
            selectedBrand.value = 'all';
            break;
        case 'category':
            selectedCategory.value = 'all';
            break;
        case 'stock_status':
            selectedStockStatus.value = 'all';
            break;
        case 'price_min':
            priceMin.value = '';
            break;
        case 'price_max':
            priceMax.value = '';
            break;
        case 'featured':
            isFeatured.value = false;
            break;
        case 'sale':
            isOnSale.value = false;
            break;
    }
    applyFilters();
};

// Auto-apply filters on certain changes
watch([selectedStatus, selectedBrand, selectedCategory, selectedStockStatus, isFeatured, isOnSale], () => {
    applyFilters();
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <Filter class="h-4 w-4" />
                    <span>Filters</span>
                    <span v-if="activeFiltersCount > 0" class="text-sm text-muted-foreground"> ({{ activeFiltersCount }} active) </span>
                </div>
                <Button v-if="hasFilters" @click="clearFilters" variant="ghost" size="sm" class="text-muted-foreground hover:text-foreground">
                    <X class="mr-1 h-4 w-4" />
                    Clear All
                </Button>
            </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Search -->
            <div class="relative">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                <Input v-model="searchQuery" placeholder="Search products by name or SKU..." class="pl-10" @keyup.enter="applyFilters" />
                <Button
                    v-if="searchQuery"
                    @click="clearFilter('search')"
                    variant="ghost"
                    size="sm"
                    class="absolute top-1/2 right-1 h-6 w-6 -translate-y-1/2 transform p-0"
                >
                    <X class="h-3 w-3" />
                </Button>
            </div>

            <!-- Filter Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Status Filter -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <Select v-model="selectedStatus">
                        <SelectTrigger>
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem value="published">Published</SelectItem>
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="archived">Archived</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Brand Filter -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Brand</label>
                    <Select v-model="selectedBrand">
                        <SelectTrigger>
                            <SelectValue placeholder="All Brands" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Brands</SelectItem>
                            <SelectItem v-for="brand in brands" :key="brand.id" :value="brand.id.toString()">
                                {{ brand.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Category Filter -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Category</label>
                    <Select v-model="selectedCategory">
                        <SelectTrigger>
                            <SelectValue placeholder="All Categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Categories</SelectItem>
                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Stock Status Filter -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Stock Status</label>
                    <Select v-model="selectedStockStatus">
                        <SelectTrigger>
                            <SelectValue placeholder="All Stock Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Stock Status</SelectItem>
                            <SelectItem value="in_stock">In Stock</SelectItem>
                            <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                            <SelectItem value="back_order">Back Order</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Price Range -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Min Price</label>
                    <Input v-model="priceMin" type="number" step="0.01" min="0" placeholder="0.00" @keyup.enter="applyFilters" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Max Price</label>
                    <Input v-model="priceMax" type="number" step="0.01" min="0" placeholder="999999.99" @keyup.enter="applyFilters" />
                </div>
            </div>

            <!-- Boolean Filters -->
            <div class="flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center space-x-2">
                    <input v-model="isFeatured" type="checkbox" class="rounded border-gray-300" />
                    <span class="text-sm">Featured Products Only</span>
                </label>
                <label class="flex cursor-pointer items-center space-x-2">
                    <input v-model="isOnSale" type="checkbox" class="rounded border-gray-300" />
                    <span class="text-sm">On Sale Products Only</span>
                </label>
            </div>

            <!-- Apply Button -->
            <div class="flex items-center space-x-2 pt-2">
                <Button @click="applyFilters" size="sm"> Apply Filters </Button>
                <Button v-if="hasFilters" @click="clearFilters" variant="outline" size="sm"> Clear All </Button>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasFilters" class="flex flex-wrap gap-2 border-t pt-2">
                <span class="text-sm text-muted-foreground">Active filters:</span>

                <Button v-if="searchQuery" @click="clearFilter('search')" variant="secondary" size="sm" class="h-6 text-xs">
                    Search: "{{ searchQuery }}"
                    <X class="ml-1 h-3 w-3" />
                </Button>

                <Button v-if="selectedStatus" @click="clearFilter('status')" variant="secondary" size="sm" class="h-6 text-xs">
                    Status: {{ selectedStatus }}
                    <X class="ml-1 h-3 w-3" />
                </Button>

                <Button v-if="selectedBrand" @click="clearFilter('brand')" variant="secondary" size="sm" class="h-6 text-xs">
                    Brand: {{ brands.find((b) => b.id.toString() === selectedBrand)?.name }}
                    <X class="ml-1 h-3 w-3" />
                </Button>

                <Button v-if="selectedCategory" @click="clearFilter('category')" variant="secondary" size="sm" class="h-6 text-xs">
                    Category: {{ categories.find((c) => c.id.toString() === selectedCategory)?.name }}
                    <X class="ml-1 h-3 w-3" />
                </Button>

                <Button v-if="isFeatured" @click="clearFilter('featured')" variant="secondary" size="sm" class="h-6 text-xs">
                    Featured
                    <X class="ml-1 h-3 w-3" />
                </Button>

                <Button v-if="isOnSale" @click="clearFilter('sale')" variant="secondary" size="sm" class="h-6 text-xs">
                    On Sale
                    <X class="ml-1 h-3 w-3" />
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
