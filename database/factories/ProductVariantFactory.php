<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}-[A-Z]{2}'),
            'name' => $this->faker->optional()->words(2, true),
            'price' => $this->faker->optional()->numberBetween(500, 10000), // Price in cents
            'compare_price' => null,
            'cost_price' => null,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'stock_status' => $this->faker->randomElement(['in_stock', 'out_of_stock', 'back_order']),
            'low_stock_threshold' => 5,
            'weight' => $this->faker->optional()->randomFloat(2, 0.1, 5),
            'length' => $this->faker->optional()->randomFloat(2, 1, 50),
            'width' => $this->faker->optional()->randomFloat(2, 1, 50),
            'height' => $this->faker->optional()->randomFloat(2, 1, 50),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 100),
            'image' => null,
        ];
    }



    /**
     * Create an active variant.
     */
    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive variant.
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an in-stock variant.
     */
    public function inStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'stock_status' => 'in_stock',
        ]);
    }

    /**
     * Create an out-of-stock variant.
     */
    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }

    /**
     * Create a variant on back order.
     */
    public function backOrder(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 0,
            'stock_status' => 'back_order',
        ]);
    }

    /**
     * Create a variant with a specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn () => [
            'price' => (int) ($price * 100), // Convert to cents
        ]);
    }

    /**
     * Create a variant with compare price (on sale).
     */
    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? $this->faker->numberBetween(1000, 5000);
            return [
                'price' => $price,
                'compare_price' => $price + $this->faker->numberBetween(500, 2000),
            ];
        });
    }

    /**
     * Create a variant with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => $this->faker->numberBetween(1, 4),
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
        ]);
    }
}
