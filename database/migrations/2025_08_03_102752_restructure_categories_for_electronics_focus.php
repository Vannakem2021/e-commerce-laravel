<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Remove the redundant "Electronics" root category (ID: 1) if it exists
        // This category has no subcategories and serves no purpose in an electronics-focused store
        DB::table('categories')->where('id', 1)->where('name', 'Electronics')->delete();

        // Step 2: Add new specific subcategories to make the hierarchy more useful
        // Only add subcategories if their parent categories exist
        $newSubcategories = [
            // iPhone subcategory under Smartphones (parent_id: 5)
            [
                'name' => 'iPhone',
                'slug' => 'iphone',
                'description' => 'Apple iPhone smartphones including latest models and accessories.',
                'parent_slug' => 'smartphones', // Use slug to find parent
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Samsung Galaxy subcategory under Smartphones (parent_id: 5)
            [
                'name' => 'Samsung Galaxy',
                'slug' => 'samsung-galaxy',
                'description' => 'Samsung Galaxy smartphones and accessories.',
                'parent_slug' => 'smartphones',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            // iPad subcategory under Tablets (parent_id: 6)
            [
                'name' => 'iPad',
                'slug' => 'ipad',
                'description' => 'Apple iPad tablets and accessories.',
                'parent_slug' => 'tablets',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Gaming Laptops subcategory under Laptops (parent_id: 7)
            [
                'name' => 'Gaming Laptops',
                'slug' => 'gaming-laptops',
                'description' => 'High-performance gaming laptops and accessories.',
                'parent_slug' => 'laptops',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Business Laptops subcategory under Laptops (parent_id: 7)
            [
                'name' => 'Business Laptops',
                'slug' => 'business-laptops',
                'description' => 'Professional laptops for business and productivity.',
                'parent_slug' => 'laptops',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
            // Wireless Headphones subcategory under Headphones (parent_id: 9)
            [
                'name' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'Bluetooth and wireless headphones.',
                'parent_slug' => 'headphones',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            // Gaming Headsets subcategory under Headphones (parent_id: 9)
            [
                'name' => 'Gaming Headsets',
                'slug' => 'gaming-headsets',
                'description' => 'Gaming headsets with microphones.',
                'parent_slug' => 'headphones',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        // Insert new subcategories only if parent exists
        foreach ($newSubcategories as $category) {
            // Find parent category by slug
            $parent = DB::table('categories')->where('slug', $category['parent_slug'])->first();

            if ($parent) {
                // Remove parent_slug and add parent_id
                unset($category['parent_slug']);
                $category['parent_id'] = $parent->id;
                $category['created_at'] = now();
                $category['updated_at'] = now();

                // Only insert if category doesn't already exist
                $exists = DB::table('categories')->where('slug', $category['slug'])->exists();
                if (!$exists) {
                    DB::table('categories')->insert($category);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the "Electronics" category
        DB::table('categories')->insert([
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Remove the subcategories we added
        $subcategorySlugs = [
            'iphone',
            'samsung-galaxy',
            'ipad',
            'gaming-laptops',
            'business-laptops',
            'wireless-headphones',
            'gaming-headsets',
        ];

        DB::table('categories')->whereIn('slug', $subcategorySlugs)->delete();
    }
};
