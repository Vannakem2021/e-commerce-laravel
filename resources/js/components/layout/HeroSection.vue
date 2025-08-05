<script setup lang="ts">
import BestSellers from '@/components/product/BestSellers.vue';
import ProductBanner from '@/components/product/ProductBanner.vue';
import ProductCarousel from '@/components/product/ProductCarousel.vue';
import ShopByCategory from '@/components/product/ShopByCategory.vue';
import { type Product } from '@/types/product';

interface Props {
    featuredProducts?: Product[];
    bestSellers?: Product[];
    newArrivals?: Product[];
}

const props = withDefaults(defineProps<Props>(), {
    featuredProducts: () => [],
    bestSellers: () => [],
    newArrivals: () => [],
});
</script>

<template>
    <!-- Image Banner Carousel -->
    <ProductBanner />

    <!-- Shop by Category Section -->
    <ShopByCategory />

    <!-- Featured Products Carousel -->
    <ProductCarousel :products="featuredProducts" />

    <!-- Best Sellers Section -->
    <BestSellers :products="bestSellers" />
</template>
