<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class AdditionalBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $additionalBrands = [
            [
                'name' => 'Google',
                'slug' => 'google',
                'description' => 'Search, cloud services, and hardware products including Pixel phones, Nest devices, and Chromebooks.',
                'website' => 'https://www.google.com',
                'meta_title' => 'Google Products - Search, Cloud & Hardware',
                'meta_description' => 'Discover Google Pixel phones, Nest devices, Chromebooks, and cloud services.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Lenovo',
                'slug' => 'lenovo',
                'description' => 'ThinkPad laptops, desktop computers, and enterprise technology solutions.',
                'website' => 'https://www.lenovo.com',
                'meta_title' => 'Lenovo Computers - ThinkPad & Enterprise Solutions',
                'meta_description' => 'Shop Lenovo ThinkPad laptops, desktop computers, and enterprise technology solutions.',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'ASUS',
                'slug' => 'asus',
                'description' => 'Gaming laptops, motherboards, graphics cards, and computer hardware.',
                'website' => 'https://www.asus.com',
                'meta_title' => 'ASUS Gaming - Laptops, Graphics Cards & Hardware',
                'meta_description' => 'Explore ASUS gaming laptops, motherboards, graphics cards, and computer hardware.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Acer',
                'slug' => 'acer',
                'description' => 'Affordable laptops, desktops, monitors, and computing accessories.',
                'website' => 'https://www.acer.com',
                'meta_title' => 'Acer Computers - Affordable Laptops & Desktops',
                'meta_description' => 'Find affordable Acer laptops, desktops, monitors, and computing accessories.',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'TVs, monitors, smartphones, and home appliances with innovative technology.',
                'website' => 'https://www.lg.com',
                'meta_title' => 'LG Electronics - TVs, Monitors & Smartphones',
                'meta_description' => 'Discover LG TVs, monitors, smartphones, and home appliances with innovative technology.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Nintendo',
                'slug' => 'nintendo',
                'description' => 'Gaming consoles, handheld devices, and video games including Switch and accessories.',
                'website' => 'https://www.nintendo.com',
                'meta_title' => 'Nintendo Gaming - Switch Consoles & Games',
                'meta_description' => 'Shop Nintendo Switch consoles, handheld devices, and video games.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Xiaomi',
                'slug' => 'xiaomi',
                'description' => 'Affordable smartphones, smart home devices, and consumer electronics.',
                'website' => 'https://www.mi.com',
                'meta_title' => 'Xiaomi Products - Smartphones & Smart Devices',
                'meta_description' => 'Explore Xiaomi smartphones, smart home devices, and affordable consumer electronics.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'OnePlus',
                'slug' => 'oneplus',
                'description' => 'Premium smartphones with flagship features and clean Android experience.',
                'website' => 'https://www.oneplus.com',
                'meta_title' => 'OnePlus Smartphones - Premium Android Devices',
                'meta_description' => 'Discover OnePlus premium smartphones with flagship features and clean Android experience.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 14,
            ],
            [
                'name' => 'Huawei',
                'slug' => 'huawei',
                'description' => 'Smartphones, laptops, tablets, and networking equipment.',
                'website' => 'https://www.huawei.com',
                'meta_title' => 'Huawei Technology - Smartphones & Laptops',
                'meta_description' => 'Shop Huawei smartphones, laptops, tablets, and networking equipment.',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 15,
            ],
            [
                'name' => 'Razer',
                'slug' => 'razer',
                'description' => 'Gaming laptops, peripherals, and accessories for professional gamers.',
                'website' => 'https://www.razer.com',
                'meta_title' => 'Razer Gaming - Laptops & Gaming Peripherals',
                'meta_description' => 'Explore Razer gaming laptops, peripherals, and accessories for professional gamers.',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 16,
            ],
        ];

        foreach ($additionalBrands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']], // Check by slug to avoid duplicates
                $brand
            );
        }

        $this->command->info('Added ' . count($additionalBrands) . ' additional brands!');
    }
}
