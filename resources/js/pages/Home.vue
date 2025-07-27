<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import HeroSection from '@/components/layout/HeroSection.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="Home - Electronics Store">
            <meta
                name="description"
                content="Shop the latest electronics at unbeatable prices. Smartphones, laptops, tablets, audio equipment and more with free shipping and warranty."
            />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Hero Section -->
        <HeroSection />

        <!-- Footer -->
        <Footer />
    </div>
</template>
