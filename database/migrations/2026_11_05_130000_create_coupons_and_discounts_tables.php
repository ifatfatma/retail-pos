<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Coupons master table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_cart', 'free_shipping'])->default('percentage');
            $table->decimal('value', 12, 2)->default(0);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->decimal('min_order_amount', 12, 2)->default(0);

            // Channel & Scope Targeting
            $table->enum('channel', ['all', 'online_only', 'pos_only'])->default('all');
            $table->enum('applies_to', ['all', 'specific_categories', 'specific_products'])->default('all');

            // Limits & Eligibility
            $table->unsignedInteger('usage_limit_total')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->default(1);
            $table->unsignedInteger('times_used')->default(0);
            $table->boolean('is_first_order_only')->default(false);

            // Validity
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active', 'start_date', 'end_date'], 'idx_coupon_lookup');
        });

        // 2. Specific Category / Product Restrictions Pivot
        Schema::create('coupon_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->string('restrictable_type', 100); // 'category' or 'product'
            $table->unsignedBigInteger('restrictable_id');
            $table->timestamps();

            $table->index(['coupon_id', 'restrictable_type', 'restrictable_id'], 'idx_coupon_restrict');
        });

        // 3. Coupon Usage Audit Ledger
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->restrictOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_phone', 30)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['coupon_id', 'customer_phone'], 'idx_coupon_phone');
            $table->index(['coupon_id', 'customer_id'], 'idx_coupon_cust');
        });

        // 4. Update sales table with coupon foreign key & code
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('discount_amount')->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 50)->nullable()->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code']);
        });

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_restrictions');
        Schema::dropIfExists('coupons');
    }
};

