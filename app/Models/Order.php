<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'billing_address',
        'shipping_address',
        'payment_status',
        'payment_method',
        'payment_reference',
        'aba_transaction_id',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'tax_amount' => 'integer',
            'shipping_amount' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include orders with a specific status.
     */
    public function scopeStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing orders.
     */
    public function scopeProcessing(Builder $query): void
    {
        $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include shipped orders.
     */
    public function scopeShipped(Builder $query): void
    {
        $query->where('status', 'shipped');
    }

    /**
     * Scope a query to only include delivered orders.
     */
    public function scopeDelivered(Builder $query): void
    {
        $query->where('status', 'delivered');
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', 'cancelled');
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal / 100, 2);
    }

    /**
     * Get the formatted tax amount.
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        return '$' . number_format($this->tax_amount / 100, 2);
    }

    /**
     * Get the formatted shipping amount.
     */
    public function getFormattedShippingAmountAttribute(): string
    {
        return '$' . number_format($this->shipping_amount / 100, 2);
    }

    /**
     * Get the formatted discount amount.
     */
    public function getFormattedDiscountAmountAttribute(): string
    {
        return '$' . number_format($this->discount_amount / 100, 2);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount / 100, 2);
    }

    /**
     * Get the total quantity of items in the order.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if the order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if the order is pending payment.
     */
    public function isPendingPayment(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -8));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Mark the order as shipped.
     */
    public function markAsShipped(): bool
    {
        return $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark the order as delivered.
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark the order as cancelled.
     */
    public function markAsCancelled(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(string $status, ?string $reference = null, ?string $abaTransactionId = null): bool
    {
        $data = ['payment_status' => $status];
        
        if ($reference) {
            $data['payment_reference'] = $reference;
        }
        
        if ($abaTransactionId) {
            $data['aba_transaction_id'] = $abaTransactionId;
        }

        return $this->update($data);
    }
}
