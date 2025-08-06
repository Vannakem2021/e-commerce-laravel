<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { ChevronDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    product_count: number;
    children?: Category[];
}

interface Brand {
    id: number;
    name: string;
    slug: string;
    product_count?: number;
}

interface Props {
    currentCategory?: string;
    currentBrand?: string;
    currentMinPrice?: number;
    currentMaxPrice?: number;
    brands?: Brand[];
}

const props = withDefaults(defineProps<Props>(), {
    currentCategory: '',
    currentBrand: '',
    currentMinPrice: 0,
    currentMaxPrice: 1000,
    brands: () => [],
});

// Filter state
const expandedSections = ref({
    category: true,
    brand: true,
    price: true,
});

const priceRange = ref({
    min: props.currentMinPrice,
    max: props.currentMaxPrice,
});

// Get categories from Inertia props (shared data is in root props)
const page = usePage();
const sharedCategories = computed(() => {
    const cats = page.props.categories || [];
    console.log('ProductFilters: Raw categories from props:', cats);
    return cats;
});

// Flatten the hierarchical structure for filter display
const categories = computed(() => {
    try {
        console.log('ProductFilters: Processing categories for filters:', sharedCategories.value);

        if (!sharedCategories.value || !Array.isArray(sharedCategories.value)) {
            console.warn('ProductFilters: Categories not available in shared data for filters');
            console.log('ProductFilters: Shared data keys:', Object.keys(sharedData.value));
            return [];
        }

        const flatCategories: Category[] = [];

        sharedCategories.value.forEach((rootCategory: Category) => {
        // Add root category
        flatCategories.push({
            id: rootCategory.id,
            name: rootCategory.name,
            slug: rootCategory.slug,
            product_count: rootCategory.product_count,
        });

        // Add children categories
        if (rootCategory.children && rootCategory.children.length > 0) {
            rootCategory.children.forEach((child: Category) => {
                flatCategories.push({
                    id: child.id,
                    name: child.name,
                    slug: child.slug,
                    product_count: child.product_count,
                });
            });
        }
        });

        console.log('ProductFilters: Generated flat categories:', flatCategories);
        return flatCategories;
    } catch (error) {
        console.error('ProductFilters: Error processing categories:', error);
        return [];
    }
});

// Categories are no longer loading since they come from server
const categoriesLoading = ref(false);

// Brands data (now comes from props)
const brandsLoading = ref(false);

// Computed brands with product counts
const brandsWithCounts = computed(() => {
    return props.brands.map((brand) => ({
        ...brand,
        count: brand.product_count || 0,
    }));
});

// Methods
const toggleSection = (section: string) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const buildFilterUrl = (params: Record<string, string | number>) => {
    const searchParams = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== '' && value !== 0) {
            searchParams.set(key, value.toString());
        }
    });

    return `/products?${searchParams.toString()}`;
};

const applyPriceFilter = () => {
    const params: Record<string, string | number> = {};

    if (props.currentCategory) params.category = props.currentCategory;
    if (props.currentBrand) params.brand = props.currentBrand;
    if (priceRange.value.min > 0) params.min_price = priceRange.value.min;
    if (priceRange.value.max < 5000) params.max_price = priceRange.value.max;

    window.location.href = buildFilterUrl(params);
};

const clearFilters = () => {
    window.location.href = '/products';
};

// Check if category is active
const isCategoryActive = (slug: string) => {
    return props.currentCategory === slug;
};

// Check if brand is active
const isBrandActive = (slug: string) => {
    return props.currentBrand === slug;
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return props.currentCategory || props.currentBrand || props.currentMinPrice > 0 || props.currentMaxPrice < 1000;
});

// Toggle category filter (checkbox behavior)
const toggleCategoryFilter = (slug: string) => {
    if (isCategoryActive(slug)) {
        // Remove category filter
        window.location.href = buildFilterUrl({
            brand: props.currentBrand,
            min_price: props.currentMinPrice,
            max_price: props.currentMaxPrice,
        });
    } else {
        // Add category filter
        window.location.href = buildFilterUrl({
            category: slug,
            brand: props.currentBrand,
            min_price: props.currentMinPrice,
            max_price: props.currentMaxPrice,
        });
    }
};

// Toggle brand filter (checkbox behavior)
const toggleBrandFilter = (slug: string) => {
    if (isBrandActive(slug)) {
        // Remove brand filter
        window.location.href = buildFilterUrl({
            category: props.currentCategory,
            min_price: props.currentMinPrice,
            max_price: props.currentMaxPrice,
        });
    } else {
        // Add brand filter
        window.location.href = buildFilterUrl({
            category: props.currentCategory,
            brand: slug,
            min_price: props.currentMinPrice,
            max_price: props.currentMaxPrice,
        });
    }
};


</script>

