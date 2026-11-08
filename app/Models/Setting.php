<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'value',
    ];

    public static function defaults(): array
    {
        return [
            // Localization & Languages
            'enabled_languages' => json_encode(['en', 'ar', 'hi', 'ur', 'bn']),
            'default_language' => 'en',
            'language_switcher_enabled' => '1',

            // General & Branding
            'site_name' => 'Retail Store',
            'site_tagline' => 'Quality retail essentials delivered directly to your door',
            'site_logo' => null,
            'announcement_text' => '🎉 Free Standard Delivery on orders over ₹500! | Cash on Delivery Available',
            'announcement_enabled' => '1',

            // Hero Section (Home)
            'hero_badge' => '⚡ LIVE INVENTORY & REAL-TIME DISPATCH',
            'hero_title' => 'Quality Retail Essentials',
            'hero_subtitle' => 'Delivered To Your Door',
            'hero_description' => 'Shop directly from our local retail store. Everything you see online is linked directly to our counter warehouse inventory in real-time.',
            'hero_promo_badge' => '⭐ Best Seller Deals',
            'hero_promo_title' => 'Neighborhood Convenience',
            'hero_promo_text' => 'Browse our stock catalog, place orders 24/7, and pick up in-store or get express courier delivery.',

            // Homepage Slideshow / Banners
            'slideshow_enabled' => '1',
            'slideshow_autoplay' => '1',
            'slideshow_speed' => '5000',
            'slideshow_slides' => json_encode([
                [
                    'id' => 1,
                    'badge' => '🔥 FESTIVE SALE • UP TO 40% OFF',
                    'title' => 'Premium Stationery & Office Essentials',
                    'subtitle' => 'High quality writing notebooks, fine pens, art supplies, and desk accessories curated for professionals and students.',
                    'primary_btn_text' => 'Shop Collection →',
                    'primary_btn_link' => '/shop',
                    'secondary_btn_text' => 'Explore All',
                    'secondary_btn_link' => '/shop',
                    'bg_theme' => 'blue-gradient',
                    'image_url' => '',
                    'active' => true,
                ],
                [
                    'id' => 2,
                    'badge' => '⚡ NEW ARRIVALS & GADGETS',
                    'title' => 'Latest Smartphones & Daily Tech',
                    'subtitle' => 'Discover genuine devices, accessories, and electronics with same-day dispatch and verified authentic warranty.',
                    'primary_btn_text' => 'Browse Electronics →',
                    'primary_btn_link' => '/shop',
                    'secondary_btn_text' => 'Track Your Order',
                    'secondary_btn_link' => '/track-order',
                    'bg_theme' => 'dark-gradient',
                    'image_url' => '',
                    'active' => true,
                ],
                [
                    'id' => 3,
                    'badge' => '🚚 LOCAL NEIGHBORHOOD DELIVERY',
                    'title' => 'Live Counter Stock Synchronized 24/7',
                    'subtitle' => 'Every product you see online is linked directly to our physical warehouse inventory. Order online, pick up or get delivered!',
                    'primary_btn_text' => 'Start Shopping Now',
                    'primary_btn_link' => '/shop',
                    'secondary_btn_text' => 'Customer Sign In',
                    'secondary_btn_link' => '/login',
                    'bg_theme' => 'emerald-gradient',
                    'image_url' => '',
                    'active' => true,
                ],
            ]),

            // Customer Testimonials (Homepage)
            'testimonials_enabled' => '1',
            'testimonials_title' => 'What Our Customers Say',
            'testimonials_subtitle' => 'Trusted by hundreds of happy shoppers for fast delivery, accurate live stock, and genuine store products.',
            'testimonials_badge' => '⭐ VERIFIED CUSTOMER REVIEWS',
            'testimonials_list' => json_encode([
                [
                    'id' => 1,
                    'name' => 'Pooja Verma',
                    'location' => 'Mumbai, Maharashtra',
                    'rating' => 5,
                    'comment' => 'Ordered stationery and pens for my design office in the morning and received the package by evening. The live stock sync is super reliable — what you see online is actually in the shop!',
                    'purchased_item' => 'Artline Fineliners & Doms Sketch Books',
                    'verified' => true,
                    'date' => 'Yesterday',
                    'active' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Amit Sharma',
                    'location' => 'Kolkata, West Bengal',
                    'rating' => 5,
                    'comment' => 'Excellent customer service and prompt delivery. Cash on Delivery was completely smooth and the courier tracking updates kept me informed every step.',
                    'purchased_item' => 'Classmate Pulse Notebooks (Pack of 6)',
                    'verified' => true,
                    'date' => '3 days ago',
                    'active' => true,
                ],
                [
                    'id' => 3,
                    'name' => 'Dr. Sneha Nair',
                    'location' => 'Pune, Maharashtra',
                    'rating' => 5,
                    'comment' => 'The online ordering experience is seamless! Got an instant invoice, clear dispatch label, and packaging was immaculate. My go-to neighborhood store.',
                    'purchased_item' => 'Parker Vector Rollerball Pen & Refills',
                    'verified' => true,
                    'date' => '1 week ago',
                    'active' => true,
                ],
                [
                    'id' => 4,
                    'name' => 'Rahul Khanna',
                    'location' => 'Delhi, NCR',
                    'rating' => 5,
                    'comment' => 'Genuine products at fair MRP pricing without inflated markups. Plus, registering an account let me track my full purchase history with one click.',
                    'purchased_item' => 'Casio Scientific Calculator FX-991CW',
                    'verified' => true,
                    'date' => '2 weeks ago',
                    'active' => true,
                ],
            ]),

            // Contact & Footer Details
            'contact_address' => '42 Market Street, Commercial Hub, Mumbai',
            'contact_phone' => '+91 98765 43210',
            'contact_email' => 'support@retailstore.local',
            'store_hours' => 'Mon – Sat: 9:00 AM – 9:00 PM | Sun: 10:00 AM – 7:00 PM',
            'footer_about' => 'Your trusted neighborhood store, now delivering online. Browse thousands of everyday essentials with live stock inventory and instant door-step delivery.',
            'copyright_text' => 'Retail POS & E-Commerce. All rights reserved.',

            // SEO & Meta Tags
            'seo_meta_title' => 'Retail Online Store - Everyday Essentials with Fast Delivery',
            'seo_meta_description' => 'Shop everyday essentials, books, electronics, and daily essentials online with real-time stock sync and fast home delivery.',
            'seo_meta_keywords' => 'retail pos, ecommerce, online shopping, fast delivery, live inventory, store',

            // POS & Payment Settings
            'upi_id' => 'smartstationery@upi',
            'upi_merchant_name' => 'Smart Stationery Store',
            'upi_enabled' => '1',

            // Marketing & Notification Settings
            'marketing_test_mode' => '1',
            'marketing_email_enabled' => '1',
            'marketing_email_provider' => 'simulated',
            'marketing_email_from_name' => 'Retail POS Deals',
            'marketing_email_from_address' => 'offers@retailstore.local',
            'marketing_email_smtp_host' => 'smtp.mailtrap.io',
            'marketing_email_smtp_port' => '2525',
            'marketing_email_smtp_user' => '',
            'marketing_email_smtp_pass' => '',

            'marketing_sms_enabled' => '1',
            'marketing_sms_provider' => 'simulated',
            'marketing_sms_sender_id' => 'RETAIL',
            'marketing_sms_api_key' => '',

            'marketing_whatsapp_enabled' => '1',
            'marketing_whatsapp_provider' => 'simulated',
            'marketing_whatsapp_phone_number_id' => '',
            'marketing_whatsapp_token' => '',

            'marketing_push_enabled' => '1',
            'marketing_push_provider' => 'simulated',
            'marketing_push_public_key' => '',
            'marketing_push_private_key' => '',

            'marketing_in_app_enabled' => '1',
        ];
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting && $setting->value !== null) {
            return $setting->value;
        }

        $defaults = static::defaults();
        return $defaults[$key] ?? $default;
    }

    public static function set(string $key, $value, string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public static function getAll(): array
    {
        $defaults = static::defaults();
        $stored = static::all()->pluck('value', 'key')->toArray();
        $merged = array_merge($defaults, $stored);

        // Transform logo URL if stored as path
        if (!empty($merged['site_logo']) && !str_starts_with($merged['site_logo'], 'http')) {
            $merged['site_logo_url'] = asset('storage/' . $merged['site_logo']);
        } else {
            $merged['site_logo_url'] = $merged['site_logo'];
        }

        // Decode slideshow_slides into array for convenience
        if (!empty($merged['slideshow_slides'])) {
            if (is_string($merged['slideshow_slides'])) {
                $decoded = json_decode($merged['slideshow_slides'], true);
                $merged['slideshow_slides_list'] = is_array($decoded) ? $decoded : [];
            } elseif (is_array($merged['slideshow_slides'])) {
                $merged['slideshow_slides_list'] = $merged['slideshow_slides'];
            }
        } else {
            $merged['slideshow_slides_list'] = [];
        }

        // Decode testimonials_list into array
        if (!empty($merged['testimonials_list'])) {
            if (is_string($merged['testimonials_list'])) {
                $decoded = json_decode($merged['testimonials_list'], true);
                $merged['testimonials_list_items'] = is_array($decoded) ? $decoded : [];
            } elseif (is_array($merged['testimonials_list'])) {
                $merged['testimonials_list_items'] = $merged['testimonials_list'];
            }
        } else {
            $merged['testimonials_list_items'] = [];
        }

        // Decode enabled_languages into array
        if (!empty($merged['enabled_languages'])) {
            if (is_string($merged['enabled_languages'])) {
                $decoded = json_decode($merged['enabled_languages'], true);
                $merged['enabled_languages_list'] = is_array($decoded) ? $decoded : ['en', 'ar', 'hi', 'ur', 'bn'];
            } elseif (is_array($merged['enabled_languages'])) {
                $merged['enabled_languages_list'] = $merged['enabled_languages'];
            }
        } else {
            $merged['enabled_languages_list'] = ['en', 'ar', 'hi', 'ur', 'bn'];
        }

        return $merged;
    }
}

