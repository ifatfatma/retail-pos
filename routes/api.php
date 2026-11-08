<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\MarketingController;
use App\Http\Controllers\Api\InstallController;
use App\Http\Middleware\RedirectIfInstalled;

// Administrator Authentication & Profile Management
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('password', [AuthController::class, 'changePassword']);
    Route::post('logout', [AuthController::class, 'logout']);
});

// Front Website Content & SEO Settings Management
Route::get('settings', [SettingController::class, 'index']);
Route::post('settings', [SettingController::class, 'update']);
Route::post('settings/slide-image', [SettingController::class, 'uploadSlideImage']);

// Catalog
Route::apiResource('categories', CategoryController::class);
Route::apiResource('brands', BrandController::class);
Route::apiResource('units', UnitController::class);
Route::apiResource('products', ProductController::class);
Route::post('products/{product}/images', [ProductController::class, 'uploadImages']);

// Inventory & Stock Management
Route::get('inventory', [InventoryController::class, 'index']);
Route::get('inventory/summary', [InventoryController::class, 'summary']);
Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
Route::get('inventory/transactions', [InventoryController::class, 'transactions']);
Route::get('inventory/{product}/history', [InventoryController::class, 'history']);

// Suppliers
Route::get('suppliers/all', [SupplierController::class, 'all']);
Route::apiResource('suppliers', SupplierController::class);

// Purchases
Route::get('purchases/summary', [PurchaseController::class, 'summary']);
Route::apiResource('purchases', PurchaseController::class);

// Customers
Route::get('customers/all', [CustomerController::class, 'all']);
Route::apiResource('customers', CustomerController::class);

// POS Counter
Route::get('pos/products', [PosController::class, 'products']);
Route::post('pos/checkout', [PosController::class, 'checkout']);
Route::post('pos/coupons/validate', [PosController::class, 'validateCoupon']);
Route::get('pos/history', [PosController::class, 'history']);
Route::get('pos/invoice/{sale}', [PosController::class, 'invoice']);

// Coupons & Discount Management (Admin)
Route::patch('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus']);
Route::post('coupons/{coupon}/duplicate', [CouponController::class, 'duplicate']);
Route::get('coupons/{coupon}/usages', [CouponController::class, 'usages']);
Route::get('coupons/{coupon}/customer-report', [CouponController::class, 'customerReport']);
Route::get('coupons/{coupon}/audit-logs', [CouponController::class, 'auditLogs']);
Route::apiResource('coupons', CouponController::class);

