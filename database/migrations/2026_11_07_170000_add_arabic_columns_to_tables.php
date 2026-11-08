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
        // 1. categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('categories', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 2. brands
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('brands', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 3. units
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'name_ar')) {
                $table->string('name_ar', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('units', 'short_name_ar')) {
                $table->string('short_name_ar', 50)->nullable()->after('short_name');
            }
        });

        // 4. products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'name_ar')) {
                $table->string('name_ar', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 5. coupons
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('coupons', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 6. suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'company_name_ar')) {
                $table->string('company_name_ar', 150)->nullable()->after('company_name');
            }
        });

        // 7. marketing_campaigns
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_campaigns', 'title_ar')) {
                $table->string('title_ar', 200)->nullable()->after('title');
            }
            if (!Schema::hasColumn('marketing_campaigns', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 8. marketing_segments
        Schema::table('marketing_segments', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_segments', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('marketing_segments', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });

        // 9. notification_templates
        Schema::table('notification_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_templates', 'subject_ar')) {
                $table->string('subject_ar', 200)->nullable()->after('subject');
            }
            if (!Schema::hasColumn('notification_templates', 'title_ar')) {
                $table->string('title_ar', 200)->nullable()->after('title');
            }
            if (!Schema::hasColumn('notification_templates', 'body_ar')) {
                $table->text('body_ar')->nullable()->after('body');
            }
        });

        // 10. automated_rules
        Schema::table('automated_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('automated_rules', 'name_ar')) {
                $table->string('name_ar', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('automated_rules', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automated_rules', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn(['subject_ar', 'title_ar', 'body_ar']);
        });

        Schema::table('marketing_segments', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'description_ar']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'company_name_ar']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'short_name_ar']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });
    }
};
