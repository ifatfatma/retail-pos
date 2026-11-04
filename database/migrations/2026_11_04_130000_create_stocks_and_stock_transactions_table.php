<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 3)->default(0);

            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id']);
        });

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', ['in', 'out', 'adjustment']);

            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_after', 12, 3);

            $table->string('reason', 150)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stocks');
    }
};

