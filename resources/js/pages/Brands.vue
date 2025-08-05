<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import { Button } from '@/components/ui/button';

import { type Brand } from '@/types/product';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Building2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    brands: Brand[];
}

const props = defineProps<Props>();

// Computed properties for organizing brands
const featuredBrands = computed(() =>
    props.brands.filter(brand => brand.is_featured).slice(0, 6)
);

const otherBrands = computed(() =>
    props.brands.filter(brand => !brand.is_featured)
);

// Helper function to get brand logo or fallback
const getBrandLogo = (brand: Brand) => {
    if (brand.logo) {
        return brand.logo.startsWith('http') ? brand.logo : `/storage/${brand.logo}`;
    }
    // Fallback to a placeholder or brand initial
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(brand.name)}&size=200&background=f3f4f6&color=374151`;
};

// Sample fallback brands data for development
// Helper function to get brand URL
const getBrandUrl = (brand: Brand) => {
    return `/products?brand=${brand.slug}`;
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <Head title="Brands - Electronics Store">
            <meta
                name="description"
                content="Shop by your favorite electronics brands. Discover products from Apple, Samsung, Sony, Microsoft, Google, Dell, HP, Lenovo and more."
            />
        </Head>

        <!-- Navbar -->
        <Navbar />

        <!-- Brands Page -->
        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb Navigation -->
                <nav class="mb-8 flex items-center space-x-2 text-sm text-gray-600">
                    <Link href="/" class="font-medium hover:text-gray-800">Home</Link>
                    <span>/</span>
                    <span class="font-semibold text-gray-800">Brands</span>
                </nav>

                <!-- Page Header -->
                <div class="mb-12 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <Building2 class="mr-3 h-8 w-8 text-teal-600" />
                        <h1 class="text-4xl font-bold text-gray-900 lg:text-5xl">Shop by Brand</h1>
                    </div>
                    <p class="mt-4 text-lg font-medium text-gray-700">Discover products from the world's leading technology brands</p>
                </div>

                <!-- Featured Brands -->
                <div v-if="featuredBrands.length > 0" class="mb-16">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900">Featured Brands</h2>
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="brand in featuredBrands"
                            :key="brand.id"
                            :href="getBrandUrl(brand)"
                            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 p-8 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                        >
                            <!-- Brand Logo -->
                            <div class="mb-6 flex justify-center">
                                <div class="h-20 w-20 overflow-hidden rounded-full bg-white p-4 shadow-md">
                                    <img :src="getBrandLogo(brand)" :alt="brand.name" class="h-full w-full object-cover" />
                                </div>
                            </div>

                            <!-- Brand Info -->
                            <div class="text-center">
                                <h3 class="mb-2 text-2xl font-bold text-gray-900 group-hover:text-teal-600">{{ brand.name }}</h3>
                                <p v-if="brand.description" class="mb-4 text-gray-600">{{ brand.description }}</p>

                                <!-- Product Count -->
                                <div class="mb-4 text-sm font-semibold text-teal-600">{{ brand.products_count || 0 }} Products Available</div>

                                <!-- View Products Button -->
                                <div class="flex items-center justify-center text-teal-600 group-hover:text-teal-700">
                                    <span class="font-semibold">View Products</span>
                                    <ArrowRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- All Brands -->
                <div v-if="otherBrands.length > 0">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900">All Brands</h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <Link
                            v-for="brand in otherBrands"
                            :key="brand.id"
                            :href="getBrandUrl(brand)"
                            class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                        >
                            <!-- Brand Logo -->
                            <div class="mb-4 flex justify-center">
                                <div class="h-16 w-16 overflow-hidden rounded-full bg-gray-50 p-3">
                                    <img :src="getBrandLogo(brand)" :alt="brand.name" class="h-full w-full object-cover" />
                                </div>
                            </div>

                            <!-- Brand Info -->
                            <div class="text-center">
                                <h3 class="mb-2 text-lg font-semibold text-gray-900 group-hover:text-teal-600">{{ brand.name }}</h3>
                                <p v-if="brand.description" class="mb-3 text-sm text-gray-600">{{ brand.description }}</p>

                                <!-- Product Count -->
                                <div class="mb-3 text-xs font-semibold text-teal-600">{{ brand.products_count || 0 }} Products</div>

                                <!-- View Products Link -->
                                <div class="flex items-center justify-center text-sm text-gray-800 group-hover:text-gray-900">
                                    <span class="font-medium">View Products</span>
                                    <ArrowRight class="ml-1 h-3 w-3 transition-transform group-hover:translate-x-1" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="props.brands.length === 0" class="py-12 text-center">
                    <Building2 class="mx-auto h-16 w-16 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No brands found</h3>
                    <p class="mt-2 text-gray-600">Brands will appear here once they are added.</p>
                    <Link
                        href="/products"
                        class="mt-4 inline-flex items-center space-x-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    >
                        <span>Browse All Products</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Browse All Products -->
                <div class="mt-16 text-center">
                    <Button
                        as-child
                        variant="outline"
                        size="lg"
                        class="border-gray-400 px-8 py-3 font-semibold text-gray-800 hover:bg-gray-100 hover:text-gray-900"
                    >
                        <Link href="/products"> Browse All Products </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <Footer />
    </div>
</template>
