<?php

namespace App\Providers;

use App\Services\InstallationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Safe session fallback for installer and uninstalled databases.
        // Prevents SQLSTATE[42S02] 'Table sessions doesn't exist' when database is fresh or unmigrated.
        $isInstallRoute = isset($_SERVER['REQUEST_URI']) && (
            str_contains($_SERVER['REQUEST_URI'], 'install') ||
            str_contains($_SERVER['REQUEST_URI'], 'api/install')
        );

        if ($isInstallRoute || !file_exists(storage_path('installed'))) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
            ]);
        }

        if (!is_dir(storage_path('framework/sessions'))) {
            @mkdir(storage_path('framework/sessions'), 0775, true);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
