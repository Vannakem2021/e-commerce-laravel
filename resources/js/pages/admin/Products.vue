<!-- This file is deprecated. Use admin/products/Index.vue instead -->
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

// Redirect to the new products index page
onMounted(() => {
    router.get(route('admin.products.index'));
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center">
        <div class="text-center">
            <p class="text-muted-foreground">Redirecting to products...</p>
        </div>
    </div>
</template>
