<script setup lang="ts">
import { cn } from '@/lib/utils';
import { type HTMLAttributes, computed } from 'vue';

interface Props {
    defaultValue?: string | number;
    modelValue?: string | number;
    class?: HTMLAttributes['class'];
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    min?: string | number;
    max?: string | number;
    step?: string | number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text'
});

const emits = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const delegatedProps = computed(() => {
    const { class: _, modelValue, ...delegated } = props;
    return delegated;
});
</script>

<template>
    <input
        :class="cn(
            'flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            props.class
        )"
        :value="modelValue"
        v-bind="delegatedProps"
        @input="emits('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
</template>
