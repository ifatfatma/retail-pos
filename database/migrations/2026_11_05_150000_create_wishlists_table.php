<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('guest_token', 64)->nullable()->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->decimal('initial_price', 12, 2)->default(0);
            $table->boolean('notify_price_drop')->default(true);
            $table->boolean('notify_back_in_stock')->default(true);
            $table->timestamps();

            // Unique constraints to prevent duplicate wishlist items
            $table->unique(['customer_id', 'product_id', 'product_variant_id'], 'idx_wishlist_customer_unique');
            $table->unique(['guest_token', 'product_id', 'product_variant_id'], 'idx_wishlist_guest_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};

