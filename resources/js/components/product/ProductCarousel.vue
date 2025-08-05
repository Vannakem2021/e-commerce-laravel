<script setup lang="ts">
import ProductCard from '@/components/product/ProductCard.vue';
import { type FeaturedProduct } from '@/types/product';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { ref } from 'vue';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// Sample featured products data with Unsplash images
const featuredProducts = ref<FeaturedProduct[]>([
    {
        id: 1,
        name: 'iPhone 15 Pro Max',
        price: 1199,
        originalPrice: 1299,
        image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&h=600&fit=crop&crop=center',
        category: 'Smartphones',
        rating: 4.9,
        isOnSale: true,
        href: '/products/iphone-15-pro-max',
    },
    {
        id: 2,
        name: 'MacBook Pro 16"',
        price: 2499,
        image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&h=600&fit=crop&crop=center',
        category: 'Laptops',
        rating: 4.8,
        href: '/products/macbook-pro-16',
    },
    {
        id: 3,
        name: 'Sony WH-1000XM5',
        price: 399,
        originalPrice: 449,
        image: 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=800&h=600&fit=crop&crop=center',
        category: 'Audio',
        rating: 4.7,
        isOnSale: true,
        href: '/products/sony-wh-1000xm5',
    },
    {
        id: 4,
        name: 'iPad Pro 12.9"',
        price: 1099,
        image: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&h=600&fit=crop&crop=center',
        category: 'Tablets',
        rating: 4.6,
        href: '/products/ipad-pro-12-9',
    },
    {
        id: 5,
        name: 'Samsung Galaxy Watch',
        price: 329,
        originalPrice: 399,
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&h=600&fit=crop&crop=center',
        category: 'Wearables',
        rating: 4.5,
        isOnSale: true,
        href: '/products/samsung-galaxy-watch',
    },
    {
        id: 6,
        name: 'Dell XPS 13',
        price: 1299,
        image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&h=600&fit=crop&crop=center',
        category: 'Laptops',
        rating: 4.4,
        href: '/products/dell-xps-13',
    },
]);

// Swiper modules
const modules = [Autoplay, Navigation, Pagination];

// Swiper configuration
const swiperOptions = {
    modules,
    spaceBetween: 30,
    slidesPerView: 1,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
    },
    pagination: {
        clickable: true,
        dynamicBullets: true,
    },
    breakpoints: {
        480: {
            slidesPerView: 1.2,
            spaceBetween: 15,
        },
        640: {
            slidesPerView: 2,
            spaceBetween: 20,
        },
        768: {
            slidesPerView: 2.5,
            spaceBetween: 25,
        },
        1024: {
            slidesPerView: 3,
            spaceBetween: 30,
        },
        1280: {
            slidesPerView: 3.5,
            spaceBetween: 30,
        },
    },
    loop: true,
};
</script>

<template>
    <section class="bg-gradient-to-br from-teal-50 to-cyan-100 py-16 lg:py-24">
        <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 lg:text-4xl">
                    Featured
                    <span class="text-teal-600">Products</span>
                </h2>
                <p class="mt-4 text-lg text-gray-700">Discover our handpicked selection of premium electronics</p>
            </div>

            <!-- Product Carousel -->
            <div class="relative">
                <Swiper v-bind="swiperOptions" class="product-carousel !pb-12">
                    <SwiperSlide v-for="product in featuredProducts" :key="product.id" class="h-auto">
                        <ProductCard :product="product" variant="premium" :show-quick-actions="true" :show-description="true" :show-brand="true" />
                    </SwiperSlide>
                </Swiper>
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
.product-carousel :deep(.swiper-pagination) {
    bottom: 0 !important;
}

.product-carousel :deep(.swiper-pagination-bullet) {
    background-color: rgb(13 148 136);
    opacity: 0.5;
    transition: all 0.3s ease;
    width: 12px;
    height: 12px;
    margin: 0 6px;
}

.product-carousel :deep(.swiper-pagination-bullet-active) {
    background-color: rgb(13 148 136);
    opacity: 1;
    transform: scale(1.2);
}

.product-carousel :deep(.swiper-pagination-bullet:hover) {
    opacity: 0.75;
    transform: scale(1.1);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
