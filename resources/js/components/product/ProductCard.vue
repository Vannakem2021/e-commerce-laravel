<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useCartStore } from '@/stores/cart';
import { type FeaturedProduct, type Product } from '@/types/product';
import { Link } from '@inertiajs/vue3';
import { Eye, Heart, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    product: Product | FeaturedProduct;
    variant?: 'standard' | 'premium' | 'compact';
    showQuickActions?: boolean;
    showDescription?: boolean;
    showBrand?: boolean;
    customBadge?: string;
    customBadgeColor?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'standard',
    showQuickActions: true,
    showDescription: true,
    showBrand: true,
});

// Store and state
const cartStore = useCartStore();
const isAddingToCart = ref(false);

// Type guards to determine which interface we're working with
const isProduct = (product: Product | FeaturedProduct): product is Product => {
    return 'slug' in product;
};

const isFeaturedProduct = (product: Product | FeaturedProduct): product is FeaturedProduct => {
    return 'href' in product;
};

// Computed properties for unified data access
const productName = computed(() => props.product.name);
const productPrice = computed(() => {
    if (isProduct(props.product)) {
        return (props.product.price / 100).toFixed(2);
    }
    return props.product.price.toFixed(2);
});

const productComparePrice = computed(() => {
    if (isProduct(props.product)) {
        return props.product.compare_price ? (props.product.compare_price / 100).toFixed(2) : null;
    }
    return props.product.originalPrice ? props.product.originalPrice.toFixed(2) : null;
});

const productImage = computed(() => {
    if (isProduct(props.product)) {
        return getProductImage(props.product);
    }
    return props.product.image;
});

const productUrl = computed(() => {
    if (isProduct(props.product)) {
        return route('products.show', props.product.slug);
    }
    return props.product.href;
});

const productBrand = computed(() => {
    if (isProduct(props.product)) {
        return props.product.brand?.name;
    }
    return props.product.category; // FeaturedProduct uses category instead of brand
});

const productDescription = computed(() => {
    if (isProduct(props.product)) {
        return props.product.short_description;
    }
    return props.product.description;
});

const isOnSale = computed(() => {
    if (isProduct(props.product)) {
        return props.product.is_on_sale && props.product.compare_price && props.product.compare_price > props.product.price;
    }
    return props.product.isOnSale && props.product.originalPrice && props.product.originalPrice > props.product.price;
});

const discountPercentage = computed(() => {
    if (!isOnSale.value) return 0;

    if (isProduct(props.product) && props.product.compare_price) {
        return Math.round(((props.product.compare_price - props.product.price) / props.product.compare_price) * 100);
    }

    if (isFeaturedProduct(props.product) && props.product.originalPrice) {
        return Math.round(((props.product.originalPrice - props.product.price) / props.product.originalPrice) * 100);
    }

    return 0;
});

const isInStock = computed(() => {
    if (isProduct(props.product)) {
        return props.product.stock_status === 'in_stock';
    }
    return true; // Featured products are assumed to be in stock
});

// Helper function for Product images (same as in Products.vue)
const getProductImage = (product: Product): string => {
    // Try primary image first, then first image, then placeholder
    if (product.primaryImage?.image_path) {
        const imagePath = product.primaryImage.image_path;
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        // Convert to large size for better quality
        const sizedPath = imagePath
            .replace('/original/', '/large/')
            .replace('.jpeg', '-large.jpeg')
            .replace('.jpg', '-large.jpg')
            .replace('.png', '-large.png');
        return `/storage/${sizedPath}`;
    }
    if (product.images?.[0]?.image_path) {
        const imagePath = product.images[0].image_path;
        if (imagePath.startsWith('http')) {
            return imagePath;
        }
        const sizedPath = imagePath
            .replace('/original/', '/large/')
            .replace('.jpeg', '-large.jpeg')
            .replace('.jpg', '-large.jpg')
            .replace('.png', '-large.png');
        return `/storage/${sizedPath}`;
    }
    return 'https://via.placeholder.com/400x400/e5e7eb/6b7280?text=No+Image';
};

// Variant-specific classes
const cardClasses = computed(() => {
    const base = 'group relative overflow-hidden bg-white transition-all duration-300';

    switch (props.variant) {
        case 'premium':
            return `${base} rounded-2xl shadow-lg hover:-translate-y-1 hover:shadow-xl`;
        case 'compact':
            return `${base} rounded-lg border border-gray-200 hover:shadow-md`;
        default: // standard
            return `${base} rounded-lg border border-gray-200 hover:shadow-lg`;
    }
});

