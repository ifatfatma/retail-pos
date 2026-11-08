<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\InstallationService;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "========================================================\n";
echo "   RETAIL POS INSTALLER COMPREHENSIVE PIPELINE TEST     \n";
echo "========================================================\n\n";

$service = new InstallationService();

// 1. Test Reset Command
echo "1. Testing Artisan Reset Command...\n";
Artisan::call('app:reset-installer', ['--force' => true]);
$isInstalledAfterReset = InstallationService::isInstalled();
assert(!$isInstalledAfterReset, "Application should be marked as uninstalled after reset.");
echo "   -> [PASSED] Installer is unlocked: installed = false\n\n";

// 2. Test Requirements Check
echo "2. Testing System Requirements Check...\n";
$reqs = $service->checkRequirements();
assert($reqs['all_passed'] === true, "All PHP requirements should pass.");
echo "   -> [PASSED] PHP Version: " . PHP_VERSION . "\n";
echo "   -> [PASSED] Total requirements evaluated: " . count($reqs['requirements']) . " (All passed)\n\n";

// 3. Test Permissions Check
echo "3. Testing Folder Permissions Check...\n";
$perms = $service->checkPermissions();
assert($perms['all_passed'] === true, "All folder permissions should pass.");
echo "   -> [PASSED] All " . count($perms['permissions']) . " directories/files are writable.\n\n";

// 4. Test Database Connection Test (Valid Credentials)
echo "4. Testing DB Connection with Valid Credentials...\n";
$dbValid = [
    'host' => config('database.connections.mysql.host', '127.0.0.1'),
    'port' => config('database.connections.mysql.port', 3306),
    'database' => config('database.connections.mysql.database', 'retail_pos'),
    'username' => config('database.connections.mysql.username', 'root'),
    'password' => config('database.connections.mysql.password', ''),
];
$dbSuccessRes = $service->testDatabaseConnection($dbValid);
assert($dbSuccessRes['success'] === true, "Valid database connection should succeed.");
echo "   -> [PASSED] Connection succeeded: " . $dbSuccessRes['message'] . " (Server: " . ($dbSuccessRes['server_version'] ?? 'N/A') . ")\n\n";

// 5. Test Database Connection Test (Invalid Credentials)
echo "5. Testing DB Connection with Invalid Credentials...\n";
$dbInvalid = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'retail_pos',
    'username' => 'non_existent_user_xyz',
    'password' => 'wrong_pass_xyz',
];
$dbFailRes = $service->testDatabaseConnection($dbInvalid);
assert($dbFailRes['success'] === false, "Invalid database connection should fail safely.");
assert(!str_contains($dbFailRes['message'], 'wrong_pass_xyz'), "Password should never leak in error response.");
echo "   -> [PASSED] Connection safely rejected without exposing passwords: " . $dbFailRes['message'] . "\n\n";

// 6. Test Full Installation Run
echo "6. Testing Full Installation Execution...\n";
$installPayload = [
    'database' => $dbValid,
    'app' => [
        'app_name' => 'Smart Stationery POS',
        'app_url' => 'http://127.0.0.1:8000',
        'app_env' => 'local',
        'app_timezone' => 'Asia/Kolkata',
        'default_language' => 'en',
        'currency' => '₹',
        'session_driver' => 'database',
    ],
    'admin' => [
        'name' => 'Administrator',
        'email' => 'admin@retailpos.local',
        'password' => 'password',
        'password_confirmation' => 'password',
    ],
    'business' => [
        'business_name' => 'Smart Stationery & Office Supplies',
        'tagline' => 'Quality retail essentials delivered to your door',
        'email' => 'admin@retailpos.local',
        'phone' => '+91 98765 43210',
        'address' => '42 Market Street, Commercial Hub',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'country' => 'India',
        'postal_code' => '400001',
        'tax_number' => 'GSTIN 27AAAAA0000A1Z5',
    ],
];

$installResult = $service->runInstallation($installPayload);
assert($installResult['success'] === true, "Installation execution must succeed.");
echo "   -> [PASSED] Installation succeeded!\n";
echo "   -> [PASSED] Installed at: " . $installResult['installed_at'] . "\n";
echo "   -> [PASSED] Admin email: " . $installResult['admin_email'] . "\n\n";

// 7. Verify Database Records
echo "7. Verifying Created Database Records...\n";
$adminUser = User::where('email', 'admin@retailpos.local')->first();
assert($adminUser !== null, "Admin user should exist.");
assert($adminUser->hasRole('Admin'), "Admin user should have 'Admin' role assigned.");
assert(Hash::check('password', $adminUser->password), "Admin password should match hashed password.");
echo "   -> [PASSED] Admin user verified with Spatie 'Admin' role and secure bcrypt hash.\n";

$savedStoreName = Setting::get('site_name');
assert($savedStoreName === 'Smart Stationery & Office Supplies', "Business name should be stored in settings.");
echo "   -> [PASSED] Business settings verified in Setting table: site_name = {$savedStoreName}\n\n";

// 8. Verify Permanent Installation Lock
echo "8. Verifying Permanent Installation Lock...\n";
$isLockedNow = InstallationService::isInstalled();
assert($isLockedNow === true, "System must be locked after installation.");

$info = InstallationService::getInstallationInfo();
echo "   -> [PASSED] Installation marker active: " . InstallationService::installedFilePath() . "\n";
echo "   -> [PASSED] Marker info: " . json_encode($info) . "\n\n";

// 9. Verify Second Installation Attempt Throws Exception
echo "9. Verifying Duplicate Installation Protection...\n";
try {
    $service->runInstallation($installPayload);
    echo "   -> [FAILED] Should have thrown exception for duplicate installation!\n";
    exit(1);
} catch (Exception $e) {
    echo "   -> [PASSED] Duplicate execution prevented: " . $e->getMessage() . "\n\n";
}

echo "========================================================\n";
echo "   ALL INSTALLATION PIPELINE TESTS PASSED (100% OK)     \n";
echo "========================================================\n";
