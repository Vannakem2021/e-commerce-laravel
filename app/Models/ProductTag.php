<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    /**
     * Get the products for the tag.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag_pivot');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the tag with color styling.
     */
    public function getStyledNameAttribute(): string
    {
        $color = $this->color ?: '#6B7280';
        return "<span style='color: {$color}'>{$this->name}</span>";
    }
}
