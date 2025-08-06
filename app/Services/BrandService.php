<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class BrandService
{
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get active brands with product counts (cached)
     */
    public function getActiveBrandsWithCounts()
    {
        return Cache::remember('brands.active.with_counts', self::CACHE_TTL, function () {
            return Brand::where('is_active', true)
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get featured brands (cached)
     */
    public function getFeaturedBrands()
    {
        return Cache::remember('brands.featured', self::CACHE_TTL, function () {
            return Brand::where('is_active', true)
                ->where('is_featured', true)
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get brands for product filtering (cached)
     */
    public function getBrandsForFiltering()
    {
        return Cache::remember('brands.for_filtering', self::CACHE_TTL, function () {
            // Use whereHas instead of withCount + having for SQLite compatibility
            $brands = Brand::where('is_active', true)
                ->whereHas('products', function ($query) {
                    $query->where('status', 'published');
                })
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            // Transform to array format for frontend
            return $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'product_count' => $brand->products_count,
                ];
            })->values(); // Use values() to get a regular Collection
        });
    }

    /**
     * Clear all brand-related caches
     */
    public function clearCache(): void
    {
        Cache::forget('brands.active.with_counts');
        Cache::forget('brands.featured');
        Cache::forget('brands.for_filtering');

        // Clear any brand-specific caches
        $brands = Brand::pluck('slug');
        foreach ($brands as $slug) {
            Cache::forget("brand.{$slug}.products");
            Cache::forget("brand.{$slug}.details");
        }
    }

    /**
     * Get brand with products (cached)
     */
    public function getBrandWithProducts(string $slug, int $limit = 12): ?Brand
    {
        $cacheKey = "brand.{$slug}.products.{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug, $limit) {
            return Brand::where('slug', $slug)
                ->where('is_active', true)
                ->with(['products' => function ($query) use ($limit) {
                    $query->where('status', 'published')
                          ->with(['primaryImage', 'categories'])
                          ->orderBy('sort_order')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit);
                }])
                ->first();
        });
    }

    /**
     * Update brand and clear related caches
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        $brand->update($data);
        $this->clearCache();

        return $brand->fresh();
    }

    /**
     * Create brand and clear related caches
     */
    public function createBrand(array $data): Brand
    {
        $brand = Brand::create($data);
        $this->clearCache();

        return $brand;
    }

    /**
     * Delete brand and clear related caches
     */
    public function deleteBrand(Brand $brand): bool
    {
        $result = $brand->delete();
        $this->clearCache();

        return $result;
    }
}
