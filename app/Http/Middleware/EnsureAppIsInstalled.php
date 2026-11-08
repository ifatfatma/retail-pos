<?php

namespace App\Http\Middleware;

use App\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    /**
     * Handle an incoming request.
     * Redirects uninstalled systems to /install wizard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Whitelisted paths during installation
        if ($request->is('install*') ||
            $request->is('api/install*') ||
            $request->is('up') ||
            $request->is('build/*') ||
            $request->is('resources/*') ||
            $request->is('@vite*') ||
            $request->is('node_modules/*')
        ) {
            return $next($request);
        }

        if (!InstallationService::isInstalled()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application is not installed. Please visit /install to configure the system.',
                    'installed' => false,
                ], 503);
            }

            return redirect('/install');
        }

        return $next($request);
    }
}
