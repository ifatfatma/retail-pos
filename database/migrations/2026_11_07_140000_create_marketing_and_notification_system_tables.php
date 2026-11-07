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
        // Add marketing fields to customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'birthday')) {
                $table->date('birthday')->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('customers', 'loyalty_tier')) {
                $table->string('loyalty_tier', 50)->default('bronze')->after('birthday');
            }
            if (!Schema::hasColumn('customers', 'last_active_at')) {
                $table->dateTime('last_active_at')->nullable()->after('total_orders');
            }
        });

        // 1. Customer Segments
        Schema::create('marketing_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->json('criteria')->nullable();
            $table->unsignedInteger('customer_count')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('key', 100)->unique();
            $table->string('category', 50)->default('promotional');
            $table->string('subject', 200)->nullable();
            $table->string('title', 200);
            $table->text('body');
            $table->string('action_url', 255)->nullable();
            $table->string('action_text', 100)->nullable();
            $table->string('banner_url', 255)->nullable();
            $table->json('channels')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Marketing Campaigns
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('banner_url', 255)->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('campaign_type', 50)->default('promotional'); // promotional, flash_sale, festival, seasonal, clearance, retargeting
            $table->string('target_type', 50)->default('all'); // all, segment, custom
            $table->foreignId('segment_id')->nullable()->constrained('marketing_segments')->nullOnDelete();
            $table->json('channels')->nullable(); // ['in_app', 'email', 'sms', 'whatsapp', 'push']
            $table->string('discount_type', 30)->default('none'); // none, percentage, fixed
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->json('target_product_ids')->nullable();
            $table->json('target_category_ids')->nullable();
            $table->json('target_brand_ids')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status', 30)->default('draft'); // draft, scheduled, active, paused, completed, cancelled
            $table->unsignedInteger('total_targeted')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_delivered')->default(0);
            $table->unsignedInteger('total_opened')->default(0);
            $table->unsignedInteger('total_clicked')->default(0);
            $table->unsignedInteger('total_conversions')->default(0);
            $table->decimal('revenue_generated', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Automated Notification Rules
        Schema::create('automated_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('event_type', 80); // flash_sale_started, offer_expiring_soon, new_product_announcement, product_back_in_stock, customer_birthday, customer_inactivity, loyalty_reward_earned, special_customer_discount, seasonal_festival_offer
            $table->text('description')->nullable();
            $table->foreignId('segment_id')->nullable()->constrained('marketing_segments')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->json('channels')->nullable();
            $table->json('conditions')->nullable();
            $table->string('frequency', 30)->default('daily'); // immediate, daily, weekly
            $table->string('run_time', 10)->nullable()->default('09:00');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_run_at')->nullable();
            $table->unsignedInteger('total_runs')->default(0);
            $table->unsignedInteger('total_triggers')->default(0);
            $table->timestamps();
        });

        // 5. Notification Delivery Logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->foreignId('automated_rule_id')->nullable()->constrained('automated_rules')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('channel', 30); // in_app, email, sms, whatsapp, push
            $table->string('recipient', 150);
            $table->string('title', 200);
            $table->text('content');
            $table->string('action_url', 255)->nullable();
            $table->string('status', 30)->default('sent'); // pending, sent, delivered, opened, clicked, failed, simulated
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('clicked_at')->nullable();
            $table->boolean('is_test_mode')->default(false);
            $table->timestamps();
        });

        // 6. In-App Customer Notifications (Inbox)
        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->nullOnDelete();
            $table->string('title', 200);
            $table->text('message');
            $table->string('image_url', 255)->nullable();
            $table->string('discount_badge', 100)->nullable();
            $table->string('coupon_code', 50)->nullable();
            $table->string('action_url', 255)->nullable();
            $table->string('action_text', 100)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });

        // 7. Customer Notification Preferences
        Schema::create('customer_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->boolean('promotional_enabled')->default(true);
            $table->boolean('channel_in_app')->default(true);
            $table->boolean('channel_email')->default(true);
            $table->boolean('channel_sms')->default(true);
            $table->boolean('channel_whatsapp')->default(true);
            $table->boolean('channel_push')->default(true);
            $table->boolean('category_flash_sales')->default(true);
            $table->boolean('category_back_in_stock')->default(true);
            $table->boolean('category_rewards')->default(true);
            $table->boolean('category_new_arrivals')->default(true);
            $table->boolean('category_festivals')->default(true);
            $table->dateTime('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_notification_preferences');
        Schema::dropIfExists('customer_notifications');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('automated_rules');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('marketing_segments');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['birthday', 'loyalty_tier', 'last_active_at']);
        });
    }
};
