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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('slug', 280)->unique()->index();
            $table->string('sku', 100)->unique()->index();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('features')->nullable(); // JSON array of features
            $table->json('specifications')->nullable(); // Product specifications

            // Pricing (stored in cents for precision)
            $table->integer('price')->index(); // Price in cents
            $table->integer('compare_price')->nullable(); // Original price in cents for sales
            $table->integer('cost_price')->nullable(); // Cost in cents for profit calculation

            // Inventory
            $table->integer('stock_quantity')->default(0)->index();
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'back_order'])->default('in_stock')->index();
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('track_inventory')->default(true);

            // Product Types
            $table->enum('product_type', ['simple', 'variable', 'digital', 'service'])->default('simple')->index();
            $table->boolean('is_digital')->default(false)->index();
            $table->boolean('is_virtual')->default(false)->index();
            $table->boolean('requires_shipping')->default(true);

            // Status & Visibility
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_on_sale')->default(false)->index();
            $table->datetime('published_at')->nullable()->index();

            // SEO
            $table->string('meta_title', 160)->nullable();
            $table->text('meta_description')->nullable();
            $table->json('seo_data')->nullable();

            // Relationships
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator

            // Physical Properties
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();

            // Additional
            $table->integer('sort_order')->default(0);
            $table->json('additional_data')->nullable(); // Flexible additional fields

            $table->timestamps();
            $table->softDeletes();

            // Composite Indexes
            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['product_type', 'status']);
            $table->index(['stock_status', 'track_inventory']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
