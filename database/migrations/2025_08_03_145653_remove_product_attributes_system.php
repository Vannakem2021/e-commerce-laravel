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
        // Remove product attribute values table (contains price modifiers)
        Schema::dropIfExists('product_attribute_values');

        // Remove product attributes table (attribute definitions)
        Schema::dropIfExists('product_attributes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate product_attributes table
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->index();
            $table->string('slug', 120)->unique()->index();
            $table->enum('type', ['text', 'number', 'select', 'multiselect', 'boolean', 'color'])->index();
            $table->json('options')->nullable(); // For select/multiselect types
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variation')->default(false); // Can create product variants
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['is_variation', 'is_visible']);
        });

        // Recreate product_attribute_values table
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_attribute_id')->constrained()->onDelete('cascade');
            $table->text('value'); // Flexible value storage
            $table->integer('price_modifier')->default(0)->comment('Price adjustment in cents');
            $table->timestamps();

            // Indexes
            $table->index(['product_attribute_id', 'value']);
        });
    }
};
