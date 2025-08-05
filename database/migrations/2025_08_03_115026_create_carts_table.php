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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index(); // For guest users
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For authenticated users
            $table->enum('status', ['active', 'abandoned', 'converted'])->default('active')->index();
            $table->timestamp('expires_at')->nullable()->index(); // Cart expiration
            $table->json('metadata')->nullable(); // Additional cart data
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
