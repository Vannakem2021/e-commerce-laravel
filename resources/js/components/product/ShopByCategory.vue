<script setup lang="ts">
import Icon from '@/components/common/Icon.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string;
    product_count: number;
    href: string;
    icon: string;
    color: string;
}

// Categories fetched from API
const categories = ref<Category[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Fetch featured categories from API
const fetchCategories = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await axios.get('/api/categories/featured');
        categories.value = response.data.categories;

        // Add special categories (New Arrivals, Best Sellers) to the mix
        const specialCategories: Category[] = [
            {
                id: 999,
                name: 'New Arrivals',
                slug: 'new-arrivals',
                description: 'Latest products just added',
                product_count: 0, // Will be updated dynamically
                href: '/products?sort=newest',
                icon: 'clock',
                color: 'from-green-500 to-green-600',
            },
            {
                id: 998,
                name: 'Best Sellers',
                slug: 'best-sellers',
                description: 'Most popular products',
                product_count: 0, // Will be updated dynamically
                href: '/products?sort=popular',
                icon: 'trending-up',
                color: 'from-orange-500 to-orange-600',
            },
        ];

        // Combine database categories with special categories
        categories.value = [...categories.value, ...specialCategories];
    } catch (err) {
        console.error('Error fetching categories:', err);
        error.value = 'Failed to load categories';

        // Fallback to hardcoded categories if API fails
        categories.value = [
            {
                id: 1,
                name: 'Smartphones',
                slug: 'smartphones',
                description: 'Latest mobile technology',
                product_count: 0,
                icon: 'smartphone',
                href: '/products?category=smartphones',
                color: 'from-blue-500 to-blue-600',
            },
            {
                id: 2,
                name: 'Laptops',
                slug: 'laptops',
                description: 'Professional computing',
                product_count: 0,
                icon: 'laptop',
                href: '/products?category=laptops',
                color: 'from-purple-500 to-purple-600',
            },
        ];
    } finally {
        loading.value = false;
    }
};

// Fetch categories on component mount
onMounted(() => {
    fetchCategories();
});
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">
                    Shop by
                    <span class="text-teal-600">Category</span>
                </h2>
                <p class="mt-4 text-lg text-gray-700">Discover products tailored to your needs</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-6">
                <div v-for="i in 6" :key="i" class="animate-pulse rounded-2xl bg-gray-200 p-4 sm:p-6">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-gray-300"></div>
                    <div class="mb-2 h-4 rounded bg-gray-300"></div>
                    <div class="mb-3 h-3 rounded bg-gray-300"></div>
                    <div class="mx-auto h-6 w-20 rounded-full bg-gray-300"></div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <Icon name="alert-circle" class="h-8 w-8 text-red-600" />
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Failed to Load Categories</h3>
                <p class="mb-4 text-gray-600">{{ error }}</p>
                <button
                    @click="fetchCategories"
                    class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    <Icon name="refresh-cw" class="mr-2 h-4 w-4" />
                    Try Again
                </button>
            </div>

            <!-- Categories Grid -->
            <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="category.href"
                    class="group relative overflow-hidden rounded-2xl p-4 text-black shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none sm:p-6"
                >
                    <!-- Background Gradient -->
                    <div
                        class="absolute inset-0 bg-gradient-to-br opacity-5 transition-opacity duration-300 group-hover:opacity-10"
                        :class="category.color"
                    ></div>

                    <!-- Content -->
                    <div class="relative z-10 text-center">
                        <!-- Icon Container -->
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-teal-200 bg-teal-100 transition-all duration-300 group-hover:scale-110 group-hover:border-teal-300 group-hover:bg-teal-200"
                        >
                            <Icon :name="category.icon" class="h-8 w-8 text-teal-700 transition-colors duration-300 group-hover:text-teal-800" />
                        </div>

                        <!-- Category Name -->
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 transition-colors duration-300 group-hover:text-teal-700">
                            {{ category.name }}
                        </h3>

                        <!-- Description -->
                        <p class="mb-3 text-sm text-gray-600 transition-colors duration-300 group-hover:text-gray-700">
                            {{ category.description }}
                        </p>

                        <!-- Product Count -->
                        <div
                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 transition-all duration-300 group-hover:bg-teal-100 group-hover:text-teal-800"
                        >
                            {{ category.product_count }} products
                        </div>
                    </div>

                    <!-- Hover Effect Overlay -->
                    <div
                        class="absolute inset-0 rounded-2xl border-2 border-transparent transition-all duration-300 group-hover:border-teal-200"
                    ></div>
                </Link>
            </div>

            <!-- View All Categories Link -->
            <div class="mt-12 text-center">
                <Link
                    href="/categories"
                    class="inline-flex items-center rounded-lg bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                >
                    <Icon name="grid-3x3" class="mr-2 h-5 w-5" />
                    View All Categories
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Additional hover effects for enhanced interactivity */
.group:hover .icon-container {
    transform: translateY(-2px);
}

/* Smooth transitions for all interactive elements */
.group * {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Focus states for accessibility */
.group:focus-within {
    transform: translateY(-4px);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .group {
        padding: 1rem;
    }

    .group h3 {
        font-size: 1rem;
    }

    .group p {
        font-size: 0.75rem;
    }
}

/* Tablet optimizations */
@media (min-width: 768px) and (max-width: 1024px) {
    .group {
        padding: 1.25rem;
    }
}

/* Desktop enhancements */
@media (min-width: 1024px) {
    .group:hover {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
}
</style>