// Methods
const addToCart = async () => {
    if (isAddingToCart.value) return;

    console.log('Adding to cart - Product ID:', props.product.id);
    isAddingToCart.value = true;

    try {
        const result = await cartStore.addToCart(props.product.id, 1);
        console.log('Cart store result:', result);

        if (result.success) {
            console.log('Added to cart successfully:', result.message);
        } else {
            console.error('Failed to add to cart:', result.message);
            // Error feedback is handled by the cart store
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        // Error feedback is handled by the cart store
    } finally {
        isAddingToCart.value = false;
    }
};

const addToWishlist = () => {
    console.log('Add to wishlist:', props.product);
    // TODO: Implement add to wishlist functionality
};
</script>

<template>
    <div :class="cardClasses">
        <!-- Custom Badge -->
        <div
            v-if="customBadge"
            :class="['absolute top-3 left-3 z-10 rounded-full px-3 py-1 text-xs font-semibold text-white', customBadgeColor || 'bg-orange-500']"
        >
            {{ customBadge }}
        </div>

        <!-- Sale Badge -->
        <div v-if="isOnSale" class="absolute top-3 right-3 z-10 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white">
            -{{ discountPercentage }}%
        </div>

        <!-- Product Image -->
        <Link :href="productUrl" class="block">
            <div class="aspect-square overflow-hidden bg-gray-100">
                <img
                    :src="productImage"
                    :alt="productName"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    loading="lazy"
                />
            </div>
        </Link>

        <!-- Quick Actions (for standard variant) -->
        <div
            v-if="showQuickActions && variant === 'standard'"
            class="absolute top-2 right-2 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
        >
            <Button
                @click="addToCart"
                :disabled="isAddingToCart || !isInStock"
                size="sm"
                class="bg-white text-gray-900 shadow-md hover:bg-gray-100 disabled:opacity-50"
            >
                <div v-if="isAddingToCart" class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                <ShoppingCart v-else class="h-4 w-4" />
            </Button>
        </div>

        <!-- Product Info -->
        <div :class="variant === 'compact' ? 'p-3' : 'p-4'">
            <!-- Brand/Category -->
            <p
                v-if="showBrand && productBrand"
                :class="['mb-2 font-medium tracking-wide uppercase', variant === 'premium' ? 'text-sm text-teal-600' : 'text-xs text-gray-500']"
            >
                {{ productBrand }}
            </p>

            <!-- Product Name -->
            <Link :href="productUrl">
                <h3
                    :class="[
                        'mb-2 line-clamp-2 font-semibold text-gray-900 transition-colors hover:text-teal-600',
                        variant === 'compact' ? 'text-sm' : variant === 'premium' ? 'text-lg' : 'text-base',
                    ]"
                >
                    {{ productName }}
                </h3>
            </Link>

            <!-- Product Description -->
            <p v-if="showDescription && productDescription && variant !== 'compact'" class="mb-3 line-clamp-2 text-sm text-gray-600">
                {{ productDescription }}
            </p>

            <!-- Price -->
            <div class="mb-4 flex items-center space-x-2">
                <span :class="variant === 'compact' ? 'text-lg font-bold text-gray-900' : 'text-xl font-bold text-gray-900'">
                    ${{ productPrice }}
                </span>
                <span v-if="productComparePrice" class="text-sm text-gray-500 line-through"> ${{ productComparePrice }} </span>
            </div>

            <!-- Actions -->
            <div v-if="variant === 'premium'" class="flex items-center justify-center space-x-4">
                <!-- Add to Cart -->
                <button
                    @click="addToCart"
                    :disabled="isAddingToCart || !isInStock"
                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-white transition-all hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                    style="background-color: #111827 !important; color: white !important"
                    title="Add to Cart"
                >
                    <div v-if="isAddingToCart" class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                    <ShoppingCart v-else class="h-4 w-4" />
                </button>

                <!-- View Product -->
                <Link :href="productUrl">
                    <button
                        class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-gray-200 hover:text-gray-800"
                        title="View Product"
                    >
                        <Eye class="h-4 w-4" />
                    </button>
                </Link>

                <!-- Add to Wishlist -->
                <button
                    @click="addToWishlist"
                    class="group flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-all hover:bg-red-50 hover:text-red-500"
                    title="Add to Wishlist"
                >
                    <Heart class="h-4 w-4" />
                </button>
            </div>

            <!-- Full-width Add to Cart (for standard and compact variants) -->
            <Button v-else @click="addToCart" :disabled="isAddingToCart || !isInStock" :class="['w-full', variant === 'compact' ? 'text-sm' : '']">
                <div v-if="isAddingToCart" :class="variant === 'compact' ? 'mr-1 h-3 w-3' : 'mr-2 h-4 w-4'" class="animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                <ShoppingCart v-else :class="variant === 'compact' ? 'mr-1 h-3 w-3' : 'mr-2 h-4 w-4'" />
                {{ isAddingToCart ? 'Adding...' : (isInStock ? 'Add to Cart' : 'Out of Stock') }}
            </Button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
