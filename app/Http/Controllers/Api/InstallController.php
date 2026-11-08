<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstallationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstallController extends Controller
{
    protected InstallationService $service;

    public function __construct(InstallationService $service)
    {
        $this->service = $service;
    }

    /**
     * Check if the application is installed.
     */
    public function status(): JsonResponse
    {
        $isInstalled = InstallationService::isInstalled();
        $info = $isInstalled ? InstallationService::getInstallationInfo() : null;

        return response()->json([
            'installed' => $isInstalled,
            'app_name' => $info['app_name'] ?? config('app.name'),
            'installed_at' => $info['installed_at'] ?? null,
            'version' => $info['app_version'] ?? '2.5.0',
        ]);
    }

    /**
     * Get system requirements checklist.
     */
    public function requirements(): JsonResponse
    {
        $result = $this->service->checkRequirements();

        return response()->json($result);
    }

    /**
     * Get directory permissions checklist.
     */
    public function permissions(): JsonResponse
    {
        $result = $this->service->checkPermissions();

        return response()->json($result);
    }

    /**
     * Test database connection.
     */
    public function testDatabase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'host'     => 'required|string',
            'port'     => 'required|numeric',
            'database' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ], [
            'database.regex' => 'Database name may only contain alphanumeric characters, dashes and underscores.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->service->testDatabaseConnection($validator->validated());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Run full installation.
     */
    public function run(Request $request): JsonResponse
    {
        if (InstallationService::isInstalled()) {
            return response()->json([
                'success' => false,
                'message' => 'System is already installed. Reinstallation is permanently disabled.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            // Database validation
            'database.host'     => 'required|string',
            'database.port'     => 'required|numeric',
            'database.database' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/',
            'database.username' => 'required|string',
            'database.password' => 'nullable|string',

            // Application configuration validation
            'app.app_name'         => 'required|string|max:100',
            'app.app_url'          => 'required|url',
            'app.app_env'          => 'required|in:production,local',
            'app.app_timezone'     => 'required|string',
            'app.default_language' => 'required|in:en,ar,hi,ur,bn',
            'app.currency'         => 'required|string|max:10',
            'app.session_driver'   => 'nullable|string|in:database,file',

            // Administrator validation
            'admin.name'                  => 'required|string|min:3|max:100',
            'admin.email'                 => 'required|email|max:150',
            'admin.password'              => 'required|string|min:8|confirmed',
            'admin.password_confirmation' => 'required|string',

            // Business info validation
            'business.business_name' => 'nullable|string|max:150',
            'business.email'         => 'nullable|email|max:150',
            'business.phone'         => 'nullable|string|max:50',
            'business.address'       => 'nullable|string|max:255',
            'business.country'       => 'nullable|string|max:100',
            'business.state'         => 'nullable|string|max:100',
            'business.city'          => 'nullable|string|max:100',
            'business.postal_code'   => 'nullable|string|max:30',
            'business.tax_number'    => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->service->runInstallation($validator->validated());

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
