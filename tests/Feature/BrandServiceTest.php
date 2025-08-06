<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Services\BrandService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BrandServiceTest extends TestCase
{
    use RefreshDatabase;

    private BrandService $brandService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brandService = app(BrandService::class);
    }

    public function test_can_get_active_brands_with_counts()
    {
        // Create test brands
        $activeBrand = Brand::factory()->create(['is_active' => true]);
        $inactiveBrand = Brand::factory()->create(['is_active' => false]);
        
        // Create products for active brand
        Product::factory()->count(3)->create([
            'brand_id' => $activeBrand->id,
            'status' => 'published'
        ]);

        $brands = $this->brandService->getActiveBrandsWithCounts();

        $this->assertCount(1, $brands);
        $this->assertEquals($activeBrand->id, $brands->first()->id);
        $this->assertEquals(3, $brands->first()->products_count);
    }

    public function test_can_get_featured_brands()
    {
        // Create test brands
        $featuredBrand = Brand::factory()->create(['is_active' => true, 'is_featured' => true]);
        $regularBrand = Brand::factory()->create(['is_active' => true, 'is_featured' => false]);

        $brands = $this->brandService->getFeaturedBrands();

        $this->assertCount(1, $brands);
        $this->assertEquals($featuredBrand->id, $brands->first()->id);
    }

    public function test_can_get_brands_for_filtering()
    {
        // Create brand with products
        $brandWithProducts = Brand::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'brand_id' => $brandWithProducts->id,
            'status' => 'published'
        ]);

        // Create brand without products
        $brandWithoutProducts = Brand::factory()->create(['is_active' => true]);

        $brands = $this->brandService->getBrandsForFiltering();

        $this->assertCount(1, $brands);
        $this->assertEquals($brandWithProducts->id, $brands->first()['id']);
    }

    public function test_caching_works_correctly()
    {
        // Clear cache first
        Cache::flush();

        // Create test data
        $brand = Brand::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'brand_id' => $brand->id,
            'status' => 'published'
        ]);

        // First call should hit database and cache result
        $brands1 = $this->brandService->getActiveBrandsWithCounts();
        
        // Second call should use cache
        $brands2 = $this->brandService->getActiveBrandsWithCounts();

        $this->assertEquals($brands1->toArray(), $brands2->toArray());
        
        // Verify cache exists
        $this->assertTrue(Cache::has('brands.active.with_counts'));
    }

    public function test_cache_is_cleared_when_brand_is_updated()
    {
        // Create test data and populate cache
        $brand = Brand::factory()->create(['is_active' => true]);
        $this->brandService->getActiveBrandsWithCounts();
        
        // Verify cache exists
        $this->assertTrue(Cache::has('brands.active.with_counts'));

        // Update brand through service
        $this->brandService->updateBrand($brand, ['name' => 'Updated Name']);

        // Verify cache is cleared
        $this->assertFalse(Cache::has('brands.active.with_counts'));
    }

    public function test_can_create_brand_and_clear_cache()
    {
        // Populate cache
        $this->brandService->getActiveBrandsWithCounts();
        $this->assertTrue(Cache::has('brands.active.with_counts'));

        // Create new brand
        $brandData = [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'is_active' => true,
        ];

        $brand = $this->brandService->createBrand($brandData);

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('Test Brand', $brand->name);
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has('brands.active.with_counts'));
    }

    public function test_can_delete_brand_and_clear_cache()
    {
        // Create brand and populate cache
        $brand = Brand::factory()->create();
        $this->brandService->getActiveBrandsWithCounts();
        $this->assertTrue(Cache::has('brands.active.with_counts'));

        // Delete brand
        $result = $this->brandService->deleteBrand($brand);

        $this->assertTrue($result);
        $this->assertTrue($brand->fresh()->trashed());
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has('brands.active.with_counts'));
    }

    public function test_can_get_brand_with_products()
    {
        // Create brand with products
        $brand = Brand::factory()->create(['is_active' => true]);
        $products = Product::factory()->count(5)->create([
            'brand_id' => $brand->id,
            'status' => 'published'
        ]);

        $brandWithProducts = $this->brandService->getBrandWithProducts($brand->slug, 3);

        $this->assertNotNull($brandWithProducts);
        $this->assertEquals($brand->id, $brandWithProducts->id);
        $this->assertCount(3, $brandWithProducts->products);
    }
}
