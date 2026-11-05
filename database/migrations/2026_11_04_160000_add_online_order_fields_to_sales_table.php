<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->text('shipping_address')->nullable()->after('notes');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('discount_amount');
            $table->string('channel', 50)->default('web')->after('order_type');
            $table->string('delivery_partner', 100)->nullable()->after('shipping_address');
            $table->string('tracking_number', 100)->nullable()->after('delivery_partner');
            $table->date('estimated_delivery_date')->nullable()->after('tracking_number');
        });

        // Change status column to VARCHAR(50) so it supports online order statuses (pending, confirmed, processing, shipped, delivered, cancelled, etc.)
        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'completed'");
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_address',
                'shipping_cost',
                'channel',
                'delivery_partner',
                'tracking_number',
                'estimated_delivery_date',
            ]);
        });

        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `status` ENUM('completed', 'hold', 'cancelled') NOT NULL DEFAULT 'completed'");
    }
};

