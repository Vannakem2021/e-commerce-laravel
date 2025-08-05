<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ProductVariant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold',
        'weight',
        'length',
        'width',
        'height',
        'is_active',
        'sort_order',
        'image',
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
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'product_id' => 'integer',
        ];
    }

    /**
     * Get the product that owns the variant.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active variants.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include in-stock variants.
     */
    public function scopeInStock(Builder $query): void
    {
        $query->where('stock_status', 'in_stock')
              ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to order variants by sort order.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the effective price (variant price or parent product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product->price;
    }

    /**
     * Get the effective compare price.
     */
    public function getEffectiveComparePriceAttribute(): ?float
    {
        return $this->compare_price ?? $this->product->compare_price;
    }

    /**
     * Get the formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->effective_price / 100, 2);
    }

    /**
     * Get the price in dollars (for display).
     */
    public function getPriceInDollarsAttribute(): ?float
    {
        return $this->price ? $this->price / 100 : null;
    }

    /**
     * Get the effective price in dollars (for display).
     */
    public function getEffectivePriceInDollarsAttribute(): float
    {
        return $this->effective_price / 100;
    }

    /**
     * Check if the variant is low in stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Get the variant display name.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return "Variant #{$this->id}";
    }

    /**
     * Get the image URL for the variant.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Fallback to product's primary image
        return $this->product->primaryImage?->url ?? asset('images/placeholder.jpg');
    }
}
