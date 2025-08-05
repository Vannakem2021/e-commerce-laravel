<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'product_snapshot',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
            'product_snapshot' => 'array',
        ];
    }

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant that owns the order item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the formatted unit price.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price / 100, 2);
    }

    /**
     * Get the formatted total price.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price / 100, 2);
    }

    /**
     * Get the display name for the order item.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product_name;

        if ($this->variant && $this->variant->name) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }

    /**
     * Calculate the total price based on quantity and unit price.
     */
    public function calculateTotalPrice(): int
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Create order item from cart item.
     */
    public static function createFromCartItem(CartItem $cartItem, int $orderId): self
    {
        return self::create([
            'order_id' => $orderId,
            'product_id' => $cartItem->product_id,
            'product_variant_id' => $cartItem->product_variant_id,
            'product_name' => $cartItem->product->name,
            'product_sku' => $cartItem->product->sku,
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->price,
            'total_price' => $cartItem->price * $cartItem->quantity,
            'product_snapshot' => [
                'id' => $cartItem->product->id,
                'name' => $cartItem->product->name,
                'sku' => $cartItem->product->sku,
                'price' => $cartItem->product->price,
                'description' => $cartItem->product->description,
                'brand' => $cartItem->product->brand ? [
                    'id' => $cartItem->product->brand->id,
                    'name' => $cartItem->product->brand->name,
                ] : null,
                'variant' => $cartItem->variant ? [
                    'id' => $cartItem->variant->id,
                    'name' => $cartItem->variant->name,
                    'sku' => $cartItem->variant->sku,
                    'price' => $cartItem->variant->price,
                ] : null,
                'images' => $cartItem->product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'alt_text' => $image->alt_text,
                    ];
                })->toArray(),
                'created_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Get the product image URL from snapshot or current product.
     */
    public function getImageUrlAttribute(): ?string
    {
        // Try to get from product snapshot first
        if (isset($this->product_snapshot['images']) && !empty($this->product_snapshot['images'])) {
            return '/storage/' . $this->product_snapshot['images'][0]['image_path'];
        }

        // Fallback to current product image
        if ($this->product && $this->product->primaryImage) {
            return '/storage/' . $this->product->primaryImage->image_path;
        }

        return '/images/placeholder-product.jpg';
    }

    /**
     * Get the brand name from snapshot or current product.
     */
    public function getBrandNameAttribute(): ?string
    {
        // Try to get from product snapshot first
        if (isset($this->product_snapshot['brand']['name'])) {
            return $this->product_snapshot['brand']['name'];
        }

        // Fallback to current product brand
        if ($this->product && $this->product->brand) {
            return $this->product->brand->name;
        }

        return null;
    }
}