// Online Orders
Route::get('orders/summary', [OrderController::class, 'summary']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking']);
Route::patch('orders/{order}/payment', [OrderController::class, 'updatePayment']);
Route::apiResource('orders', OrderController::class);

// Reports & Analytics
Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
Route::get('reports/products', [ReportController::class, 'productPerformance']);
Route::get('reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
Route::get('reports/profit-loss', [ReportController::class, 'profitLoss']);

// Marketing & Notifications Module (Admin)
Route::prefix('marketing')->group(function () {
    Route::get('dashboard', [MarketingController::class, 'dashboard']);

    // Campaigns
    Route::get('campaigns', [MarketingController::class, 'campaigns']);
    Route::get('campaigns/scheduled', [MarketingController::class, 'scheduledCampaigns']);
    Route::post('campaigns', [MarketingController::class, 'storeCampaign']);
    Route::get('campaigns/{campaign}', [MarketingController::class, 'showCampaign']);
    Route::put('campaigns/{campaign}', [MarketingController::class, 'updateCampaign']);
    Route::delete('campaigns/{campaign}', [MarketingController::class, 'destroyCampaign']);
    Route::post('campaigns/{campaign}/send', [MarketingController::class, 'sendCampaignNow']);
    Route::post('campaigns/{campaign}/toggle-status', [MarketingController::class, 'toggleCampaignStatus']);

    // Customer Segments
    Route::get('segments', [MarketingController::class, 'segments']);
    Route::post('segments', [MarketingController::class, 'storeSegment']);
    Route::put('segments/{segment}', [MarketingController::class, 'updateSegment']);
    Route::delete('segments/{segment}', [MarketingController::class, 'destroySegment']);
    Route::get('segments/{segment}/customers', [MarketingController::class, 'segmentCustomers']);

    // Automated Rules
    Route::get('rules', [MarketingController::class, 'rules']);
    Route::post('rules', [MarketingController::class, 'storeRule']);
    Route::put('rules/{rule}', [MarketingController::class, 'updateRule']);
    Route::delete('rules/{rule}', [MarketingController::class, 'destroyRule']);
    Route::post('rules/{rule}/toggle-active', [MarketingController::class, 'toggleRuleActive']);
    Route::post('rules/{rule}/run-now', [MarketingController::class, 'runRuleNow']);

    // Notification Templates
    Route::get('templates', [MarketingController::class, 'templates']);
    Route::post('templates', [MarketingController::class, 'storeTemplate']);
    Route::put('templates/{template}', [MarketingController::class, 'updateTemplate']);
    Route::delete('templates/{template}', [MarketingController::class, 'destroyTemplate']);

    // Delivery Reports & Logs
    Route::get('delivery-reports', [MarketingController::class, 'deliveryReports']);

    // Settings & Testing
    Route::get('settings', [MarketingController::class, 'getSettings']);
    Route::post('settings', [MarketingController::class, 'updateSettings']);
    Route::post('test-message', [MarketingController::class, 'sendTestMessage']);
});

// Public E-Commerce Storefront
Route::prefix('shop')->group(function () {
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('marketing/banners', [MarketingController::class, 'activeStorefrontBanners']);
    Route::get('products', [ShopController::class, 'products']);
    Route::get('products/{idOrSlug}', [ShopController::class, 'productDetails']);
    Route::get('categories', [ShopController::class, 'categories']);
    Route::post('coupons/validate', [ShopController::class, 'validateCoupon']);
    Route::post('checkout', [ShopController::class, 'checkout']);
    Route::get('track/{orderNumber}', [ShopController::class, 'trackOrder']);

    // Customer In-App Notifications & Preferences
    Route::get('account/notifications', [MarketingController::class, 'customerNotifications']);
    Route::patch('account/notifications/{notification}/read', [MarketingController::class, 'markNotificationRead']);
    Route::patch('account/notifications/read-all', [MarketingController::class, 'markAllNotificationsRead']);
    Route::get('account/notification-preferences', [MarketingController::class, 'getCustomerPreferences']);
    Route::put('account/notification-preferences', [MarketingController::class, 'updateCustomerPreferences']);
    Route::post('account/notification-preferences/unsubscribe-all', [MarketingController::class, 'unsubscribeAll']);

    // Customer Authentication & Account Management
    Route::prefix('auth')->group(function () {
        Route::post('register', [CustomerAuthController::class, 'register']);
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::post('forgot-password', [CustomerAuthController::class, 'forgotPassword']);
        Route::post('reset-password', [CustomerAuthController::class, 'resetPassword']);
        Route::get('me', [CustomerAuthController::class, 'me']);
        Route::put('profile', [CustomerAuthController::class, 'updateProfile']);
        Route::put('password', [CustomerAuthController::class, 'changePassword']);
        Route::post('logout', [CustomerAuthController::class, 'logout']);
    });

    // Customer Orders & Invoices
    Route::get('account/orders', [CustomerAuthController::class, 'orders']);
    Route::get('account/orders/{orderNumber}', [CustomerAuthController::class, 'orderDetails']);

    // Customer & Guest Wishlist
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::delete('wishlist/{productId}', [WishlistController::class, 'destroy']);
    Route::post('wishlist/sync', [WishlistController::class, 'sync']);
    Route::delete('wishlist', [WishlistController::class, 'clear']);
});

// Web-Based Installation Wizard API Endpoints
Route::prefix('install')->middleware(['throttle:30,1'])->group(function () {
    Route::get('status', [InstallController::class, 'status']);
    Route::middleware(RedirectIfInstalled::class)->group(function () {
        Route::get('requirements', [InstallController::class, 'requirements']);
        Route::get('permissions', [InstallController::class, 'permissions']);
        Route::post('database/test', [InstallController::class, 'testDatabase']);
        Route::post('run', [InstallController::class, 'run']);
    });
});

