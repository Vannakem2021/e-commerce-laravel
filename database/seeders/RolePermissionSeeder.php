<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache before seeding
        app()['cache']->forget(config('permission.cache.key'));

        // Create permissions
        $permissions = [
            // Dashboard & Profile - Essential for all users
            'view-dashboard',
            'edit-profile',
            'view-settings',
            'change-password',
            'view-profile',

            // User Management - Admin only
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Product Management - Admin/Staff
            'manage-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Category Management - Admin/Staff
            'manage-categories',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Order Management
            'manage-orders',
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            'process-orders',

            // Customer Management - Admin only
            'manage-customers',
            'view-customers',
            'edit-customers',

            // Inventory Management - Admin/Staff
            'manage-inventory',
            'view-inventory',
            'update-inventory',

            // Reports & Analytics - Admin only
            'view-reports',
            'view-analytics',

            // System Settings - Admin only
            'manage-settings',
            'manage-system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create admin role with all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Create user role with basic permissions
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'view-dashboard',
            'edit-profile',
            'view-profile',
            'view-settings',
            'change-password',
            'create-orders',
            'view-orders',
        ]);

        // Clear permission cache after seeding
        app()['cache']->forget(config('permission.cache.key'));

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Admin role permissions: ' . $admin->permissions->count());
        $this->command->info('User role permissions: ' . $user->permissions->count());
    }
}