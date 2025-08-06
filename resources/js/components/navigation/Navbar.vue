<script setup lang="ts">
import AppLogo from '@/components/common/AppLogo.vue';
import Icon from '@/components/common/Icon.vue';
import { usePermissions } from '@/composables/usePermissions';
import { useCategories } from '@/composables/useCategories';
import { useCartStore } from '@/stores/cart';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { isAdmin } = usePermissions();
const { categoryDropdowns, hasCategories, categoryError } = useCategories();
const cartStore = useCartStore();

// Get cart count - always use cart store as single source of truth
const cartCount = computed(() => {
    return cartStore.cartCount;
});
const isMobileMenuOpen = ref(false);

// Close mobile menu when clicking outside or pressing escape
const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeMobileMenu();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);

    // Initialize cart store with shared data if not already initialized
    if (!cartStore.cart) {
        const sharedCartSummary = page.props.cart_summary as any;
        if (sharedCartSummary && (sharedCartSummary.total_quantity > 0 || sharedCartSummary.id > 0)) {
            // Only initialize if there's actually cart data
            const cartData = {
                id: sharedCartSummary.id || 0,
                total_quantity: sharedCartSummary.total_quantity || 0,
                total_price: sharedCartSummary.total_price || 0,
                formatted_total: sharedCartSummary.formatted_total || '$0.00',
                items: [], // We don't have items in summary, but that's ok for navbar
            };
            cartStore.setInitialData(cartData);
        }
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});

// Primary navigation items (no dropdowns)
const primaryNavItems = [
    { name: 'Products', href: '/products' },
    { name: 'New Arrivals', href: '/products/new' },
    { name: 'Best Sellers', href: '/products/bestsellers' },
];

// Error handling for categories
const showCategoryError = computed(() => {
    return categoryError.value && !hasCategories.value;
});

// Fallback navigation when categories fail to load
const fallbackNavigation = computed(() => {
    if (hasCategories.value) return [];

    return [
        { name: 'All Products', href: '/products' },
        { name: 'New Arrivals', href: '/products/new' },
        { name: 'Best Sellers', href: '/products/bestsellers' },
    ];
});

// Brands dropdown
const brandsDropdown = {
    name: 'Brands',
    href: '/brands',
    subcategories: [
        { name: 'Apple', href: '/products?brand=apple' },
        { name: 'Samsung', href: '/products?brand=samsung' },
        { name: 'Sony', href: '/products?brand=sony' },
        { name: 'Microsoft', href: '/products?brand=microsoft' },
        { name: 'Google', href: '/products?brand=google' },
        { name: 'Dell', href: '/products?brand=dell' },
        { name: 'HP', href: '/products?brand=hp' },
        { name: 'Lenovo', href: '/products?brand=lenovo' },
    ],
};
</script>

