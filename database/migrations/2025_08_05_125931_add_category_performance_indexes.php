<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Add composite indexes for common query patterns
            $table->index(['is_active', 'is_featured', 'sort_order'], 'categories_active_featured_sort_idx');
            $table->index(['parent_id', 'is_active', 'sort_order'], 'categories_parent_active_sort_idx');
            $table->index(['slug', 'is_active'], 'categories_slug_active_idx');
            $table->index(['is_featured', 'is_active'], 'categories_featured_active_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            // Add indexes for category-product relationship queries
            $table->index(['category_id', 'is_primary'], 'product_categories_category_primary_idx');
            $table->index(['is_primary', 'category_id'], 'product_categories_primary_category_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop the composite indexes
            $table->dropIndex('categories_active_featured_sort_idx');
            $table->dropIndex('categories_parent_active_sort_idx');
            $table->dropIndex('categories_slug_active_idx');
            $table->dropIndex('categories_featured_active_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            // Drop the product-category indexes
            $table->dropIndex('product_categories_category_primary_idx');
            $table->dropIndex('product_categories_primary_category_idx');
        });
    }
};
