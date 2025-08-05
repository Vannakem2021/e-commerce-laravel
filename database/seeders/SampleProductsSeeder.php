<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;

class SampleProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for product creation
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $this->command->error('Admin user not found. Please create an admin user first.');
            return;
        }

        // Get categories and brands
        $smartphones = Category::where('slug', 'smartphones')->first();
        $laptops = Category::where('slug', 'laptops')->first();
        $headphones = Category::where('slug', 'headphones')->first();
        $tablets = Category::where('slug', 'tablets')->first();
        $gamingConsoles = Category::where('slug', 'gaming-consoles')->first();

        $apple = Brand::where('slug', 'apple')->first();
        $samsung = Brand::where('slug', 'samsung')->first();
        $sony = Brand::where('slug', 'sony')->first();
        $dell = Brand::where('slug', 'dell')->first();
        $nintendo = Brand::where('slug', 'nintendo')->first();

        $sampleProducts = [
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'sku' => 'APPLE-IP15P-128',
                'short_description' => 'Latest iPhone with A17 Pro chip and titanium design.',
                'description' => 'The iPhone 15 Pro features the powerful A17 Pro chip, a stunning titanium design, and an advanced camera system. Experience incredible performance and photography capabilities.',
                'price' => 99900, // $999.00
                'stock_quantity' => 50,
                'brand_id' => $apple?->id,
                'category_ids' => [$smartphones?->id],
                'is_featured' => true,
                'status' => 'published',
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'sku' => 'SAMSUNG-GS24U-256',
                'short_description' => 'Premium Android smartphone with S Pen and AI features.',
                'description' => 'The Galaxy S24 Ultra combines cutting-edge AI technology with the precision of the S Pen. Capture stunning photos and boost productivity with Galaxy AI.',
                'price' => 119900, // $1199.00
                'stock_quantity' => 30,
                'brand_id' => $samsung?->id,
                'category_ids' => [$smartphones?->id],
                'is_featured' => true,
                'status' => 'published',
            ],
            [
                'name' => 'Dell XPS 13',
                'slug' => 'dell-xps-13',
                'sku' => 'DELL-XPS13-I7',
                'short_description' => 'Ultra-portable laptop with Intel Core i7 and stunning display.',
                'description' => 'The Dell XPS 13 delivers exceptional performance in an ultra-portable design. Features Intel Core i7 processor, 16GB RAM, and a beautiful InfinityEdge display.',
                'price' => 129900, // $1299.00
                'stock_quantity' => 25,
                'brand_id' => $dell?->id,
                'category_ids' => [$laptops?->id],
                'is_featured' => true,
                'status' => 'published',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'slug' => 'sony-wh-1000xm5',
                'sku' => 'SONY-WH1000XM5-BLK',
                'short_description' => 'Industry-leading noise canceling wireless headphones.',
                'description' => 'Experience exceptional sound quality and industry-leading noise cancellation with the Sony WH-1000XM5. Perfect for music, calls, and travel.',
                'price' => 39900, // $399.00
                'stock_quantity' => 75,
                'brand_id' => $sony?->id,
                'category_ids' => [$headphones?->id],
                'is_featured' => false,
                'status' => 'published',
            ],
            [
                'name' => 'iPad Pro 12.9"',
                'slug' => 'ipad-pro-12-9',
                'sku' => 'APPLE-IPADPRO-129-256',
                'short_description' => 'Powerful tablet with M2 chip and Liquid Retina XDR display.',
                'description' => 'The iPad Pro 12.9" with M2 chip delivers desktop-class performance in a portable design. Features stunning Liquid Retina XDR display and all-day battery life.',
                'price' => 109900, // $1099.00
                'stock_quantity' => 40,
                'brand_id' => $apple?->id,
                'category_ids' => [$tablets?->id],
                'is_featured' => true,
                'status' => 'published',
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'slug' => 'nintendo-switch-oled',
                'sku' => 'NINTENDO-SWITCH-OLED-WHT',
                'short_description' => 'Gaming console with vibrant OLED screen and enhanced audio.',
                'description' => 'The Nintendo Switch OLED model features a vibrant 7-inch OLED screen, enhanced audio, and a wide adjustable stand for tabletop gaming.',
                'price' => 34900, // $349.00
                'stock_quantity' => 60,
                'brand_id' => $nintendo?->id,
                'category_ids' => [$gamingConsoles?->id],
                'is_featured' => true,
                'status' => 'published',
            ],
        ];

        foreach ($sampleProducts as $productData) {
            $categoryIds = $productData['category_ids'];
            unset($productData['category_ids']);

            $product = Product::create([
                ...$productData,
                'user_id' => $adminUser->id,
                'product_type' => 'simple',
                'stock_status' => 'in_stock',
                'track_inventory' => true,
                'requires_shipping' => true,
                'is_digital' => false,
                'is_virtual' => false,
                'is_on_sale' => false,
                'sort_order' => 0,
                'meta_title' => $productData['name'],
                'meta_description' => $productData['short_description'],
            ]);

            // Attach categories
            if (!empty($categoryIds)) {
                $product->categories()->attach(array_filter($categoryIds));
            }
        }

        $this->command->info('Created ' . count($sampleProducts) . ' sample products!');
        $this->command->info('You can now view these products in the admin dashboard or on the frontend.');
    }
}
