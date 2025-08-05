<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user as the creator
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Get some brands and categories
        $brands = Brand::take(3)->get();
        $categories = Category::take(5)->get();

        if ($brands->isEmpty() || $categories->isEmpty()) {
            $this->command->error('No brands or categories found. Please run BrandSeeder and CategorySeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Premium Wireless Headphones',
                'short_description' => 'High-quality wireless headphones with noise cancellation',
                'description' => 'Experience premium sound quality with our latest wireless headphones featuring active noise cancellation, 30-hour battery life, and premium comfort padding.',
                'price' => 29999, // $299.99
                'compare_price' => 39999, // $399.99
                'stock_quantity' => 50,
                'is_featured' => true,
                'is_on_sale' => true,
            ],
            [
                'name' => 'Smart Fitness Watch',
                'short_description' => 'Advanced fitness tracking with heart rate monitoring',
                'description' => 'Track your fitness goals with precision using our smart fitness watch. Features include heart rate monitoring, GPS tracking, sleep analysis, and 7-day battery life.',
                'price' => 19999, // $199.99
                'stock_quantity' => 75,
                'is_featured' => true,
            ],
            [
                'name' => 'Portable Bluetooth Speaker',
                'short_description' => 'Waterproof speaker with 360-degree sound',
                'description' => 'Take your music anywhere with this portable Bluetooth speaker. IPX7 waterproof rating, 12-hour battery life, and immersive 360-degree sound.',
                'price' => 7999, // $79.99
                'compare_price' => 9999, // $99.99
                'stock_quantity' => 100,
                'is_on_sale' => true,
            ],
            [
                'name' => 'Wireless Charging Pad',
                'short_description' => 'Fast wireless charging for all Qi-enabled devices',
                'description' => 'Charge your devices wirelessly with our sleek charging pad. Compatible with all Qi-enabled devices, featuring fast charging technology and LED indicators.',
                'price' => 3999, // $39.99
                'stock_quantity' => 200,
            ],
            [
                'name' => 'USB-C Hub',
                'short_description' => '7-in-1 USB-C hub with 4K HDMI output',
                'description' => 'Expand your laptop connectivity with our 7-in-1 USB-C hub. Features 4K HDMI output, USB 3.0 ports, SD card reader, and 100W power delivery.',
                'price' => 5999, // $59.99
                'stock_quantity' => 150,
            ],
            [
                'name' => 'Gaming Mechanical Keyboard',
                'short_description' => 'RGB backlit mechanical keyboard for gaming',
                'description' => 'Dominate your games with our mechanical gaming keyboard. Features tactile switches, customizable RGB backlighting, and programmable macro keys.',
                'price' => 12999, // $129.99
                'stock_quantity' => 30,
                'is_featured' => true,
            ],
            [
                'name' => 'Wireless Mouse',
                'short_description' => 'Ergonomic wireless mouse with precision tracking',
                'description' => 'Work and play comfortably with our ergonomic wireless mouse. Features precision optical tracking, 18-month battery life, and comfortable grip design.',
                'price' => 2999, // $29.99
                'stock_quantity' => 80,
            ],
            [
                'name' => 'Laptop Stand',
                'short_description' => 'Adjustable aluminum laptop stand',
                'description' => 'Improve your workspace ergonomics with our adjustable laptop stand. Made from premium aluminum with multiple height and angle adjustments.',
                'price' => 4999, // $49.99
                'stock_quantity' => 60,
            ],
            [
                'name' => 'Phone Case',
                'short_description' => 'Protective case with wireless charging support',
                'description' => 'Protect your phone with style using our premium protective case. Features wireless charging compatibility, drop protection, and precise cutouts.',
                'price' => 1999, // $19.99
                'stock_quantity' => 500,
            ],
            [
                'name' => 'Tablet Screen Protector',
                'short_description' => 'Tempered glass screen protector',
                'description' => 'Keep your tablet screen pristine with our tempered glass screen protector. Features 9H hardness, anti-fingerprint coating, and bubble-free installation.',
                'price' => 1499, // $14.99
                'stock_quantity' => 300,
            ],
        ];

        foreach ($products as $index => $productData) {
            // Generate SKU and slug
            $productData['sku'] = 'PROD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $productData['slug'] = Str::slug($productData['name']);
            
            // Set default values
            $productData = array_merge([
                'stock_status' => 'in_stock',
                'track_inventory' => true,
                'product_type' => 'simple',
                'is_digital' => false,
                'is_virtual' => false,
                'requires_shipping' => true,
                'status' => 'published',
                'is_featured' => false,
                'is_on_sale' => false,
                'published_at' => now(),
                'sort_order' => $index,
                'user_id' => $user->id,
                'brand_id' => $brands->random()->id,
            ], $productData);

            // Create the product
            $product = Product::create($productData);

            // Attach random categories (1-3 categories per product)
            $randomCategories = $categories->random(rand(1, 3));
            $product->categories()->attach($randomCategories->pluck('id'));

            // Create a placeholder image
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'https://via.placeholder.com/400x400/6366f1/ffffff?text=' . urlencode($product->name),
                'alt_text' => $product->name,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            $this->command->info("Created product: {$product->name}");
        }

        $this->command->info('Product seeding completed!');
    }
}
