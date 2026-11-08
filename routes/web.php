<?php

use App\Services\InstallationService;
use Illuminate\Support\Facades\Route;

// Dedicated installation wizard route
Route::get('/install/{any?}', function () {
    if (InstallationService::isInstalled()) {
        return redirect('/admin/login');
    }
    return view('app');
})->where('any', '.*');

// Main application routes - redirect to /install if system has not been set up
Route::get('/{any}', function () {
    if (!InstallationService::isInstalled()) {
        return redirect('/install');
    }
    return view('app');
})->where('any', '.*');
