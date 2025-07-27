<script setup lang="ts">
import BestSellers from '@/components/product/BestSellers.vue';
import ProductBanner from '@/components/product/ProductBanner.vue';
import ProductCarousel from '@/components/product/ProductCarousel.vue';
import ShopByCategory from '@/components/product/ShopByCategory.vue';
</script>

<template>
    <!-- Image Banner Carousel -->
    <ProductBanner />

    <!-- Shop by Category Section -->
    <ShopByCategory />

    <!-- Featured Products Carousel -->
    <ProductCarousel />

    <!-- Best Sellers Section -->
    <BestSellers />
</template>
