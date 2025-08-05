<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
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
        'sku',
        'short_description',
        'description',
        'features',
        'specifications',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold',
        'track_inventory',
        'product_type',
        'is_digital',
        'is_virtual',
        'requires_shipping',
        'status',
        'is_featured',
        'is_on_sale',
        'published_at',
        'meta_title',
        'meta_description',
        'seo_data',
        'brand_id',
        'user_id',
        'weight',
        'length',
        'width',
        'height',
        'sort_order',
        'additional_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer', // Stored in cents
            'compare_price' => 'integer', // Stored in cents
            'cost_price' => 'integer', // Stored in cents
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'track_inventory' => 'boolean',
            'is_digital' => 'boolean',
            'is_virtual' => 'boolean',
            'requires_shipping' => 'boolean',
            'is_featured' => 'boolean',
            'is_on_sale' => 'boolean',
            'published_at' => 'datetime',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'sort_order' => 'integer',
            'specifications' => 'array',
            'seo_data' => 'array',
            'additional_data' => 'array',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        // Removed attribute_values_with_price_modifiers
    ];

    /**
     * Get the brand that owns the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the user that created the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Get the primary category for the product.
     */
    public function primaryCategory()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->wherePivot('is_primary', true)
                    ->first();
    }

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image for the product.
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the variants for the product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the active variants for the product.
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }



    /**
     * Get the tags for the product.
     */
    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag_pivot');
    }

    // Removed attribute-related relationships

    /**
     * Scope a query to only include published products.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include products on sale.
     */
    public function scopeOnSale(Builder $query): void
    {
        $query->where('is_on_sale', true);
    }

    /**
     * Scope a query to only include in-stock products.
     */
    public function scopeInStock(Builder $query): void
    {
        $query->where('stock_status', 'in_stock')
              ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): void
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price / 100, 2);
    }

    /**
     * Get the formatted compare price attribute.
     */
    public function getFormattedComparePriceAttribute(): string
    {
        return $this->compare_price ? '$' . number_format($this->compare_price / 100, 2) : null;
    }

    /**
     * Get the price in dollars (for display).
     */
    public function getPriceInDollarsAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Get the compare price in dollars (for display).
     */
    public function getComparePriceInDollarsAttribute(): ?float
    {
        return $this->compare_price ? $this->compare_price / 100 : null;
    }

    /**
     * Get the cost price in dollars (for display).
     */
    public function getCostPriceInDollarsAttribute(): ?float
    {
        return $this->cost_price ? $this->cost_price / 100 : null;
    }

    /**
     * Get the discount percentage if on sale.
     */
    public function getDiscountPercentageAttribute(): int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return 0;
    }

    // Removed getAttributeValuesWithPriceModifiersAttribute method

    /**
     * Check if the product is low in stock.
     */
    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->stock_quantity <= $this->low_stock_threshold;
    }


}
