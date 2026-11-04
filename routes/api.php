<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PosController;

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
Route::get('pos/history', [PosController::class, 'history']);
Route::get('pos/invoice/{sale}', [PosController::class, 'invoice']);
