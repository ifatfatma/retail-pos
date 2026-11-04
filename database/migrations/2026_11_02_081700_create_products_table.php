<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name', 200);
            $table->string('slug', 220)->unique();

            $table->text('description')->nullable();

            $table->enum('product_type', [
                'simple',
                'variable'
            ])->default('simple');

            $table->boolean('track_inventory')->default(true);

            $table->decimal('low_stock_threshold', 12, 3)
                ->default(0);

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

