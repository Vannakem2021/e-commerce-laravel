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
        Schema::table('brands', function (Blueprint $table) {
            // Add composite indexes for common query patterns
            $table->index(['is_active', 'is_featured', 'sort_order'], 'brands_active_featured_sort_idx');
            $table->index(['is_active', 'sort_order', 'name'], 'brands_active_sort_name_idx');
            $table->index(['slug', 'is_active'], 'brands_slug_active_idx');
            
            // Add index for sort_order if not exists
            if (!Schema::hasIndex('brands', 'brands_sort_order_index')) {
                $table->index('sort_order');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            // Ensure we have optimal indexes for brand-product queries
            $table->index(['brand_id', 'status', 'is_featured'], 'products_brand_status_featured_idx');
            $table->index(['brand_id', 'status', 'created_at'], 'products_brand_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex('brands_active_featured_sort_idx');
            $table->dropIndex('brands_active_sort_name_idx');
            $table->dropIndex('brands_slug_active_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_brand_status_featured_idx');
            $table->dropIndex('products_brand_status_created_idx');
        });
    }
};
