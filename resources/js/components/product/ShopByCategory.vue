<script setup lang="ts">
import Icon from '@/components/common/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Category data with icons and product counts
const categories = ref([
    {
        id: 1,
        name: 'Smartphones',
        description: 'Latest mobile technology',
        productCount: 245,
        icon: 'smartphone',
        href: '/categories/smartphones',
        color: 'from-blue-500 to-blue-600',
    },
    {
        id: 2,
        name: 'Laptops',
        description: 'Professional computing',
        productCount: 189,
        icon: 'laptop',
        href: '/categories/laptops',
        color: 'from-purple-500 to-purple-600',
    },
    {
        id: 3,
        name: 'Audio',
        description: 'Premium sound experience',
        productCount: 156,
        icon: 'headphones',
        href: '/categories/audio',
        color: 'from-green-500 to-green-600',
    },
    {
        id: 4,
        name: 'Gaming',
        description: 'Gaming gear & consoles',
        productCount: 134,
        icon: 'gamepad',
        href: '/categories/gaming',
        color: 'from-red-500 to-red-600',
    },
    {
        id: 5,
        name: 'Smart Home',
        description: 'Connected living',
        productCount: 98,
        icon: 'home',
        href: '/categories/smart-home',
        color: 'from-orange-500 to-orange-600',
    },
    {
        id: 6,
        name: 'Accessories',
        description: 'Essential add-ons',
        productCount: 312,
        icon: 'plug',
        href: '/categories/accessories',
        color: 'from-teal-500 to-teal-600',
    },
]);
</script>

<template>
    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">
                    Shop by
                    <span class="text-teal-600">Category</span>
                </h2>
                <p class="mt-4 text-lg text-gray-700">Discover products tailored to your needs</p>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6 md:grid-cols-3 lg:grid-cols-6">
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
                            <!-- Debug: Show icon name -->
                            <!-- <span class="text-xs text-gray-500">{{ category.icon }}</span> -->
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
                            {{ category.productCount }} products
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
                    class="inline-flex items-center rounded-lg bg-teal-600 px-6 py-3 text-base font-medium text-black transition-all duration-200 hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
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
