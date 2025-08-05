<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'meta_title' => $name . ' Products',
            'meta_description' => $this->faker->sentence(),
            'is_active' => true,
            'is_featured' => $this->faker->boolean(30),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
