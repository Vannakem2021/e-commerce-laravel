<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Dashboard & Profile
            'view-dashboard',
            'edit-profile',
            'view-settings',

            // User Management
            'manage-users',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Product Management
            'manage-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Category Management
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

            // Customer Management
            'manage-customers',
            'view-customers',
            'edit-customers',

            // Inventory Management
            'manage-inventory',
            'view-inventory',
            'update-inventory',

            // Reports & Analytics
            'view-reports',
            'view-analytics',

            // System Settings
            'manage-settings',
            'manage-system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo([
            'view-dashboard',
            'edit-profile',
            'view-settings',
            'create-orders',
            'view-orders',
        ]);
    }
}