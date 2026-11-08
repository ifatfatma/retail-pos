<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    // Retrieve all settings with defaults
    public function index()
    {
        return response()->json([
            'data' => Setting::getAll(),
        ]);
    }

    // Save/update settings and handle logo file upload
    public function update(Request $request)
    {
        $groupMap = [
            'site_name' => 'general',
            'site_tagline' => 'general',
            'announcement_text' => 'general',
            'announcement_enabled' => 'general',

            'hero_badge' => 'hero',
            'hero_title' => 'hero',
            'hero_subtitle' => 'hero',
            'hero_description' => 'hero',
            'hero_promo_badge' => 'hero',
            'hero_promo_title' => 'hero',
            'hero_promo_text' => 'hero',

            'contact_address' => 'contact',
            'contact_phone' => 'contact',
            'contact_email' => 'contact',
            'store_hours' => 'contact',
            'footer_about' => 'contact',
            'copyright_text' => 'contact',

            'seo_meta_title' => 'seo',
            'seo_meta_description' => 'seo',
            'seo_meta_keywords' => 'seo',

            'slideshow_enabled' => 'slideshow',
            'slideshow_autoplay' => 'slideshow',
            'slideshow_speed' => 'slideshow',
            'slideshow_slides' => 'slideshow',

            'testimonials_enabled' => 'testimonials',
            'testimonials_title' => 'testimonials',
            'testimonials_subtitle' => 'testimonials',
            'testimonials_badge' => 'testimonials',
            'testimonials_list' => 'testimonials',

            'upi_id' => 'payment',
            'upi_merchant_name' => 'payment',
            'upi_enabled' => 'payment',

            'enabled_languages' => 'localization',
            'default_language' => 'localization',
            'language_switcher_enabled' => 'localization',
        ];

        // Handle logo file upload if provided
        if ($request->hasFile('site_logo')) {
            $request->validate([
                'site_logo' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            ]);

            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::set('site_logo', $path, 'general');
        }

        // Handle remove logo flag
        if ($request->boolean('remove_logo')) {
            $currentLogo = Setting::where('key', 'site_logo')->first()?->value;
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }
            Setting::set('site_logo', null, 'general');
        }

        // Update all other provided text settings
        foreach ($groupMap as $key => $group) {
            if ($request->has($key)) {
                $value = $request->input($key);
                if ($key === 'slideshow_slides' && is_array($value)) {
                    $value = json_encode($value);
                }
                if ($key === 'testimonials_list' && is_array($value)) {
                    $value = json_encode($value);
                }
                if ($key === 'enabled_languages' && is_array($value)) {
                    $value = json_encode(array_values($value));
                }
                Setting::set($key, $value, $group);
            }
        }

        return response()->json([
            'message' => 'Website static contents, logo, and SEO settings updated successfully.',
            'data' => Setting::getAll(),
        ]);
    }

    // Upload slide banner image
    public function uploadSlideImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('slides', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'message' => 'Slide image uploaded successfully.',
            'path' => $path,
            'url' => $url,
        ]);
    }
}

