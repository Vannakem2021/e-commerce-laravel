<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { type Product, type ProductVariant } from '@/types/product';
import { Link } from '@inertiajs/vue3';
import { Heart, Minus, Plus, RotateCcw, Share2, Shield, ShoppingCart, Truck } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    product: Product;
}

const props = defineProps<Props>();

// Store and composables
const cartStore = useCartStore();

// Reactive state
const selectedImageIndex = ref(0);
const quantity = ref(1);
const selectedTab = ref('description');
const selectedVariant = ref<ProductVariant | null>(null);
const isAddingToCart = ref(false);

// Helper function to get product image URL
const getProductImage = (product: Product, imageIndex: number = 0, size: string = 'large'): string => {
    // Try to get the specific image by index
    if (product.images?.[imageIndex]?.image_path) {
        const imagePath = product.images[imageIndex].image_path;
        // If it's already a full URL (like placeholder), use it directly
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        // Convert original path to desired size
        const sizedPath = imagePath
            .replace('/original/', `/${size}/`)
            .replace('.jpeg', `-${size}.jpeg`)
            .replace('.jpg', `-${size}.jpg`)
            .replace('.png', `-${size}.png`);
        return `/storage/${sizedPath}`;
    }
    // Try primary image as fallback
    if (product.primaryImage?.image_path) {
        const imagePath = product.primaryImage.image_path;
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        // Convert original path to desired size
        const sizedPath = imagePath
            .replace('/original/', `/${size}/`)
            .replace('.jpeg', `-${size}.jpeg`)
            .replace('.jpg', `-${size}.jpg`)
            .replace('.png', `-${size}.png`);
        return `/storage/${sizedPath}`;
    }
    // Try first image as fallback
    if (product.images?.[0]?.image_path) {
        const imagePath = product.images[0].image_path;
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        // Convert original path to desired size
        const sizedPath = imagePath
            .replace('/original/', `/${size}/`)
            .replace('.jpeg', `-${size}.jpeg`)
            .replace('.jpg', `-${size}.jpg`)
            .replace('.png', `-${size}.png`);
        return `/storage/${sizedPath}`;
    }
    return 'https://via.placeholder.com/600x600/e5e7eb/6b7280?text=No+Image';
};

// Computed properties
const selectedImage = computed(() => {
    return getProductImage(props.product, selectedImageIndex.value);
});

// Simplified pricing - use base product prices only
const currentPrice = computed(() => {
    return selectedVariant.value?.price || props.product.price;
});

const currentComparePrice = computed(() => {
    return selectedVariant.value?.compare_price || props.product.compare_price;
});

const currentStock = computed(() => {
    return selectedVariant.value?.stock_quantity || props.product.stock_quantity;
});

const currentStockStatus = computed(() => {
    return selectedVariant.value?.stock_status || props.product.stock_status;
});

const discountPercentage = computed(() => {
    const price = currentPrice.value;
    const comparePrice = currentComparePrice.value;
    if (comparePrice && comparePrice > price) {
        return Math.round(((comparePrice - price) / comparePrice) * 100);
    }
    return 0;
});

const isInStock = computed(() => {
    return currentStockStatus.value === 'in_stock' && currentStock.value > 0;
});

const maxQuantity = computed(() => {
    return currentStock.value || 999;
});

// Removed attribute-related computed properties

// Methods
const selectImage = (index: number) => {
    selectedImageIndex.value = index;
};

