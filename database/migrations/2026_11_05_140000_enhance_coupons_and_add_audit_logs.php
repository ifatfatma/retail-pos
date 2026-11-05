<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('type', 50)->default('percentage')->change();
            $table->unsignedInteger('buy_qty')->nullable()->after('min_order_amount');
            $table->unsignedInteger('get_qty')->nullable()->after('buy_qty');
            $table->decimal('get_discount_percent', 5, 2)->default(100.00)->after('get_qty');
            $table->json('tier_rules')->nullable()->after('get_discount_percent');
        });

        Schema::create('coupon_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // created, updated, status_toggled, duplicated, deleted
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['coupon_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_audit_logs');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['buy_qty', 'get_qty', 'get_discount_percent', 'tier_rules']);
            $table->enum('type', ['percentage', 'fixed_cart', 'free_shipping'])->default('percentage')->change();
        });
    }
};

