<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class AdditionalCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find parent categories
        $audioVideo = Category::where('slug', 'audio-video')->first();
        $computers = Category::where('slug', 'computers')->first();
        $mobileDevices = Category::where('slug', 'mobile-devices')->first();

        $additionalCategories = [
            // Gaming category (main category)
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'description' => 'Gaming consoles, accessories, and gaming peripherals for all platforms.',
                'meta_title' => 'Gaming - Consoles, Accessories & Peripherals',
                'meta_description' => 'Shop gaming consoles, accessories, and peripherals for PlayStation, Xbox, Nintendo, and PC gaming.',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Accessories category (main category)
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Cables, chargers, cases, and other electronic accessories.',
                'meta_title' => 'Electronics Accessories - Cables, Chargers & Cases',
                'meta_description' => 'Find cables, chargers, cases, and other electronic accessories for your devices.',
                'parent_id' => null,
                'sort_order' => 6,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        // Add subcategories that depend on existing parent categories
        if ($audioVideo) {
            $additionalCategories[] = [
                'name' => 'Cameras',
                'slug' => 'cameras',
                'description' => 'Digital cameras, action cameras, and photography equipment.',
                'meta_title' => 'Cameras - Digital & Action Cameras',
                'meta_description' => 'Shop digital cameras, action cameras, and photography equipment.',
                'parent_id' => $audioVideo->id,
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ];
            
            $additionalCategories[] = [
                'name' => 'Smart TVs',
                'slug' => 'smart-tvs',
                'description' => 'Smart TVs, streaming devices, and home entertainment systems.',
                'meta_title' => 'Smart TVs - Entertainment & Streaming Devices',
                'meta_description' => 'Discover smart TVs, streaming devices, and home entertainment systems.',
                'parent_id' => $audioVideo->id,
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => true,
            ];
        }

        if ($computers) {
            $additionalCategories[] = [
                'name' => 'Computer Accessories',
                'slug' => 'computer-accessories',
                'description' => 'Keyboards, mice, monitors, and other computer peripherals.',
                'meta_title' => 'Computer Accessories - Keyboards, Mice & Monitors',
                'meta_description' => 'Find keyboards, mice, monitors, and other computer peripherals.',
                'parent_id' => $computers->id,
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
            ];
        }

        if ($mobileDevices) {
            $additionalCategories[] = [
                'name' => 'Mobile Accessories',
                'slug' => 'mobile-accessories',
                'description' => 'Phone cases, screen protectors, chargers, and mobile accessories.',
                'meta_title' => 'Mobile Accessories - Cases, Chargers & Protection',
                'meta_description' => 'Shop phone cases, screen protectors, chargers, and mobile accessories.',
                'parent_id' => $mobileDevices->id,
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ];
            
            $additionalCategories[] = [
                'name' => 'Smartwatches',
                'slug' => 'smartwatches',
                'description' => 'Apple Watch, Samsung Galaxy Watch, and other smartwatches.',
                'meta_title' => 'Smartwatches - Apple Watch & Fitness Trackers',
                'meta_description' => 'Discover Apple Watch, Samsung Galaxy Watch, and fitness tracking smartwatches.',
                'parent_id' => $mobileDevices->id,
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => true,
            ];
        }

        foreach ($additionalCategories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']], // Check by slug to avoid duplicates
                $category
            );
        }

        // Now add gaming subcategories after creating the main Gaming category
        $gaming = Category::where('slug', 'gaming')->first();
        if ($gaming) {
            $gamingSubcategories = [
                [
                    'name' => 'Gaming Consoles',
                    'slug' => 'gaming-consoles',
                    'description' => 'PlayStation, Xbox, Nintendo Switch, and other gaming consoles.',
                    'meta_title' => 'Gaming Consoles - PlayStation, Xbox & Nintendo',
                    'meta_description' => 'Shop PlayStation, Xbox, Nintendo Switch, and other gaming consoles.',
                    'parent_id' => $gaming->id,
                    'sort_order' => 1,
                    'is_active' => true,
                    'is_featured' => true,
                ],
                [
                    'name' => 'Gaming Accessories',
                    'slug' => 'gaming-accessories',
                    'description' => 'Controllers, gaming keyboards, mice, and gaming peripherals.',
                    'meta_title' => 'Gaming Accessories - Controllers & Peripherals',
                    'meta_description' => 'Find controllers, gaming keyboards, mice, and gaming peripherals.',
                    'parent_id' => $gaming->id,
                    'sort_order' => 2,
                    'is_active' => true,
                    'is_featured' => false,
                ],
            ];

            foreach ($gamingSubcategories as $category) {
                Category::firstOrCreate(
                    ['slug' => $category['slug']],
                    $category
                );
            }
        }

        $this->command->info('Added additional categories successfully!');
    }
}
