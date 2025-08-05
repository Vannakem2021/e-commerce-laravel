<script setup lang="ts">
import AppContent from '@/components/layout/AppContent.vue';
import AppShell from '@/components/layout/AppShell.vue';
import AppSidebar from '@/components/layout/AppSidebar.vue';
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <slot />
        </AppContent>
    </AppShell>
</template>
