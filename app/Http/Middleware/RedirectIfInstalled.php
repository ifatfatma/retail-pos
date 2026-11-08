<?php

namespace App\Http\Middleware;

use App\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    /**
     * Handle an incoming request.
     * Prevents access to the installer after the system has already been installed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallationService::isInstalled()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application is already installed. Installer routes are permanently disabled.',
                    'installed' => true,
                ], 403);
            }

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
