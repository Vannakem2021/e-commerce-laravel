<script setup lang="ts">
import ProductFilters from '@/components/admin/ProductFilters.vue';
import ProductTable from '@/components/admin/ProductTable.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { PaginatedResponse, ProductFilters as ProductFiltersType } from '@/types';
import type { Brand, Category, Product } from '@/types/product';
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    products: PaginatedResponse<Product>;
    filters: ProductFiltersType;
    brands: Brand[];
    categories: Category[];
}

const props = defineProps<Props>();

// Per-page options
const perPageOptions = [5, 10, 15, 25, 50];
const currentPerPage = ref(props.products.per_page || 10);

// Computed property for select value
const selectValue = computed(() => currentPerPage.value.toString());

// Handle per-page change
const changePerPage = (perPage: number) => {
    currentPerPage.value = perPage;
    const currentUrl = new URL(window.location.href);
    const params = new URLSearchParams(currentUrl.search);
    params.set('per_page', perPage.toString());
    params.delete('page'); // Reset to first page when changing per-page

    router.get(`${currentUrl.pathname}?${params.toString()}`);
};
</script>

<template>
    <Head title="Products - Admin" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                        <Package class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Products</h1>
                        <p class="text-muted-foreground">Manage your product catalog</p>
                    </div>
                </div>
                <Link :href="route('admin.products.create')">
                    <Button class="flex items-center space-x-2">
                        <Plus class="h-4 w-4" />
                        <span>Add Product</span>
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <ProductFilters :filters="filters" :brands="brands" :categories="categories" class="mb-6" />

            <!-- Products Table -->
            <Card>
                <CardHeader>
                    <CardTitle> Products ({{ products.total }}) </CardTitle>
                </CardHeader>
                <CardContent>
                    <ProductTable :products="products.data" />

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="mt-6 border-t pt-6">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <!-- Results Info & Per Page Selector -->
                            <div class="flex flex-col items-center gap-4 sm:flex-row">
                                <div class="text-sm text-muted-foreground">
                                    Showing <span class="font-medium">{{ products.from }}</span> to
                                    <span class="font-medium">{{ products.to }}</span> of
                                    <span class="font-medium">{{ products.total }}</span> results
                                </div>

                                <!-- Per Page Selector -->
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-muted-foreground">Show:</span>
                                    <Select :model-value="selectValue" @update:model-value="changePerPage(Number($event))">
                                        <SelectTrigger class="h-8 w-20">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="option in perPageOptions" :key="option" :value="option.toString()">
                                                {{ option }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span class="text-muted-foreground">per page</span>
                                </div>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="flex items-center space-x-1">
                                <!-- Previous Button -->
                                <Button
                                    v-if="products.prev_page_url"
                                    variant="outline"
                                    size="sm"
                                    @click="router.get(products.prev_page_url)"
                                    class="px-3"
                                >
                                    Previous
                                </Button>

                                <!-- Page Numbers -->
                                <div class="flex items-center space-x-1">
                                    <Button
                                        v-for="link in products.links.slice(1, -1)"
                                        :key="link.label"
                                        :variant="link.active ? 'default' : 'outline'"
                                        :disabled="!link.url"
                                        size="sm"
                                        @click="link.url && router.get(link.url)"
                                        class="min-w-[40px]"
                                        v-html="link.label"
                                    />
                                </div>

                                <!-- Next Button -->
                                <Button
                                    v-if="products.next_page_url"
                                    variant="outline"
                                    size="sm"
                                    @click="router.get(products.next_page_url)"
                                    class="px-3"
                                >
                                    Next
                                </Button>
                            </div>
                        </div>

                        <!-- Page Info -->
                        <div class="mt-3 text-center text-xs text-muted-foreground">Page {{ products.current_page }} of {{ products.last_page }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
