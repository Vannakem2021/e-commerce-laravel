<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import HeroSection from '@/components/layout/HeroSection.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Toaster } from '@/components/ui/sonner';

import { type Product } from '@/types/product';
import { Head } from '@inertiajs/vue3';

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
    <div class="min-h-screen bg-background">
        <Head title="Home - Electronics Store">
            <meta
                name="description"
                content="Shop the latest electronics at unbeatable prices. Smartphones, laptops, tablets, audio equipment and more with free shipping and warranty."
            />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Hero Section -->
        <HeroSection :featured-products="featuredProducts" :best-sellers="bestSellers" :new-arrivals="newArrivals" />

        <!-- Footer -->
        <Footer />

        <!-- Toast Notifications -->
        <Toaster position="bottom-right" />
    </div>
</template>
