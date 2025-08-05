<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { BarChart3, DollarSign, Package, ShoppingCart, TrendingUp, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Sample dashboard data - in real app this would come from props
const stats = [
    {
        title: 'Total Revenue',
        value: '$45,231.89',
        change: '+20.1%',
        icon: DollarSign,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
    },
    {
        title: 'Orders',
        value: '2,350',
        change: '+180.1%',
        icon: ShoppingCart,
        color: 'text-blue-600',
        bgColor: 'bg-blue-50',
    },
    {
        title: 'Products',
        value: '12,234',
        change: '+19%',
        icon: Package,
        color: 'text-purple-600',
        bgColor: 'bg-purple-50',
    },
    {
        title: 'Active Users',
        value: '573',
        change: '+201',
        icon: Users,
        color: 'text-orange-600',
        bgColor: 'bg-orange-50',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-8">
            <!-- Welcome Header -->
            <div class="mb-6 space-y-2 sm:mb-8">
                <h1 class="text-2xl font-bold text-foreground sm:text-3xl">Admin Dashboard</h1>
                <p class="text-sm text-muted-foreground sm:text-base">
                    Welcome back, {{ user.name }}! Here's what's happening with your store today.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="mb-6 grid gap-4 sm:mb-8 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.title" class="rounded-lg border bg-card p-4 sm:p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">{{ stat.title }}</h3>
                        <div :class="[stat.bgColor, 'rounded-lg p-2']">
                            <component :is="stat.icon" :class="[stat.color, 'h-4 w-4']" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-xl font-bold text-foreground sm:text-2xl">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-green-600">{{ stat.change }}</span> from last month
                        </p>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics Section -->
            <div class="grid gap-4 lg:grid-cols-7">
                <!-- Revenue Chart -->
                <div class="rounded-lg border bg-card p-4 sm:p-6 lg:col-span-4">
                    <div class="flex items-center justify-between space-y-0 pb-4">
                        <h3 class="text-base font-semibold text-foreground sm:text-lg">Revenue Overview</h3>
                        <div class="flex items-center space-x-2">
                            <BarChart3 class="h-4 w-4 text-muted-foreground" />
                        </div>
                    </div>
                    <div class="flex h-[200px] items-center justify-center rounded-lg border-2 border-dashed border-border sm:h-[300px]">
                        <div class="space-y-2 text-center">
                            <TrendingUp class="mx-auto h-8 w-8 text-muted-foreground sm:h-12 sm:w-12" />
                            <p class="text-sm text-muted-foreground sm:text-base">Revenue chart will be displayed here</p>
                            <p class="text-xs text-muted-foreground sm:text-sm">Integration with charting library needed</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-lg border bg-card p-4 sm:p-6 lg:col-span-3">
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold text-foreground sm:text-lg">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-foreground">New order received</p>
                                    <p class="text-xs text-muted-foreground">Order #1234 - $299.99</p>
                                </div>
                                <p class="text-xs text-muted-foreground">2m ago</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-foreground">Product updated</p>
                                    <p class="text-xs text-muted-foreground">iPhone 15 Pro - Stock updated</p>
                                </div>
                                <p class="text-xs text-muted-foreground">5m ago</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-foreground">New user registered</p>
                                    <p class="text-xs text-muted-foreground">john.doe@example.com</p>
                                </div>
                                <p class="text-xs text-muted-foreground">10m ago</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium text-foreground">Low stock alert</p>
                                    <p class="text-xs text-muted-foreground">MacBook Air - 3 items left</p>
                                </div>
                                <p class="text-xs text-muted-foreground">15m ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-foreground">Quick Actions</h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <button class="flex items-center space-x-3 rounded-lg border border-border p-4 text-left transition-colors hover:bg-accent">
                        <Package class="h-5 w-5 text-blue-600" />
                        <div>
                            <p class="font-medium text-foreground">Add Product</p>
                            <p class="text-sm text-muted-foreground">Create new product</p>
                        </div>
                    </button>
                    <button class="flex items-center space-x-3 rounded-lg border border-border p-4 text-left transition-colors hover:bg-accent">
                        <ShoppingCart class="h-5 w-5 text-green-600" />
                        <div>
                            <p class="font-medium text-foreground">View Orders</p>
                            <p class="text-sm text-muted-foreground">Manage orders</p>
                        </div>
                    </button>
                    <button class="flex items-center space-x-3 rounded-lg border border-border p-4 text-left transition-colors hover:bg-accent">
                        <Users class="h-5 w-5 text-purple-600" />
                        <div>
                            <p class="font-medium text-foreground">User Management</p>
                            <p class="text-sm text-muted-foreground">Manage users</p>
                        </div>
                    </button>
                    <button class="flex items-center space-x-3 rounded-lg border border-border p-4 text-left transition-colors hover:bg-accent">
                        <BarChart3 class="h-5 w-5 text-orange-600" />
                        <div>
                            <p class="font-medium text-foreground">Analytics</p>
                            <p class="text-sm text-muted-foreground">View reports</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
