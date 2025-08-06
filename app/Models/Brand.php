<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'meta_title',
        'meta_description',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the products for the brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to only include active brands.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured brands.
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Scope a query to order brands by sort order.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the brand's logo URL with fallback.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return str_starts_with($this->logo, 'http') ? $this->logo : "/storage/{$this->logo}";
        }

        // Fallback to generated avatar
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&size=200&background=f3f4f6&color=374151";
    }

    /**
     * Get published products count.
     */
    public function getPublishedProductsCountAttribute(): int
    {
        return $this->products()->where('status', 'published')->count();
    }

    /**
     * Scope to include only brands with published products.
     */
    public function scopeWithPublishedProducts(Builder $query): void
    {
        $query->whereHas('products', function ($q) {
            $q->where('status', 'published');
        });
    }

    /**
     * Get SEO-friendly URL for the brand.
     */
    public function getUrlAttribute(): string
    {
        return route('brands.show', $this->slug);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when brand is updated
        static::saved(function ($brand) {
            if (app()->bound(\App\Services\BrandService::class)) {
                app(\App\Services\BrandService::class)->clearCache();
            }
        });

        static::deleted(function ($brand) {
            if (app()->bound(\App\Services\BrandService::class)) {
                app(\App\Services\BrandService::class)->clearCache();
            }
        });
    }
}
