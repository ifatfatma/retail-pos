import { ref, computed } from 'vue';
import axios from 'axios';
import { updateEnabledLocales } from '../i18n';

// Shared singleton reactive settings
const settings = ref({
    site_name: 'Retail Store',
    site_tagline: 'Quality retail essentials delivered directly to your door',
    site_logo: null,
    site_logo_url: null,
    announcement_text: '🎉 Free Standard Delivery on orders over ₹500! | Cash on Delivery Available',
    announcement_enabled: '1',

    hero_badge: '⚡ LIVE INVENTORY & REAL-TIME DISPATCH',
    hero_title: 'Quality Retail Essentials',
    hero_subtitle: 'Delivered To Your Door',
    hero_description: 'Shop directly from our local retail store. Everything you see online is linked directly to our counter warehouse inventory in real-time.',
    hero_promo_badge: '⭐ Best Seller Deals',
    hero_promo_title: 'Neighborhood Convenience',
    hero_promo_text: 'Browse our stock catalog, place orders 24/7, and pick up in-store or get express courier delivery.',

    contact_address: '42 Market Street, Commercial Hub, Mumbai',
    contact_phone: '+91 98765 43210',
    contact_email: 'support@retailstore.local',
    store_hours: 'Mon – Sat: 9:00 AM – 9:00 PM | Sun: 10:00 AM – 7:00 PM',
    footer_about: 'Your trusted neighborhood store, now delivering online. Browse thousands of everyday essentials with live stock inventory and instant door-step delivery.',
    copyright_text: 'Retail POS & E-Commerce. All rights reserved.',

    seo_meta_title: 'Retail Online Store - Everyday Essentials with Fast Delivery',
    seo_meta_description: 'Shop everyday essentials, books, electronics, and daily essentials online with real-time stock sync and fast home delivery.',
    seo_meta_keywords: 'retail pos, ecommerce, online shopping, fast delivery, live inventory, store',

    slideshow_enabled: '1',
    slideshow_autoplay: '1',
    slideshow_speed: '5000',
    slideshow_slides_list: [],

    testimonials_enabled: '1',
    testimonials_title: 'What Our Customers Say',
    testimonials_subtitle: 'Trusted by hundreds of happy shoppers for fast delivery, accurate live stock, and genuine store products.',
    testimonials_badge: '⭐ VERIFIED CUSTOMER REVIEWS',
    testimonials_list_items: [],
});

const isLoaded = ref(false);

export function useShopSettings() {
    async function loadSettings(force = false) {
        if (isLoaded.value && !force) return settings.value;

        try {
            const res = await axios.get('/api/shop/settings');
            if (res.data?.data) {
                settings.value = { ...settings.value, ...res.data.data };
                applySeoMeta(settings.value);
                if (settings.value.enabled_languages_list) {
                    updateEnabledLocales(
                        settings.value.enabled_languages_list,
                        settings.value.default_language || 'en'
                    );
                }
                isLoaded.value = true;
            }
        } catch (e) {
            console.error('Failed to load shop settings:', e);
        }

        return settings.value;
    }

    function applySeoMeta(data) {
        if (data.seo_meta_title) {
            document.title = data.seo_meta_title;
        }

        // Meta Description tag
        let metaDesc = document.querySelector('meta[name="description"]');
        if (!metaDesc && data.seo_meta_description) {
            metaDesc = document.createElement('meta');
            metaDesc.name = 'description';
            document.head.appendChild(metaDesc);
        }
        if (metaDesc && data.seo_meta_description) {
            metaDesc.content = data.seo_meta_description;
        }

        // Meta Keywords tag
        let metaKeywords = document.querySelector('meta[name="keywords"]');
        if (!metaKeywords && data.seo_meta_keywords) {
            metaKeywords = document.createElement('meta');
            metaKeywords.name = 'keywords';
            document.head.appendChild(metaKeywords);
        }
        if (metaKeywords && data.seo_meta_keywords) {
            metaKeywords.content = data.seo_meta_keywords;
        }
    }

    return {
        settings,
        isLoaded,
        loadSettings,
        applySeoMeta,
    };
}

