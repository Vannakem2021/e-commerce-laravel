<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';

interface Props {
    /**
     * Required permission to show the content
     */
    permission?: string;
    
    /**
     * Array of permissions - user needs ANY of these
     */
    anyPermissions?: string[];
    
    /**
     * Array of permissions - user needs ALL of these
     */
    allPermissions?: string[];
    
    /**
     * Required role to show the content
     */
    role?: string;
    
    /**
     * Array of roles - user needs ANY of these
     */
    anyRoles?: string[];
    
    /**
     * Array of roles - user needs ALL of these
     */
    allRoles?: string[];
    
    /**
     * Show content when user DOESN'T have permission (inverse logic)
     */
    inverse?: boolean;
    
    /**
     * Fallback content to show when permission check fails
     */
    fallback?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    inverse: false,
    fallback: false,
});

const { 
    hasPermission, 
    hasAnyPermission, 
    hasAllPermissions,
    hasRole,
    hasAnyRole,
    hasAllRoles
} = usePermissions();

/**
 * Check if user meets the permission/role requirements
 */
const hasAccess = computed(() => {
    let permissionCheck = true;
    let roleCheck = true;

    // Check single permission
    if (props.permission) {
        permissionCheck = hasPermission(props.permission);
    }
    
    // Check any permissions
    if (props.anyPermissions && props.anyPermissions.length > 0) {
        permissionCheck = permissionCheck && hasAnyPermission(props.anyPermissions);
    }
    
    // Check all permissions
    if (props.allPermissions && props.allPermissions.length > 0) {
        permissionCheck = permissionCheck && hasAllPermissions(props.allPermissions);
    }

    // Check single role
    if (props.role) {
        roleCheck = hasRole(props.role);
    }
    
    // Check any roles
    if (props.anyRoles && props.anyRoles.length > 0) {
        roleCheck = roleCheck && hasAnyRole(props.anyRoles);
    }
    
    // Check all roles
    if (props.allRoles && props.allRoles.length > 0) {
        roleCheck = roleCheck && hasAllRoles(props.allRoles);
    }

    const result = permissionCheck && roleCheck;
    
    // Apply inverse logic if specified
    return props.inverse ? !result : result;
});

/**
 * Determine what content to show
 */
const showContent = computed(() => {
    return hasAccess.value;
});

const showFallback = computed(() => {
    return !hasAccess.value && props.fallback;
});
</script>

<template>
    <!-- Main content when user has access -->
    <div v-if="showContent">
        <slot />
    </div>
    
    <!-- Fallback content when user doesn't have access -->
    <div v-else-if="showFallback">
        <slot name="fallback">
            <div class="text-center py-8 text-gray-500">
                <p>You don't have permission to view this content.</p>
            </div>
        </slot>
    </div>
</template>
