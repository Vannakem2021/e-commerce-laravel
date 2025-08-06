<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'contact_name',
        'phone_number',
        'house_number',
        'street_number',
        'city_province',
        'district_khan',
        'commune_sangkat',
        'postal_code',
        'additional_info',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include default addresses.
     */
    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }

    /**
     * Scope a query to filter by address type.
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Get the formatted address string.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_number,
            $this->street_number,
            $this->commune_sangkat,
            $this->district_khan,
            $this->city_province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Set a new default address and unset others.
     */
    public function setAsDefault(): void
    {
        // Unset other default addresses for this user and type
        static::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this address as default
        $this->update(['is_default' => true]);
    }
}
