<script setup lang="ts">
import ProductCard from '@/components/product/ProductCard.vue';
import { type Product } from '@/types/product';
import { Link } from '@inertiajs/vue3';

interface Props {
    products?: Product[];
    title?: string;
    showViewAll?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    products: () => [],
    title: 'Best Sellers',
    showViewAll: true,
});
</script>

<template>
    <section class="bg-gray-50 py-16 lg:py-24">
        <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">
                    {{ title.split(' ')[0] }}
                    <span class="text-teal-600">{{ title.split(' ').slice(1).join(' ') }}</span>
                </h2>
                <p class="mt-4 text-lg text-gray-700">Our most popular products loved by customers</p>
            </div>

            <!-- Products Grid -->
            <div v-if="products.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    variant="premium"
                    :show-quick-actions="true"
                    :show-description="true"
                    :show-brand="true"
                    custom-badge="Best Seller"
                    custom-badge-color="bg-orange-500"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <p class="text-gray-500">No products available at the moment.</p>
            </div>

            <!-- View All Products Link -->
            <div v-if="showViewAll && products.length > 0" class="mt-12 text-center">
                <Link
                    href="/products"
                    class="inline-flex items-center rounded-lg bg-primary px-6 py-3 text-base font-medium text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                >
                    View All Products
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Enhanced hover effects */
.group:hover .product-image {
    transform: scale(1.05);
}

/* Smooth transitions for all interactive elements */
.group * {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .group {
        max-width: 400px;
        margin: 0 auto;
    }

    .group h3 {
        font-size: 1rem;
    }

    .group .text-xl {
        font-size: 1.25rem;
    }

    .group .aspect-square {
        aspect-ratio: 1;
    }
}

/* Desktop enhancements */
@media (min-width: 1024px) {
    .group:hover {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
}
</style>
