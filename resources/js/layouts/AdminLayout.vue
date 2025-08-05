<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { type User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart3, ChevronDown, Home, Layers, LayoutGrid, LogOut, Menu, Package, Settings, ShoppingCart, Tag, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user as User);

// Admin navigation items
const adminNavItems = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: LayoutGrid,
        active: page.url === '/admin',
    },
    {
        title: 'Products',
        href: '/admin/products',
        icon: Package,
        active: page.url.startsWith('/admin/products') || page.url.startsWith('/admin/categories') || page.url.startsWith('/admin/brands'),
        children: [
            {
                title: 'All Products',
                href: '/admin/products',
                icon: Package,
                active: page.url === '/admin/products' || page.url.startsWith('/admin/products/'),
            },
            {
                title: 'Categories',
                href: '/admin/categories',
                icon: Layers,
                active: page.url.startsWith('/admin/categories'),
            },
            {
                title: 'Brands',
                href: '/admin/brands',
                icon: Tag,
                active: page.url.startsWith('/admin/brands'),
            },
        ],
    },
    {
        title: 'Orders',
        href: '/admin/orders',
        icon: ShoppingCart,
        active: page.url.startsWith('/admin/orders'),
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
        active: page.url.startsWith('/admin/users'),
    },
    {
        title: 'Reports',
        href: '/admin/reports',
        icon: BarChart3,
        active: page.url.startsWith('/admin/reports'),
    },
    {
        title: 'Settings',
        href: '/admin/settings',
        icon: Settings,
        active: page.url.startsWith('/admin/settings'),
    },
];

const logout = () => {
    // Handle logout
    window.location.href = '/logout';
};
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <!-- Mobile Header -->
        <header class="fixed top-0 right-0 left-0 z-50 flex h-16 items-center justify-between border-b bg-card px-4 md:hidden">
            <Sheet>
                <SheetTrigger as-child>
                    <Button variant="ghost" size="icon">
                        <Menu class="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-64 p-0">
                    <div class="flex h-full flex-col">
                        <!-- Mobile Logo -->
                        <div class="flex h-16 items-center border-b px-4">
                            <Link href="/admin" class="flex items-center space-x-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                                    <span class="text-sm font-bold text-primary-foreground">A</span>
                                </div>
                                <span class="font-bold">Admin Panel</span>
                            </Link>
                        </div>

                        <!-- Mobile Navigation -->
                        <nav class="flex-1 space-y-2 px-4 py-4">
                            <template v-for="item in adminNavItems" :key="item.title">
                                <!-- Main Navigation Item -->
                                <Link
                                    :href="item.href"
                                    :class="[
                                        'flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                        item.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                    ]"
                                >
                                    <component :is="item.icon" class="mr-3 h-4 w-4" />
                                    {{ item.title }}
                                </Link>

                                <!-- Sub Navigation Items -->
                                <div v-if="item.children && item.active" class="ml-6 space-y-1">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.title"
                                        :href="child.href"
                                        :class="[
                                            'flex items-center rounded-lg px-3 py-2 text-sm transition-colors',
                                            child.active
                                                ? 'bg-accent font-medium text-accent-foreground'
                                                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                        ]"
                                    >
                                        <component :is="child.icon" class="mr-3 h-4 w-4" />
                                        {{ child.title }}
                                    </Link>
                                </div>
                            </template>
                        </nav>

                        <!-- Mobile User Menu -->
                        <div class="border-t p-4">
                            <div class="mb-4 flex items-center space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">{{ user.name }}</span>
                                    <span class="text-xs text-muted-foreground">{{ user.email }}</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Link href="/" class="flex items-center rounded-lg px-3 py-2 text-sm hover:bg-accent">
                                    <Home class="mr-2 h-4 w-4" />
                                    <span>View Store</span>
                                </Link>
                                <Link href="/settings/profile" class="flex items-center rounded-lg px-3 py-2 text-sm hover:bg-accent">
                                    <Users class="mr-2 h-4 w-4" />
                                    <span>Profile Settings</span>
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    class="flex items-center rounded-lg px-3 py-2 text-sm text-destructive hover:bg-accent"
                                >
                                    <LogOut class="mr-2 h-4 w-4" />
                                    <span>Log out</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </SheetContent>
            </Sheet>

            <!-- Mobile Logo -->
            <Link href="/admin" class="flex items-center space-x-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                    <span class="text-sm font-bold text-primary-foreground">A</span>
                </div>
                <span class="font-bold">Admin Panel</span>
            </Link>

            <!-- Mobile User Avatar -->
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                {{ user.name.charAt(0).toUpperCase() }}
            </div>
        </header>

        <!-- Desktop Sidebar -->
        <aside class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
            <div class="flex flex-grow flex-col border-r bg-card shadow-sm">
                <!-- Logo -->
                <div class="flex h-16 items-center border-b px-4">
                    <Link href="/admin" class="flex items-center space-x-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                            <span class="text-sm font-bold text-primary-foreground">A</span>
                        </div>
                        <span class="font-bold">Admin Panel</span>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-2 px-4 py-4">
                    <template v-for="item in adminNavItems" :key="item.title">
                        <!-- Main Navigation Item -->
                        <Link
                            :href="item.href"
                            :class="[
                                'flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                item.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                            ]"
                        >
                            <component :is="item.icon" class="mr-3 h-4 w-4" />
                            {{ item.title }}
                        </Link>

                        <!-- Sub Navigation Items -->
                        <div v-if="item.children && item.active" class="ml-6 space-y-1">
                            <Link
                                v-for="child in item.children"
                                :key="child.title"
                                :href="child.href"
                                :class="[
                                    'flex items-center rounded-lg px-3 py-2 text-sm transition-colors',
                                    child.active
                                        ? 'bg-accent font-medium text-accent-foreground'
                                        : 'text-muted-foreground hover:bg-accent/50 hover:text-accent-foreground',
                                ]"
                            >
                                <component :is="child.icon" class="mr-3 h-3 w-3" />
                                {{ child.title }}
                            </Link>
                        </div>
                    </template>
                </nav>

                <!-- User Menu -->
                <div class="border-t p-4">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="w-full justify-start">
                                <div class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex flex-col items-start">
                                    <span class="text-sm font-medium">{{ user.name }}</span>
                                    <span class="text-xs text-muted-foreground">{{ user.email }}</span>
                                </div>
                                <ChevronDown class="ml-auto h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuItem as-child>
                                <Link href="/" class="flex items-center">
                                    <Home class="mr-2 h-4 w-4" />
                                    <span>View Store</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/settings/profile" class="flex items-center">
                                    <Users class="mr-2 h-4 w-4" />
                                    <span>Profile Settings</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/settings/password" class="flex items-center">
                                    <Settings class="mr-2 h-4 w-4" />
                                    <span>Account Settings</span>
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" class="flex items-center text-destructive">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    <span>Log out</span>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 pt-16 md:ml-64 md:pt-0">
            <slot />
        </main>
    </div>
</template>
