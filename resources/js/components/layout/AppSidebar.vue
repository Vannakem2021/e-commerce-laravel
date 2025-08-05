<script setup lang="ts">
import AppLogo from '@/components/common/AppLogo.vue';
import NavAccount from '@/components/navigation/NavAccount.vue';
import NavFooter from '@/components/navigation/NavFooter.vue';
import NavMain from '@/components/navigation/NavMain.vue';
import NavUser from '@/components/navigation/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Settings, UserCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const { isAdmin } = usePermissions();

// Platform navigation items (admin only) - removed dashboard from sidebar
const platformNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    // Dashboard is now accessed via /admin route, not in sidebar
    // Admin users can access it directly via URL or admin panel

    return items;
});

// User navigation items (for all users)
const userNavItems = computed<NavItem[]>(() => [
    {
        title: 'Profile',
        href: '/settings/profile',
        icon: UserCircle,
    },
    {
        title: 'Settings',
        href: '/settings/password',
        icon: Settings,
    },
]);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('home')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="platformNavItems" />
            <NavAccount :items="userNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
