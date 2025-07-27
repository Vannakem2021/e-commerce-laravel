<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
    size: 'md'
});

// Generate user initials from name
const userInitials = computed(() => {
    if (!props.user.name) return 'U';
    
    const names = props.user.name.split(' ');
    if (names.length >= 2) {
        return `${names[0][0]}${names[1][0]}`.toUpperCase();
    }
    return names[0][0].toUpperCase();
});

// Avatar size classes
const avatarSizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-6 w-6';
        case 'lg':
            return 'h-12 w-12';
        default:
            return 'h-8 w-8';
    }
});

// Text size classes
const textSizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return {
                name: 'text-sm',
                email: 'text-xs'
            };
        case 'lg':
            return {
                name: 'text-lg',
                email: 'text-base'
            };
        default:
            return {
                name: 'text-sm',
                email: 'text-xs'
            };
    }
});
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- User Avatar -->
        <Avatar :class="avatarSizeClasses">
            <AvatarImage 
                v-if="user.avatar" 
                :src="user.avatar" 
                :alt="user.name || 'User avatar'" 
            />
            <AvatarFallback class="bg-teal-100 text-teal-700 font-medium">
                {{ userInitials }}
            </AvatarFallback>
        </Avatar>
        
        <!-- User Details -->
        <div class="flex flex-col min-w-0 flex-1">
            <div :class="['font-medium text-gray-900 dark:text-gray-100 truncate', textSizeClasses.name]">
                {{ user.name || 'User' }}
            </div>
            <div 
                v-if="showEmail && user.email" 
                :class="['text-gray-500 dark:text-gray-400 truncate', textSizeClasses.email]"
            >
                {{ user.email }}
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure text truncation works properly */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Smooth transitions for theme changes */
.text-gray-900,
.text-gray-100,
.text-gray-500,
.text-gray-400 {
    transition: color 0.2s ease-in-out;
}
</style>
