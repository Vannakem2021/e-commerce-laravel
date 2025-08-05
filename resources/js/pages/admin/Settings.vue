<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Settings, Save } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Settings - Admin" />

    <AdminLayout>
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-50">
                        <Settings class="h-6 w-6 text-gray-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Settings</h1>
                        <p class="text-muted-foreground">Configure system settings</p>
                    </div>
                </div>
                <Button class="flex items-center space-x-2">
                    <Save class="h-4 w-4" />
                    <span>Save Changes</span>
                </Button>
            </div>

            <!-- Content Placeholder -->
            <div class="rounded-lg border bg-card p-8">
                <div class="text-center space-y-4">
                    <Settings class="h-16 w-16 text-muted-foreground mx-auto" />
                    <h3 class="text-lg font-semibold text-foreground">System Settings</h3>
                    <p class="text-muted-foreground">Settings management functionality will be implemented here</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