const incrementQuantity = () => {
    if (quantity.value < maxQuantity.value) {
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// Variant handling methods
const handleVariantSelected = (variant: ProductVariant | null) => {
    selectedVariant.value = variant;

    // Update images if variant has specific image
    if (variant?.image_url) {
        // You could add logic here to switch to variant-specific images
        // For now, we'll keep the current image selection
    }

    // Reset quantity if it exceeds new stock limit
    if (variant && quantity.value > variant.stock_quantity) {
        quantity.value = Math.min(quantity.value, variant.stock_quantity);
    }
};

// Removed attribute handling

const addToCart = async () => {
    if (isAddingToCart.value || !isInStock.value) return;

    isAddingToCart.value = true;

    try {
        const result = await cartStore.addToCart(
            props.product.id,
            quantity.value,
            selectedVariant.value?.id
        );

        if (result.success) {
            // Success feedback is handled by the cart store
            console.log('Added to cart successfully:', result.message);
        } else {
            // Error feedback is handled by the cart store
            console.error('Failed to add to cart:', result.message);
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        // Error feedback is handled by the cart store
    } finally {
        isAddingToCart.value = false;
    }
};

const addToWishlist = () => {
    // Add to wishlist logic here
    console.log(`Adding product ${props.product.id} to wishlist`);
};



const shareProduct = () => {
    // Share product logic here
    if (navigator.share) {
        navigator.share({
            title: props.product.name,
            text: props.product.description,
            url: window.location.href,
        });
    }
};

// Removed attribute handling methods

// Removed remaining attribute helper methods
</script>

<template>
    <div class="bg-white">
        <!-- Breadcrumb Navigation -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-600">
                <Link href="/" class="font-medium hover:text-gray-800">Home</Link>
                <span>/</span>
                <Link href="/products" class="font-medium hover:text-gray-800">Products</Link>
                <span>/</span>
                <Link
                    v-if="product.categories && product.categories.length > 0"
                    :href="`/category/${product.categories[0].slug || product.categories[0].name.toLowerCase().replace(/\s+/g, '-')}`"
                    class="font-medium hover:text-gray-800"
                >
                    {{ product.categories[0].name }}
                </Link>
                <span v-else class="font-medium text-gray-800">Uncategorized</span>
                <span>/</span>
                <span class="font-semibold text-gray-800">{{ product.name }}</span>
            </nav>
        </div>

        <!-- Product Detail Section -->
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 rounded-2xl bg-white p-8 shadow-[0_2px_10px_rgba(0,0,0,0.1)] lg:grid-cols-2">
                <!-- Product Images -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="mx-auto aspect-square max-w-md overflow-hidden rounded-2xl bg-gray-100">
                        <img
                            :src="selectedImage"
                            :alt="product.name"
                            class="h-full w-full object-cover object-center transition-transform duration-300 hover:scale-105"
                        />
                    </div>

                    <!-- Thumbnail Images -->
                    <div v-if="product.images && product.images.length > 1" class="mx-auto grid max-w-md grid-cols-4 gap-3">
                        <button
                            v-for="(_, index) in product.images"
                            :key="index"
                            @click="selectImage(index)"
                            :class="[
                                'aspect-square overflow-hidden rounded-lg border-2 transition-all duration-200',
                                selectedImageIndex === index
                                    ? 'border-teal-600 ring-2 ring-teal-600 ring-offset-2'
                                    : 'border-gray-200 hover:border-gray-300',
                            ]"
                        >
                            <img
                                :src="getProductImage(product, index, 'medium')"
                                :alt="`${product.name} view ${index + 1}`"
                                class="h-full w-full object-cover object-center"
                            />
                        </button>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="space-y-6">
                    <!-- Product Title and Rating -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span v-if="product.brand" class="text-sm font-semibold text-teal-600">{{ product.brand.name }}</span>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="addToWishlist"
                                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-red-500"
                                >
                                    <Heart class="h-5 w-5" />
                                </button>
                                <button
                                    @click="shareProduct"
                                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <Share2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 lg:text-4xl">{{ product.name }}</h1>
                    </div>

                    <!-- Price -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-4">
                            <span class="text-4xl font-bold text-red-600">${{ (currentPrice / 100).toFixed(0) }}</span>
                            <span v-if="currentComparePrice" class="text-xl text-gray-400 line-through"
                                >${{ (currentComparePrice / 100).toFixed(0) }}</span
                            >
                        </div>

                        <!-- Removed price breakdown section -->
                    </div>

                    <!-- Stock Status -->
                    <div class="flex items-center space-x-2">
                        <div :class="['h-3 w-3 rounded-full', isInStock ? 'bg-green-500' : 'bg-red-500']"></div>
                        <span :class="['text-sm font-medium', isInStock ? 'text-green-700' : 'text-red-700']">
                            {{ isInStock ? 'In Stock' : 'Out of Stock' }}
                            <span v-if="isInStock && currentStock" class="text-gray-600"> ({{ currentStock }} available) </span>
                        </span>
                    </div>

                    <!-- Short Description -->
                    <div v-if="product.short_description" class="leading-relaxed text-gray-600">
                        <p>{{ product.short_description }}</p>
                    </div>

                    <!-- Removed attribute selection sections -->

                    <!-- Removed product specifications section -->

                    <!-- Features/Benefits -->
                    <div class="grid grid-cols-2 gap-4 py-6">
                        <div class="flex items-center space-x-2">
                            <Truck class="h-5 w-5 text-green-600" />
                            <span class="text-sm text-gray-600">Free Shipping</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Shield class="h-5 w-5 text-green-600" />
                            <span class="text-sm text-gray-600">2 Year Warranty</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RotateCcw class="h-5 w-5 text-green-600" />
                            <span class="text-sm text-gray-600">30 Day Returns</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Shield class="h-5 w-5 text-green-600" />
                            <span class="text-sm text-gray-600">Secure Payment</span>
                        </div>
                    </div>

                    <!-- Quantity and Add to Cart -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <label for="quantity" class="sr-only">Quantity</label>
                                <div class="flex items-center rounded-lg border border-gray-300">
                                    <button
                                        type="button"
                                        @click="decrementQuantity"
                                        :disabled="quantity <= 1"
                                        class="flex h-12 w-12 items-center justify-center rounded-l-lg border-r border-gray-300 text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </button>
                                    <input
                                        id="quantity"
                                        v-model.number="quantity"
                                        type="number"
                                        min="1"
                                        :max="maxQuantity"
                                        class="h-12 w-16 border-0 text-center text-sm focus:ring-0"
                                    />
                                    <button
                                        type="button"
                                        @click="incrementQuantity"
                                        :disabled="quantity >= maxQuantity"
                                        class="flex h-12 w-12 items-center justify-center rounded-r-lg border-l border-gray-300 text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-4">
                            <button
                                type="button"
                                @click="addToCart"
                                :disabled="!isInStock || isAddingToCart"
                                class="flex flex-1 items-center justify-center rounded-lg bg-primary px-8 py-4 text-base font-medium text-primary-foreground transition-colors hover:bg-primary/90 focus:ring-2 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                style="--tw-ring-color: hsl(var(--ring))"
                            >
                                <ShoppingCart v-if="!isAddingToCart" class="mr-2 h-5 w-5" />
                                <div v-else class="mr-2 h-5 w-5 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                                {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
                            </button>
                            <button
                                type="button"
                                @click="addToWishlist"
                                class="flex items-center justify-center rounded-lg border border-border bg-background px-4 py-4 text-foreground transition-colors hover:bg-accent focus:ring-2 focus:ring-offset-2 focus:outline-none"
                                style="--tw-ring-color: hsl(var(--ring))"
                            >
                                <Heart class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Product SKU -->
                    <div class="text-sm text-gray-600"><span class="font-medium">SKU:</span> {{ product.sku }}</div>
                </div>
            </div>

            <!-- Product Details Tabs -->
            <div class="mt-16 rounded-2xl bg-white p-8 shadow-[0_2px_10px_rgba(0,0,0,0.1)]">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="selectedTab = 'description'"
                            :class="[
                                'border-b-2 px-1 py-4 text-sm font-semibold transition-colors',
                                selectedTab === 'description'
                                    ? 'border-teal-600 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            Description
                        </button>
                        <button
                            @click="selectedTab = 'specifications'"
                            :class="[
                                'border-b-2 px-1 py-4 text-sm font-semibold transition-colors',
                                selectedTab === 'specifications'
                                    ? 'border-teal-600 text-gray-900'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            Specifications
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="py-8">
                    <!-- Description Tab -->
                    <div v-if="selectedTab === 'description'" class="space-y-6">
                        <div v-if="product.description" class="prose max-w-none">
                            <p class="leading-relaxed text-gray-700">{{ product.description }}</p>
                        </div>
                        <div v-else class="py-8 text-center text-gray-500">No description available for this product.</div>

                        <div v-if="product.features">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Key Features</h3>
                            <div class="prose max-w-none">
                                <div v-html="product.features" class="leading-relaxed text-gray-700"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div v-if="selectedTab === 'specifications'">
                        <div v-if="product.specifications" class="prose max-w-none">
                            <div v-html="product.specifications" class="leading-relaxed text-gray-700"></div>
                        </div>
                        <div v-else class="py-8 text-center text-gray-500">No specifications available for this product.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