<template>
    <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <Link href="/" class="flex items-center transition-opacity hover:opacity-80">
                        <AppLogo class="h-8 w-auto" />
                    </Link>
                </div>

                <!-- Main Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    <!-- Primary Navigation Items -->
                    <Link
                        v-for="item in primaryNavItems"
                        :key="item.name"
                        :href="item.href"
                        class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-teal-50 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                    >
                        {{ item.name }}
                    </Link>

                    <!-- Category Dropdowns -->
                    <div v-for="category in categoryDropdowns" :key="category.name" class="group relative">
                        <!-- Category Button -->
                        <Link
                            :href="category.href"
                            class="flex items-center rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-teal-50 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                        >
                            {{ category.name }}
                            <Icon name="chevron-down" class="ml-1 inline-block h-3 w-3 transition-transform group-hover:rotate-180" />
                        </Link>

                        <!-- Dropdown Menu -->
                        <div
                            class="invisible absolute top-full left-0 z-50 mt-1 w-56 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100"
                        >
                            <div class="rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                                <!-- View All Category Link -->
                                <Link
                                    :href="category.href"
                                    class="mb-1 block border-b border-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-colors duration-150 hover:bg-teal-50 hover:text-gray-900"
                                >
                                    View All {{ category.name }}
                                </Link>
                                <!-- Subcategory Links -->
                                <Link
                                    v-for="subcategory in category.subcategories"
                                    :key="subcategory.name"
                                    :href="subcategory.href"
                                    class="block px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-teal-50 hover:text-gray-900"
                                >
                                    {{ subcategory.name }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Brands Dropdown -->
                    <div class="group relative">
                        <!-- Brands Button -->
                        <Link
                            :href="brandsDropdown.href"
                            class="flex items-center rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-teal-50 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                        >
                            {{ brandsDropdown.name }}
                            <Icon name="chevron-down" class="ml-1 inline-block h-3 w-3 transition-transform group-hover:rotate-180" />
                        </Link>

                        <!-- Brands Dropdown Menu -->
                        <div
                            class="invisible absolute top-full left-0 z-50 mt-1 w-56 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100"
                        >
                            <div class="rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                                <!-- View All Brands Link -->
                                <Link
                                    :href="brandsDropdown.href"
                                    class="mb-1 block border-b border-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 transition-colors duration-150 hover:bg-teal-50 hover:text-gray-900"
                                >
                                    View All Brands
                                </Link>
                                <!-- Brand Links -->
                                <Link
                                    v-for="brand in brandsDropdown.subcategories"
                                    :key="brand.name"
                                    :href="brand.href"
                                    class="block px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-teal-50 hover:text-gray-900"
                                >
                                    {{ brand.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side: Cart and User -->
                <div class="flex items-center space-x-3">
                    <!-- Mobile Menu Button -->
                    <button
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="rounded-lg p-2 text-gray-900 transition-all duration-200 hover:bg-gray-100 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none md:hidden"
                    >
                        <Icon :name="isMobileMenuOpen ? 'x' : 'menu'" class="h-6 w-6" />
                    </button>

                    <!-- Shopping Cart -->
                    <Link
                        href="/cart"
                        class="relative rounded-lg p-2 text-gray-900 transition-all duration-200 hover:bg-gray-100 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <Icon name="shopping-cart" class="h-5 w-5 text-gray-900" />
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#00c9a7] text-xs font-semibold text-white shadow-sm"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>

                    <!-- User Menu -->
                    <div class="group relative hidden sm:block">
                        <button
                            class="flex items-center space-x-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-900 transition-all duration-200 hover:bg-gray-100 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Icon name="user" class="h-5 w-5" />
                            <span v-if="user" class="hidden sm:block">{{ user.name }}</span>
                            <span v-else class="hidden sm:block">Account</span>
                            <Icon name="chevron-down" class="h-4 w-4 transition-transform group-hover:rotate-180" />
                        </button>

                        <!-- User Dropdown -->
                        <div
                            class="invisible absolute top-full right-0 z-50 mt-1 w-48 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100"
                        >
                            <div class="rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                                <template v-if="user">
                                    <Link
                                        v-if="isAdmin"
                                        href="/admin"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="layout-dashboard" class="h-4 w-4" />
                                        <span>Dashboard</span>
                                    </Link>
                                    <Link
                                        href="/profile"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="user" class="h-4 w-4" />
                                        <span>Profile</span>
                                    </Link>
                                    <Link
                                        href="/orders"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="package" class="h-4 w-4" />
                                        <span>My Orders</span>
                                    </Link>
                                    <Link
                                        href="/settings"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="settings" class="h-4 w-4" />
                                        <span>Settings</span>
                                    </Link>
                                    <div class="my-1 h-px bg-gray-200"></div>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-red-600 transition-colors duration-150 hover:bg-red-50 hover:text-red-700"
                                    >
                                        <Icon name="log-out" class="h-4 w-4" />
                                        <span>Logout</span>
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        href="/login"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="log-in" class="h-4 w-4" />
                                        <span>Login</span>
                                    </Link>
                                    <Link
                                        href="/register"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="user-plus" class="h-4 w-4" />
                                        <span>Register</span>
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="isMobileMenuOpen" class="border-t border-gray-200 bg-white md:hidden">
            <div class="space-y-1 px-4 py-3">
                <!-- Mobile Navigation -->
                <div class="space-y-1">
                    <!-- Primary Navigation Items -->
                    <div class="mb-4 space-y-1">
                        <Link
                            v-for="item in primaryNavItems"
                            :key="item.name"
                            :href="item.href"
                            class="block rounded-md px-3 py-2 text-sm font-semibold text-gray-800 transition-colors duration-150 hover:bg-teal-50 hover:text-gray-900"
                            @click="isMobileMenuOpen = false"
                        >
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Category Sections -->
                    <div v-for="category in categoryDropdowns" :key="category.name" class="space-y-1">
                        <Link
                            :href="category.href"
                            class="block rounded-md px-3 py-2 text-sm font-semibold text-gray-900 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            {{ category.name }}
                        </Link>
                        <div class="space-y-1 pl-4">
                            <Link
                                v-for="subcategory in category.subcategories"
                                :key="subcategory.name"
                                :href="subcategory.href"
                                class="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                                @click="isMobileMenuOpen = false"
                            >
                                {{ subcategory.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Brands Section -->
                    <div class="mt-4 space-y-1">
                        <Link
                            :href="brandsDropdown.href"
                            class="block rounded-md px-3 py-2 text-sm font-semibold text-gray-900 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            {{ brandsDropdown.name }}
                        </Link>
                        <div class="space-y-1 pl-4">
                            <Link
                                v-for="brand in brandsDropdown.subcategories"
                                :key="brand.name"
                                :href="brand.href"
                                class="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                                @click="isMobileMenuOpen = false"
                            >
                                {{ brand.name }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Mobile User Menu -->
                <div class="mt-3 border-t border-gray-200 pt-3">
                    <template v-if="user">
                        <div class="px-3 py-2 text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <Link
                            v-if="isAdmin"
                            href="/dashboard"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="layout-dashboard" class="h-4 w-4" />
                            <span>Dashboard</span>
                        </Link>
                        <Link
                            href="/profile"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="user" class="h-4 w-4" />
                            <span>Profile</span>
                        </Link>
                        <Link
                            href="/orders"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="package" class="h-4 w-4" />
                            <span>My Orders</span>
                        </Link>
                        <Link
                            href="/settings"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="settings" class="h-4 w-4" />
                            <span>Settings</span>
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-red-600 transition-colors duration-150 hover:bg-red-50 hover:text-red-700"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="log-out" class="h-4 w-4" />
                            <span>Logout</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="log-in" class="h-4 w-4" />
                            <span>Login</span>
                        </Link>
                        <Link
                            href="/register"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="user-plus" class="h-4 w-4" />
                            <span>Register</span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>
