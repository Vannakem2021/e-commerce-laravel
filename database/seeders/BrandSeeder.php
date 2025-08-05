<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Premium technology products including iPhones, iPads, MacBooks, and accessories.',
                'website' => 'https://www.apple.com',
                'meta_title' => 'Apple Products - Premium Technology',
                'meta_description' => 'Shop the latest Apple products including iPhones, iPads, MacBooks, and accessories with cutting-edge technology.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Innovative electronics including smartphones, tablets, TVs, and home appliances.',
                'website' => 'https://www.samsung.com',
                'meta_title' => 'Samsung Electronics - Innovation Technology',
                'meta_description' => 'Discover Samsung\'s latest smartphones, tablets, TVs, and home appliances with innovative technology.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Entertainment and technology products including cameras, headphones, gaming consoles, and audio equipment.',
                'website' => 'https://www.sony.com',
                'meta_title' => 'Sony Products - Entertainment Technology',
                'meta_description' => 'Explore Sony\'s range of cameras, headphones, gaming consoles, and audio equipment for entertainment.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Dell',
                'slug' => 'dell',
                'description' => 'Professional computers, laptops, servers, and IT solutions for business and personal use.',
                'website' => 'https://www.dell.com',
                'meta_title' => 'Dell Computers - Professional IT Solutions',
                'meta_description' => 'Shop Dell computers, laptops, servers, and IT solutions for business and personal computing needs.',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'HP',
                'slug' => 'hp',
                'description' => 'Computing and printing solutions including laptops, desktops, printers, and accessories.',
                'website' => 'https://www.hp.com',
                'meta_title' => 'HP Computing - Laptops, Desktops & Printers',
                'meta_description' => 'Find HP laptops, desktops, printers, and computing accessories for home and business use.',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Microsoft',
                'slug' => 'microsoft',
                'description' => 'Software and hardware products including Surface devices, Xbox gaming, and productivity software.',
                'website' => 'https://www.microsoft.com',
                'meta_title' => 'Microsoft Products - Software & Hardware',
                'meta_description' => 'Discover Microsoft Surface devices, Xbox gaming, and productivity software solutions.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                ...$brand,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
