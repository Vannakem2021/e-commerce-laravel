<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Product-related permissions
        $permissions = [
            // Product Management
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'publish-products',

            // Category Management
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Brand Management
            'view-brands',
            'create-brands',
            'edit-brands',
            'delete-brands',

            // Product Images
            'manage-product-images',

            // Product Variants
            'manage-product-variants',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all product permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign limited permissions to user role (if needed)
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $userRole->givePermissionTo([
                'view-products',
                'view-categories',
                'view-brands',
            ]);
        }
    }
}
