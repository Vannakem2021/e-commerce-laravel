<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_includes_categories_in_shared_inertia_data()
    {
        // Create test categories
        $parentCategory = Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => 1,
        ]);

        $childCategory = Category::factory()->create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'is_active' => true,
            'parent_id' => $parentCategory->id,
            'sort_order' => 1,
        ]);

        // Make a request to products page that uses Inertia
        $response = $this->get('/products');

        // Debug: Check what shared data is actually available
        $response->assertInertia(fn ($page) =>
            $page->has('shared')
        );

        // Check that the response includes shared category data
        $response->assertInertia(fn ($page) =>
            $page->has('shared.categories')
                 ->where('shared.categories.0.name', 'Electronics')
                 ->where('shared.categories.0.children.0.name', 'Smartphones')
        );
    }

    /** @test */
    public function it_includes_featured_categories_in_shared_inertia_data()
    {
        // Create test categories
        $parentCategory = Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'parent_id' => null,
        ]);

        $featuredCategory = Category::factory()->create([
            'name' => 'Featured Smartphones',
            'slug' => 'featured-smartphones',
            'is_active' => true,
            'is_featured' => true,
            'parent_id' => $parentCategory->id,
            'sort_order' => 1,
        ]);

        // Make a request to products page that uses Inertia
        $response = $this->get('/products');

        // Check that the response includes shared featured category data
        $response->assertInertia(fn ($page) =>
            $page->has('shared.featured_categories')
                 ->where('shared.featured_categories.0.name', 'Featured Smartphones')
                 ->where('shared.featured_categories.0.href', '/products?category=featured-smartphones')
        );
    }

    /** @test */
    public function deprecated_api_routes_still_work_with_warnings()
    {
        // Create test categories
        Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'parent_id' => null,
        ]);

        // Test deprecated categories API
        $response = $this->get('/api/categories');

        $response->assertStatus(200)
                 ->assertJson([
                     'deprecated' => true,
                     'message' => 'This endpoint is deprecated. Use shared Inertia data instead.',
                 ])
                 ->assertJsonStructure([
                     'categories',
                     'deprecated',
                     'message',
                     'migration_guide',
                 ]);

        // Test deprecated featured categories API
        $response = $this->get('/api/categories/featured');

        $response->assertStatus(200)
                 ->assertJson([
                     'deprecated' => true,
                     'message' => 'This endpoint is deprecated. Use shared Inertia data instead.',
                 ]);
    }

    /** @test */
    public function shared_categories_are_cached()
    {
        // Create test category
        Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'parent_id' => null,
        ]);

        // First request should cache the data
        $this->get('/products');

        // Verify cache exists
        $this->assertTrue(Cache::has('shared_categories'));
        $this->assertTrue(Cache::has('featured_categories'));

        // Get cached data
        $cachedCategories = Cache::get('shared_categories');
        $this->assertIsArray($cachedCategories);
        $this->assertCount(1, $cachedCategories);
        $this->assertEquals('Electronics', $cachedCategories[0]['name']);
    }

    /** @test */
    public function only_active_categories_are_included_in_shared_data()
    {
        // Create active and inactive categories
        Category::factory()->create([
            'name' => 'Active Category',
            'slug' => 'active-category',
            'is_active' => true,
            'parent_id' => null,
        ]);

        Category::factory()->create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'is_active' => false,
            'parent_id' => null,
        ]);

        // Make request
        $response = $this->get('/products');

        // Check that only active categories are included
        $response->assertInertia(fn ($page) =>
            $page->has('shared.categories')
                 ->where('shared.categories.0.name', 'Active Category')
                 ->missing('shared.categories.1') // Should not have inactive category
        );
    }

    /** @test */
    public function categories_include_product_counts()
    {
        // Create category with products
        $category = Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'parent_id' => null,
        ]);

        // Create products for the category
        \App\Models\Product::factory()->count(3)->create([
            'status' => 'published',
        ])->each(function ($product) use ($category) {
            $product->categories()->attach($category->id);
        });

        // Make request
        $response = $this->get('/products');

        // Check that product count is included
        $response->assertInertia(fn ($page) =>
            $page->has('shared.categories')
                 ->where('shared.categories.0.product_count', 3)
        );
    }
}
