<script setup lang="ts">
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { onMounted, onUnmounted, ref } from 'vue';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// Banner images data with high-quality Unsplash images
const bannerImages = ref([
    {
        id: 1,
        image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1920&h=800&fit=crop&crop=center',
        alt: 'Premium Electronics Collection',
        title: 'Premium Electronics',
    },
    {
        id: 2,
        image: 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=1920&h=800&fit=crop&crop=center',
        alt: 'Latest Smartphone Technology',
        title: 'Latest Smartphones',
    },
    {
        id: 3,
        image: 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=1920&h=800&fit=crop&crop=center',
        alt: 'Professional Audio Equipment',
        title: 'Audio Equipment',
    },
    {
        id: 4,
        image: 'https://images.unsplash.com/photo-1515378791036-0648a814c963?w=1920&h=800&fit=crop&crop=center',
        alt: 'Gaming and Entertainment',
        title: 'Gaming & Entertainment',
    },
    {
        id: 5,
        image: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1920&h=800&fit=crop&crop=center',
        alt: 'Smart Home Technology',
        title: 'Smart Home Tech',
    },
]);

// Swiper modules
const modules = [Autoplay, Navigation, Pagination];

// Swiper configuration
const swiperOptions = {
    modules,
    spaceBetween: 0,
    slidesPerView: 1,
    autoplay: {
        delay: 4500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
    },
    pagination: {
        clickable: true,
        dynamicBullets: false,
    },
    navigation: {
        nextEl: '.banner-swiper-button-next',
        prevEl: '.banner-swiper-button-prev',
    },
    loop: true,
    speed: 800,
};

// Close mobile menu when clicking outside or pressing escape
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        // Handle escape if needed
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <section class="relative bg-gray-900">
        <!-- Product Banner Carousel -->
        <div class="relative">
            <Swiper v-bind="swiperOptions" class="product-banner !h-[400px] md:!h-[500px] lg:!h-[600px]">
                <SwiperSlide v-for="banner in bannerImages" :key="banner.id" class="relative">
                    <!-- Banner Image -->
                    <div class="relative h-full w-full overflow-hidden">
                        <img :src="banner.image" :alt="banner.alt" class="h-full w-full object-cover object-center" loading="lazy" />

                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-black/40"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>

                        <!-- Optional Content Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center text-white">
                                <h2 class="text-3xl font-bold opacity-0 transition-opacity duration-300 hover:opacity-100 md:text-4xl lg:text-5xl">
                                    {{ banner.title }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <!-- Navigation Arrows -->
            <button
                class="banner-swiper-button-prev absolute top-1/2 left-4 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/30 focus:ring-2 focus:ring-white/50 focus:outline-none"
                aria-label="Previous image"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button
                class="banner-swiper-button-next absolute top-1/2 right-4 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/30 focus:ring-2 focus:ring-white/50 focus:outline-none"
                aria-label="Next image"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </section>
</template>

<style scoped>
.product-banner :deep(.swiper-pagination) {
    bottom: 20px !important;
    z-index: 10;
}

.product-banner :deep(.swiper-pagination-bullet) {
    background-color: rgba(255, 255, 255, 0.5);
    opacity: 1;
    transition: all 0.3s ease;
    width: 12px;
    height: 12px;
    margin: 0 6px;
}

.product-banner :deep(.swiper-pagination-bullet-active) {
    background-color: rgb(13 148 136); /* teal-600 */
    transform: scale(1.3);
}

.product-banner :deep(.swiper-pagination-bullet:hover) {
    background-color: rgba(255, 255, 255, 0.8);
    transform: scale(1.1);
}

.product-banner :deep(.swiper-slide) {
    height: 100%;
}

/* Custom fade effect enhancement */
.product-banner :deep(.swiper-slide-active) {
    z-index: 1;
}

.product-banner :deep(.swiper-slide-next),
.product-banner :deep(.swiper-slide-prev) {
    z-index: 0;
}

/* Navigation button hover effects */
.banner-swiper-button-prev:hover,
.banner-swiper-button-next:hover {
    transform: translateY(-50%) scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .banner-swiper-button-prev,
    .banner-swiper-button-next {
        width: 40px;
        height: 40px;
    }

    .banner-swiper-button-prev {
        left: 12px;
    }

    .banner-swiper-button-next {
        right: 12px;
    }
}
</style>
