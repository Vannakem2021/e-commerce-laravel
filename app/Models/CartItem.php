<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
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
            'price' => 'integer',
            'product_snapshot' => 'array',
        ];
    }

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant that owns the cart item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the total price for this cart item (price * quantity).
     */
    public function getTotalPriceAttribute(): int
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price / 100, 2);
    }

    /**
     * Get the formatted total price.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_price / 100, 2);
    }

    /**
     * Get the display name for the cart item.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name ?? $this->product_snapshot['name'] ?? 'Unknown Product';

        if ($this->variant && $this->variant->name) {
            $name .= ' - ' . $this->variant->name;
        }

        return $name;
    }

    /**
     * Update the quantity of this cart item.
     */
    public function updateQuantity(int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->delete();
        }

        return $this->update(['quantity' => $quantity]);
    }

    /**
     * Increment the quantity by the given amount.
     */
    public function incrementQuantity(int $amount = 1): bool
    {
        return $this->updateQuantity($this->quantity + $amount);
    }

    /**
     * Decrement the quantity by the given amount.
     */
    public function decrementQuantity(int $amount = 1): bool
    {
        return $this->updateQuantity($this->quantity - $amount);
    }
}
