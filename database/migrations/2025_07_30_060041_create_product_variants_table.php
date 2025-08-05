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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku', 100)->unique()->index();
            $table->string('name')->nullable(); // Variant name (e.g., "Red - Large")

            // Pricing (stored in cents for precision)
            $table->integer('price')->nullable(); // Override parent price in cents
            $table->integer('compare_price')->nullable(); // Compare price in cents
            $table->integer('cost_price')->nullable(); // Cost price in cents

            // Inventory
            $table->integer('stock_quantity')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'back_order'])->default('in_stock');
            $table->integer('low_stock_threshold')->default(5);

            // Physical Properties
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);

            // Additional
            $table->string('image')->nullable(); // Variant-specific image
            $table->json('attribute_values')->nullable(); // Store attribute combination

            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'is_active']);
            $table->index(['stock_status', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