<template>
    <div class="w-full overflow-hidden rounded-lg border border-gray-200 bg-white">
        <!-- Clear Filters -->
        <div v-if="hasActiveFilters" class="border-b border-gray-200 bg-gray-50 px-4 py-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Active Filters</span>
                <button @click="clearFilters" class="text-xs font-medium text-teal-600 hover:text-teal-700">Clear All</button>
            </div>
        </div>

        <!-- Categories Filter -->
        <div class="border-b border-gray-200">
            <button
                @click="toggleSection('category')"
                class="flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-gray-50"
            >
                <div class="flex items-center space-x-2">
                    <div class="flex h-4 w-4 items-center justify-center rounded-sm bg-gray-400">
                        <span class="text-xs text-white">📂</span>
                    </div>
                    <span class="font-medium text-gray-900">Categories</span>
                </div>
                <ChevronDown :class="['h-4 w-4 text-gray-500 transition-transform duration-200', expandedSections.category ? 'rotate-180' : '']" />
            </button>

            <div v-if="expandedSections.category" class="px-4 pb-3">
                <!-- Loading State -->
                <div v-if="categoriesLoading" class="space-y-2">
                    <div v-for="i in 5" :key="i" class="animate-pulse">
                        <div class="h-6 rounded bg-gray-200"></div>
                    </div>
                </div>

                <!-- Categories List -->
                <div v-else class="space-y-1">
                    <label
                        v-for="category in categories"
                        :key="category.slug"
                        class="-mx-2 flex cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors hover:bg-gray-50"
                    >
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                :checked="isCategoryActive(category.slug)"
                                @change="toggleCategoryFilter(category.slug)"
                                class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-2 focus:ring-teal-500"
                            />
                            <span class="text-sm text-gray-700">{{ category.name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">({{ category.product_count }})</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Brands Filter -->
        <div class="border-b border-gray-200">
            <button
                @click="toggleSection('brand')"
                class="flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-gray-50"
            >
                <div class="flex items-center space-x-2">
                    <div class="flex h-4 w-4 items-center justify-center rounded-sm bg-gray-400">
                        <span class="text-xs text-white">🏷️</span>
                    </div>
                    <span class="font-medium text-gray-900">Brands</span>
                </div>
                <ChevronDown :class="['h-4 w-4 text-gray-500 transition-transform duration-200', expandedSections.brand ? 'rotate-180' : '']" />
            </button>

            <div v-if="expandedSections.brand" class="px-4 pb-3">
                <!-- Loading State -->
                <div v-if="brandsLoading" class="space-y-2">
                    <div v-for="i in 5" :key="i" class="animate-pulse">
                        <div class="h-6 rounded bg-gray-200"></div>
                    </div>
                </div>

                <!-- Brands List -->
                <div v-else class="space-y-1">
                    <label
                        v-for="brand in brandsWithCounts"
                        :key="brand.slug"
                        class="-mx-2 flex cursor-pointer items-center justify-between rounded px-2 py-1 transition-colors hover:bg-gray-50"
                    >
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                :checked="isBrandActive(brand.slug)"
                                @change="toggleBrandFilter(brand.slug)"
                                class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-2 focus:ring-teal-500"
                            />
                            <span class="text-sm text-gray-700">{{ brand.name }}</span>
                        </div>
                        <span class="text-xs text-gray-500">({{ brand.count }})</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Price Range Filter -->
        <div>
            <button
                @click="toggleSection('price')"
                class="flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-gray-50"
            >
                <div class="flex items-center space-x-2">
                    <div class="flex h-4 w-4 items-center justify-center rounded-sm bg-gray-400">
                        <span class="text-xs text-white">💰</span>
                    </div>
                    <span class="font-medium text-gray-900">Price Range</span>
                </div>
                <ChevronDown :class="['h-4 w-4 text-gray-500 transition-transform duration-200', expandedSections.price ? 'rotate-180' : '']" />
            </button>

            <div v-if="expandedSections.price" class="px-4 pb-4">
                <!-- Price Range Display -->
                <div class="mb-4">
                    <div class="mb-2 flex items-center justify-between text-sm text-gray-600">
                        <span>${{ priceRange.min }}</span>
                        <span>${{ priceRange.max }}</span>
                    </div>

                    <!-- Price Range Slider -->
                    <div class="relative">
                        <div class="h-2 rounded-full bg-gray-200">
                            <div
                                class="h-2 rounded-full bg-teal-600"
                                :style="{
                                    marginLeft: `${(priceRange.min / 1000) * 100}%`,
                                    width: `${((priceRange.max - priceRange.min) / 1000) * 100}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Price Inputs -->
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Min</label>
                        <Input v-model.number="priceRange.min" type="number" min="0" max="1000" class="h-8 text-sm" placeholder="0" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-700">Max</label>
                        <Input v-model.number="priceRange.max" type="number" min="0" max="1000" class="h-8 text-sm" placeholder="1000" />
                    </div>
                </div>

                <!-- Apply Price Filter Button -->
                <button
                    @click="applyPriceFilter"
                    class="w-full rounded-md bg-teal-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-teal-700"
                >
                    Apply Filter
                </button>
            </div>
        </div>
    </div>
</template>
