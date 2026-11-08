<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Categories
DB::table('categories')->where('name', 'Stationery')->update([
    'name_ar' => 'أدوات مكتبية',
    'description_ar' => 'جميع اللوازم والأدوات المكتبية المتميزة'
]);
DB::table('categories')->where('name', 'Notebooks')->update([
    'name_ar' => 'دفاتر وملاحظات',
    'description_ar' => 'دفاتر ومفكرات عالية الجودة'
]);
DB::table('categories')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);

// Brands
DB::table('brands')->where('name', 'Classmate')->update([
    'name_ar' => 'كلاسميت',
    'description_ar' => 'علامة تجارية رائدة للدفاتر والأدوات المكتبية'
]);
DB::table('brands')->where('name', 'Apple')->update([
    'name_ar' => 'آبل',
    'description_ar' => 'أجهزة إلكترونية وتقنية عالية الجودة'
]);
DB::table('brands')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);

// Units
DB::table('units')->where('name', 'Piece')->update(['name_ar' => 'قطعة', 'short_name_ar' => 'قطعة']);
DB::table('units')->where('name', 'Box')->update(['name_ar' => 'صندوق', 'short_name_ar' => 'صندوق']);
DB::table('units')->where('name', 'Packet')->update(['name_ar' => 'حزمة', 'short_name_ar' => 'حزمة']);
DB::table('units')->where('name', 'Kilogram')->update(['name_ar' => 'كيلوغرام', 'short_name_ar' => 'كغ']);
DB::table('units')->whereNull('name_ar')->update(['name_ar' => DB::raw('name'), 'short_name_ar' => DB::raw('short_name')]);

// Products
DB::table('products')->where('name', 'like', '%book%')->update([
    'name_ar' => 'كتاب',
    'description_ar' => 'كتاب عالي الجودة'
]);
DB::table('products')->where('name', 'like', '%iphone%')->update([
    'name_ar' => 'آيفون',
    'description_ar' => 'هاتف ذكي ممتاز'
]);
DB::table('products')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);

// Coupons
DB::table('coupons')->where('name', 'like', '%first%')->update([
    'name_ar' => 'خصم الطلب الأول',
    'description_ar' => 'خصم خاص للعملاء الجدد'
]);
DB::table('coupons')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);

// Marketing segments
DB::table('marketing_segments')->whereNull('name_ar')->update(['name_ar' => DB::raw('name')]);

// Notification Templates
DB::table('notification_templates')->whereNull('title_ar')->update([
    'title_ar' => DB::raw('title'),
    'subject_ar' => DB::raw('subject'),
    'body_ar' => DB::raw('body')
]);

echo "Arabic seeded successfully!\n";
