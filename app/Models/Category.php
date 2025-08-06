<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
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
        'meta_title',
        'meta_description',
        'image',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'is_featured',
        'seo_data',
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
            'parent_id' => 'integer',
            'seo_data' => 'array',
        ];
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.) with depth limit.
     */
    public function descendants(int $maxDepth = 3)
    {
        if ($maxDepth <= 0) {
            return $this->children();
        }

        return $this->children()->with(['descendants' => function ($query) use ($maxDepth) {
            // Recursively load descendants with decreasing depth
            if ($maxDepth > 1) {
                $query->with(['descendants' => function ($q) use ($maxDepth) {
                    // Continue recursion with reduced depth
                    return $q->descendants($maxDepth - 1);
                }]);
            }
        }]);
    }

    /**
     * Get all descendants as a flat collection with depth limit.
     */
    public function getAllDescendants(int $maxDepth = 3): \Illuminate\Support\Collection
    {
        $descendants = collect();
        $this->collectDescendants($descendants, $maxDepth, 0);
        return $descendants;
    }

    /**
     * Recursively collect descendants into a flat collection.
     */
    private function collectDescendants(\Illuminate\Support\Collection $collection, int $maxDepth, int $currentDepth): void
    {
        if ($currentDepth >= $maxDepth) {
            return;
        }

        $children = $this->children()->get();
        foreach ($children as $child) {
            $collection->push($child);
            $child->collectDescendants($collection, $maxDepth, $currentDepth + 1);
        }
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Get the primary products for the category.
     */
    public function primaryProducts()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
                    ->wherePivot('is_primary', true)
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured categories.
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include root categories (no parent).
     */
    public function scopeRoot(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order categories by sort order.
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
     * Check if this category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the full path of the category (parent > child > grandchild).
     */
    public function getFullPathAttribute(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }


}
