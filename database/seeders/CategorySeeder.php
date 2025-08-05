<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main categories
        $mainCategories = [
            [
                'id' => 1,
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest electronic devices and gadgets including smartphones, laptops, tablets, and accessories.',
                'meta_title' => 'Electronics - Latest Devices & Gadgets',
                'meta_description' => 'Shop the latest electronics including smartphones, laptops, tablets, and tech accessories.',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'id' => 2,
                'name' => 'Computers',
                'slug' => 'computers',
                'description' => 'Desktop computers, laptops, and computing accessories for work and gaming.',
                'meta_title' => 'Computers - Laptops, Desktops & Accessories',
                'meta_description' => 'Find the best computers, laptops, desktops, and computing accessories for work and gaming.',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'id' => 3,
                'name' => 'Mobile Devices',
                'slug' => 'mobile-devices',
                'description' => 'Smartphones, tablets, and mobile accessories from top brands.',
                'meta_title' => 'Mobile Devices - Smartphones & Tablets',
                'meta_description' => 'Discover the latest smartphones, tablets, and mobile accessories from leading brands.',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'id' => 4,
                'name' => 'Audio & Video',
                'slug' => 'audio-video',
                'description' => 'Headphones, speakers, cameras, and entertainment devices.',
                'meta_title' => 'Audio & Video - Headphones, Speakers & Cameras',
                'meta_description' => 'Shop audio and video equipment including headphones, speakers, cameras, and entertainment devices.',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        // Subcategories
        $subCategories = [
            // Electronics subcategories
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'description' => 'Latest smartphones from Apple, Samsung, and other leading brands.',
                'parent_id' => 3, // Mobile Devices
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Tablets',
                'slug' => 'tablets',
                'description' => 'iPads, Android tablets, and tablet accessories.',
                'parent_id' => 3, // Mobile Devices
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Computer subcategories
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'description' => 'Gaming laptops, business laptops, and ultrabooks.',
                'parent_id' => 2, // Computers
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Desktop Computers',
                'slug' => 'desktop-computers',
                'description' => 'Gaming PCs, workstations, and all-in-one computers.',
                'parent_id' => 2, // Computers
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
            // Audio & Video subcategories
            [
                'name' => 'Headphones',
                'slug' => 'headphones',
                'description' => 'Wireless headphones, gaming headsets, and earbuds.',
                'parent_id' => 4, // Audio & Video
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Speakers',
                'slug' => 'speakers',
                'description' => 'Bluetooth speakers, smart speakers, and sound systems.',
                'parent_id' => 4, // Audio & Video
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        // Insert main categories first
        foreach ($mainCategories as $category) {
            DB::table('categories')->insert([
                ...$category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert subcategories
        foreach ($subCategories as $category) {
            DB::table('categories')->insert([
                ...$category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
