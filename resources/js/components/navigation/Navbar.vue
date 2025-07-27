<script setup lang="ts">
import AppLogo from '@/components/common/AppLogo.vue';
import Icon from '@/components/common/Icon.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cartItemCount = ref(3); // This would come from your cart state
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
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});

const categories = [
    {
        name: 'Smartphones',
        subcategories: [
            { name: 'iPhone', href: '/smartphones/iphone' },
            { name: 'Samsung Galaxy', href: '/smartphones/samsung' },
            { name: 'Google Pixel', href: '/smartphones/pixel' },
            { name: 'OnePlus', href: '/smartphones/oneplus' },
            { name: 'Xiaomi', href: '/smartphones/xiaomi' },
        ],
    },
    {
        name: 'Laptops',
        subcategories: [
            { name: 'MacBook', href: '/laptops/macbook' },
            { name: 'Windows Laptops', href: '/laptops/windows' },
            { name: 'Gaming Laptops', href: '/laptops/gaming' },
            { name: 'Ultrabooks', href: '/laptops/ultrabooks' },
            { name: 'Chromebooks', href: '/laptops/chromebooks' },
        ],
    },
    {
        name: 'Tablets',
        subcategories: [
            { name: 'iPad', href: '/tablets/ipad' },
            { name: 'Android Tablets', href: '/tablets/android' },
            { name: 'Windows Tablets', href: '/tablets/windows' },
            { name: 'E-Readers', href: '/tablets/e-readers' },
        ],
    },
    {
        name: 'Audio',
        subcategories: [
            { name: 'Headphones', href: '/audio/headphones' },
            { name: 'Earbuds', href: '/audio/earbuds' },
            { name: 'Speakers', href: '/audio/speakers' },
            { name: 'Soundbars', href: '/audio/soundbars' },
            { name: 'Home Audio', href: '/audio/home-audio' },
        ],
    },
    {
        name: 'Gaming',
        subcategories: [
            { name: 'PlayStation', href: '/gaming/playstation' },
            { name: 'Xbox', href: '/gaming/xbox' },
            { name: 'Nintendo Switch', href: '/gaming/nintendo' },
            { name: 'PC Gaming', href: '/gaming/pc' },
            { name: 'Gaming Accessories', href: '/gaming/accessories' },
        ],
    },
    {
        name: 'Smart Home',
        subcategories: [
            { name: 'Smart Speakers', href: '/smart-home/speakers' },
            { name: 'Security Cameras', href: '/smart-home/cameras' },
            { name: 'Smart Lighting', href: '/smart-home/lighting' },
            { name: 'Thermostats', href: '/smart-home/thermostats' },
            { name: 'Smart Plugs', href: '/smart-home/plugs' },
        ],
    },
    {
        name: 'Accessories',
        subcategories: [
            { name: 'Phone Cases', href: '/accessories/cases' },
            { name: 'Chargers & Cables', href: '/accessories/chargers' },
            { name: 'Screen Protectors', href: '/accessories/screen-protectors' },
            { name: 'Power Banks', href: '/accessories/power-banks' },
            { name: 'Stands & Mounts', href: '/accessories/stands' },
        ],
    },
];
</script>

<template>
    <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <Link href="/" class="flex items-center transition-opacity hover:opacity-80">
                        <AppLogo class="h-8 w-auto" />
                    </Link>
                </div>

                <!-- Categories Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    <div v-for="category in categories" :key="category.name" class="group relative">
                        <!-- Category Button -->
                        <button
                            class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-teal-50 hover:text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:outline-none"
                        >
                            {{ category.name }}
                            <Icon name="chevron-down" class="ml-1 inline-block h-3 w-3 transition-transform group-hover:rotate-180" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="invisible absolute top-full left-0 z-50 mt-1 w-56 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100"
                        >
                            <div class="rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                                <Link
                                    v-for="subcategory in category.subcategories"
                                    :key="subcategory.name"
                                    :href="subcategory.href"
                                    class="block px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-teal-50 hover:text-teal-700"
                                >
                                    {{ subcategory.name }}
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
                            v-if="cartItemCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#00c9a7] text-xs font-semibold text-white shadow-sm"
                        >
                            {{ cartItemCount }}
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
                                        href="/dashboard"
                                        class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-teal-600"
                                    >
                                        <Icon name="user" class="h-4 w-4" />
                                        <span>Dashboard</span>
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
                <!-- Mobile Categories -->
                <div class="space-y-1">
                    <div v-for="category in categories" :key="category.name" class="space-y-1">
                        <div class="px-3 py-2 text-sm font-medium text-gray-900">{{ category.name }}</div>
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
                </div>

                <!-- Mobile User Menu -->
                <div class="mt-3 border-t border-gray-200 pt-3">
                    <template v-if="user">
                        <div class="px-3 py-2 text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <Link
                            href="/dashboard"
                            class="flex items-center space-x-3 rounded-md px-3 py-2 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-teal-600"
                            @click="isMobileMenuOpen = false"
                        >
                            <Icon name="user" class="h-4 w-4" />
                            <span>Dashboard</span>
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
