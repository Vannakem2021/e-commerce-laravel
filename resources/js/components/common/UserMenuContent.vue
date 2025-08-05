<script setup lang="ts">
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
