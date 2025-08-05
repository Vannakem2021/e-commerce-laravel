<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import ProductDetail from '@/components/product/ProductDetail.vue';
import RelatedProducts from '@/components/product/RelatedProducts.vue';
import { Toaster } from '@/components/ui/sonner';

import { type Product } from '@/types/product';
import { Head } from '@inertiajs/vue3';

interface Props {
    product: Product;
    relatedProducts: Product[];
}

const props = defineProps<Props>();
</script>

<template>
    <div class="min-h-screen bg-background">
        <Head :title="`${product.name} - Electronics Store`">
            <meta name="description" :content="product.short_description || product.description" />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Product Detail Component -->
        <ProductDetail :product="product" />

        <!-- Related Products -->
        <RelatedProducts v-if="relatedProducts.length > 0" :products="relatedProducts" title="Related Products" />

        <!-- Footer -->
        <Footer />

        <!-- Toast Notifications -->
        <Toaster position="bottom-right" />
    </div>
</template>
