<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    name: string;
    class?: string;
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    // Convert kebab-case to PascalCase (e.g., "shopping-bag" -> "ShoppingBag")
    const iconName = props.name
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
    return (icons as Record<string, any>)[iconName];
});
</script>

<template>
    <component v-if="icon" :is="icon" :class="className" :size="size" :stroke-width="strokeWidth" :color="color" />
    <div v-else :class="className" class="flex items-center justify-center bg-gray-200 text-xs text-gray-500">?</div>
</template>
