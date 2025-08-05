<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some products to add variants to
        $products = Product::take(5)->get();

        foreach ($products as $product) {
            // Create generic variants for demonstration
            $this->createGenericVariants($product);
        }
    }

    private function createGenericVariants($product)
    {
        // Create 2-3 generic variants per product for demonstration
        $variantCount = rand(2, 3);
        $variantNames = ['Standard', 'Premium', 'Deluxe', 'Pro', 'Basic'];

        for ($i = 0; $i < $variantCount; $i++) {
            $variantName = $variantNames[$i] ?? "Variant " . ($i + 1);
            $priceModifier = $i * 1000; // Add $10 per variant level

            ProductVariant::create([
                'product_id' => $product->id,
                'name' => $product->name . ' - ' . $variantName,
                'sku' => $product->sku . '-V' . ($i + 1),
                'price' => $product->price + $priceModifier,
                'compare_price' => $product->compare_price ? $product->compare_price + $priceModifier : null,
                'stock_quantity' => rand(10, 50),
                'stock_status' => rand(10, 50) > 5 ? 'in_stock' : 'out_of_stock',
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
