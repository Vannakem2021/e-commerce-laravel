<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShoppingCart, Filter } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Orders - Admin" />

    <AdminLayout>
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                        <ShoppingCart class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Orders</h1>
                        <p class="text-muted-foreground">Manage customer orders</p>
                    </div>
                </div>
                <Button variant="outline" class="flex items-center space-x-2">
                    <Filter class="h-4 w-4" />
                    <span>Filter Orders</span>
                </Button>
            </div>

            <!-- Content Placeholder -->
            <div class="rounded-lg border bg-card p-8">
                <div class="text-center space-y-4">
                    <ShoppingCart class="h-16 w-16 text-muted-foreground mx-auto" />
                    <h3 class="text-lg font-semibold text-foreground">Orders Management</h3>
                    <p class="text-muted-foreground">Order management functionality will be implemented here</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
