<script setup lang="ts">
import ProductCard from '@/components/product/ProductCard.vue';
import { type FeaturedProduct } from '@/types/product';

interface Props {
    products: FeaturedProduct[];
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Related Products',
});
</script>

<template>
    <div class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">{{ title }}</h2>
                <p class="mt-4 text-lg font-medium text-gray-700">Discover more products you might like</p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    variant="premium"
                    :show-quick-actions="true"
                    :show-description="true"
                    :show-brand="true"
                />
            </div>

            <!-- View All Button -->
            <div class="mt-12 text-center">
                <Button
                    as-child
                    variant="outline"
                    size="lg"
                    class="border-gray-400 px-8 py-3 font-semibold text-gray-800 hover:bg-gray-100 hover:text-gray-900"
                >
                    <Link href="/products"> View All Products </Link>
                </Button>
            </div>
        </div>
    </div>
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
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
