import type { Auth } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();
    const auth = computed(() => page.props.auth as Auth);

    /**
     * Check if user has a specific permission
     */
    const hasPermission = (permission: string): boolean => {
        if (!auth.value?.permissions) return false;
        return auth.value.permissions.includes(permission);
    };

    /**
     * Check if user has any of the specified permissions
     */
    const hasAnyPermission = (permissions: string[]): boolean => {
        if (!auth.value?.permissions) return false;
        return permissions.some((permission) => auth.value.permissions.includes(permission));
    };

    /**
     * Check if user has all of the specified permissions
     */
    const hasAllPermissions = (permissions: string[]): boolean => {
        if (!auth.value?.permissions) return false;
        return permissions.every((permission) => auth.value.permissions.includes(permission));
    };

    /**
     * Check if user has a specific role
     */
    const hasRole = (role: string): boolean => {
        if (!auth.value?.roles) return false;
        return auth.value.roles.includes(role);
    };

    /**
     * Check if user has any of the specified roles
     */
    const hasAnyRole = (roles: string[]): boolean => {
        if (!auth.value?.roles) return false;
        return roles.some((role) => auth.value.roles.includes(role));
    };

    /**
     * Check if user has all of the specified roles
     */
    const hasAllRoles = (roles: string[]): boolean => {
        if (!auth.value?.roles) return false;
        return roles.every((role) => auth.value.roles.includes(role));
    };

    /**
     * Check if user is admin
     */
    const isAdmin = computed(() => hasRole('admin'));

    /**
     * Check if user is regular user
     */
    const isUser = computed(() => hasRole('user'));

    /**
     * Check if user can manage products
     */
    const canManageProducts = computed(() => hasPermission('manage-products'));

    /**
     * Check if user can manage orders
     */
    const canManageOrders = computed(() => hasPermission('manage-orders'));

    /**
     * Check if user can manage users
     */
    const canManageUsers = computed(() => hasPermission('manage-users'));

    /**
     * Check if user can view reports
     */
    const canViewReports = computed(() => hasPermission('view-reports'));

    return {
        // Raw auth data
        auth,

        // Permission checks
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,

        // Role checks
        hasRole,
        hasAnyRole,
        hasAllRoles,

        // Convenience role checks
        isAdmin,
        isUser,

        // Convenience permission checks
        canManageProducts,
        canManageOrders,
        canManageUsers,
        canViewReports,
    };
}
