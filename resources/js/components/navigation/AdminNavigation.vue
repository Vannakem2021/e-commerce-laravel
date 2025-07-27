<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { Link } from '@inertiajs/vue3';
import { BarChart3, FolderTree, LayoutDashboard, Package, Settings, ShoppingCart, UserCheck, Users, Warehouse } from 'lucide-vue-next';
import { computed } from 'vue';

const { hasPermission, hasAnyPermission, isAdmin } = usePermissions();

// Define navigation items with permission requirements
const navigationItems = computed(() => [
    {
        label: 'Dashboard',
        items: [
            {
                title: 'Overview',
                href: '/dashboard',
                icon: LayoutDashboard,
                permission: 'view-dashboard',
            },
        ],
    },
    {
        label: 'E-commerce',
        items: [
            {
                title: 'Products',
                href: '/admin/products',
                icon: Package,
                permission: 'view-products',
            },
            {
                title: 'Categories',
                href: '/admin/categories',
                icon: FolderTree,
                permission: 'view-categories',
            },
            {
                title: 'Orders',
                href: '/admin/orders',
                icon: ShoppingCart,
                permission: 'view-orders',
            },
            {
                title: 'Inventory',
                href: '/admin/inventory',
                icon: Warehouse,
                permission: 'view-inventory',
            },
        ],
    },
    {
        label: 'User Management',
        items: [
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
                permission: 'view-users',
            },
            {
                title: 'Customers',
                href: '/admin/customers',
                icon: UserCheck,
                permission: 'view-customers',
            },
        ],
    },
    {
        label: 'Analytics',
        items: [
            {
                title: 'Reports',
                href: '/admin/reports',
                icon: BarChart3,
                permission: 'view-reports',
            },
        ],
    },
    {
        label: 'System',
        items: [
            {
                title: 'Settings',
                href: '/admin/settings',
                icon: Settings,
                permission: 'manage-settings',
            },
        ],
    },
]);

// Filter navigation items based on user permissions
const filteredNavigation = computed(() => {
    return navigationItems.value
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => hasPermission(item.permission)),
        }))
        .filter((group) => group.items.length > 0);
});

// Show admin navigation only for admin users or users with specific permissions
const showAdminNavigation = computed(() => {
    return isAdmin.value || hasAnyPermission(['view-products', 'view-orders', 'view-users', 'view-reports', 'manage-settings']);
});
</script>

<template>
    <div v-if="showAdminNavigation">
        <template v-for="group in filteredNavigation" :key="group.label">
            <SidebarGroup>
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in group.items" :key="item.href">
                            <SidebarMenuButton :as-child="true">
                                <Link :href="item.href">
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </template>
    </div>
</template>
