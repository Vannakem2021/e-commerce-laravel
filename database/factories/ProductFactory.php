<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = \Illuminate\Support\Str::slug($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(3, true),
            'features' => $this->faker->paragraphs(2, true),
            'specifications' => $this->faker->paragraphs(2, true),
            'price' => $this->faker->numberBetween(1000, 50000), // Price in cents
            'compare_price' => null,
            'cost_price' => null,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'stock_status' => $this->faker->randomElement(['in_stock', 'out_of_stock', 'back_order']),
            'low_stock_threshold' => 5,
            'track_inventory' => true,
            'product_type' => 'simple',
            'is_digital' => false,
            'is_virtual' => false,
            'requires_shipping' => true,
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'is_on_sale' => false,
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'meta_title' => $this->faker->optional()->sentence(),
            'meta_description' => $this->faker->optional()->sentence(),
            'seo_data' => null,
            'brand_id' => null,
            'user_id' => User::factory(),
            'weight' => $this->faker->optional()->randomFloat(2, 0.1, 10),
            'length' => $this->faker->optional()->randomFloat(2, 1, 100),
            'width' => $this->faker->optional()->randomFloat(2, 1, 100),
            'height' => $this->faker->optional()->randomFloat(2, 1, 100),
            'sort_order' => $this->faker->numberBetween(0, 1000),
            'additional_data' => null,
        ];
    }

    /**
     * Indicate that the product is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the product is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product is on sale.
     */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_on_sale' => true,
            'compare_price' => $attributes['price'] + $this->faker->numberBetween(500, 2000),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }

    /**
     * Indicate that the product is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'stock_status' => 'in_stock',
        ]);
    }

    /**
     * Indicate that the product is digital.
     */
    public function digital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_digital' => true,
            'requires_shipping' => false,
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
        ]);
    }

    /**
     * Indicate that the product is virtual.
     */
    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_virtual' => true,
            'requires_shipping' => false,
            'track_inventory' => false,
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
        ]);
    }

    /**
     * Indicate that the product is variable (has variants).
     */
    public function variable(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'variable',
        ]);
    }
}
