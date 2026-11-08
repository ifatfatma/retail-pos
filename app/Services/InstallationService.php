<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;

class InstallationService
{
    /**
     * Get path to installation marker file.
     */
    public static function installedFilePath(): string
    {
        return storage_path('installed');
    }

    /**
     * Get path to installation lock file.
     */
    public static function lockFilePath(): string
    {
        return storage_path('framework/install.lock');
    }

    /**
     * Check if application is already installed.
     */
    public static function isInstalled(): bool
    {
        if (!file_exists(static::installedFilePath())) {
            return false;
        }

        // Verify that database is accessible and actually migrated
        // Prevents crashes if DB_DATABASE is changed in .env to a fresh uninstalled database
        try {
            $info = static::getInstallationInfo();
            $installedDb = $info['database'] ?? null;
            $currentDb = config('database.connections.mysql.database');

            if ($installedDb && $currentDb && $installedDb !== $currentDb) {
                return false;
            }

            if (!DB::connection()->getSchemaBuilder()->hasTable('users')) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * Mark application as installed with metadata.
     */
    public static function markAsInstalled(array $metadata = []): bool
    {
        $data = array_merge([
            'installed' => true,
            'installed_at' => now()->toIso8601String(),
            'app_name' => config('app.name', 'Retail POS'),
            'app_version' => '2.5.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
        ], $metadata);

        return (bool) file_put_contents(
            static::installedFilePath(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Get installation metadata.
     */
    public static function getInstallationInfo(): ?array
    {
        $filePath = static::installedFilePath();
        if (!file_exists($filePath)) {
            return null;
        }

        $content = @file_get_contents($filePath);
        if (!$content) {
            return ['installed' => true, 'installed_at' => null];
        }

        return json_decode($content, true) ?: ['installed' => true];
    }

    /**
     * Check all system PHP requirements.
     */
    public function checkRequirements(): array
    {
        $minPhpVersion = '8.2.0';
        $currentPhpVersion = PHP_VERSION;
        $phpVersionPassed = version_compare($currentPhpVersion, $minPhpVersion, '>=');

        $requiredExtensions = [
            'pdo_mysql' => ['label' => 'PDO MySQL Driver', 'critical' => true],
            'openssl'   => ['label' => 'OpenSSL Extension', 'critical' => true],
            'mbstring'  => ['label' => 'Mbstring Extension', 'critical' => true],
            'tokenizer' => ['label' => 'Tokenizer Extension', 'critical' => true],
            'xml'       => ['label' => 'XML / DOM Extension', 'critical' => true],
            'ctype'     => ['label' => 'Ctype Extension', 'critical' => true],
            'json'      => ['label' => 'JSON Extension', 'critical' => true],
            'fileinfo'  => ['label' => 'Fileinfo Extension', 'critical' => true],
            'curl'      => ['label' => 'cURL Extension', 'critical' => true],
            'bcmath'    => ['label' => 'BCMath Extension', 'critical' => false],
            'gd'        => ['label' => 'GD Library Extension', 'critical' => false],
        ];

        $results = [];
        $allPassed = $phpVersionPassed;

        $results[] = [
            'name' => 'php_version',
            'label' => 'PHP Version',
            'required' => ">= {$minPhpVersion}",
            'current' => $currentPhpVersion,
            'status' => $phpVersionPassed ? 'passed' : 'failed',
            'critical' => true,
        ];

        foreach ($requiredExtensions as $ext => $info) {
            $isLoaded = extension_loaded($ext);
            if (!$isLoaded && $info['critical']) {
                $allPassed = false;
            }

            $results[] = [
                'name' => $ext,
                'label' => $info['label'],
                'required' => 'Enabled',
                'current' => $isLoaded ? 'Enabled' : 'Disabled',
                'status' => $isLoaded ? 'passed' : ($info['critical'] ? 'failed' : 'warning'),
                'critical' => $info['critical'],
            ];
        }

        return [
            'all_passed' => $allPassed,
            'requirements' => $results,
        ];
    }

    /**
     * Check storage and bootstrap directory permissions.
     */
    public function checkPermissions(): array
    {
        $directories = [
            'storage/app'                => ['label' => 'storage/app', 'path' => storage_path('app')],
            'storage/framework'          => ['label' => 'storage/framework', 'path' => storage_path('framework')],
            'storage/framework/cache'    => ['label' => 'storage/framework/cache', 'path' => storage_path('framework/cache')],
            'storage/framework/sessions' => ['label' => 'storage/framework/sessions', 'path' => storage_path('framework/sessions')],
            'storage/framework/views'    => ['label' => 'storage/framework/views', 'path' => storage_path('framework/views')],
            'storage/logs'               => ['label' => 'storage/logs', 'path' => storage_path('logs')],
            'bootstrap/cache'            => ['label' => 'bootstrap/cache', 'path' => base_path('bootstrap/cache')],
        ];

        $results = [];
        $allPassed = true;

        foreach ($directories as $key => $info) {
            $path = $info['path'];
            if (!file_exists($path)) {
                @mkdir($path, 0775, true);
            }

            $isWritable = is_writable($path);
            if (!$isWritable) {
                $allPassed = false;
            }

            $results[] = [
                'folder' => $info['label'],
                'path' => $path,
                'is_writable' => $isWritable,
                'status' => $isWritable ? 'passed' : 'failed',
            ];
        }

        // Check if root or .env is writable
        $envPath = base_path('.env');
        $envWritable = file_exists($envPath) ? is_writable($envPath) : is_writable(base_path());
        if (!$envWritable) {
            $allPassed = false;
        }

        $results[] = [
            'folder' => '.env Configuration File',
            'path' => $envPath,
            'is_writable' => $envWritable,
            'status' => $envWritable ? 'passed' : 'failed',
        ];

        return [
            'all_passed' => $allPassed,
            'permissions' => $results,
        ];
    }

    /**
     * Test database connection with provided credentials without throwing uncaught exceptions.
     */
    public function testDatabaseConnection(array $config): array
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? 3306);
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        if (empty($database)) {
            return [
                'success' => false,
                'message' => 'Database name is required.',
            ];
        }

        try {
            // Test 1: Connect directly to the specific database
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

            return [
                'success' => true,
                'message' => "Successfully connected to MySQL database '{$database}'.",
                'server_version' => $version,
                'database_exists' => true,
            ];
        } catch (PDOException $e) {
            // If database does not exist (error 1049), check if server can be reached and if we can create it
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $serverPdo = new PDO($serverDsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);

                    $version = $serverPdo->getAttribute(PDO::ATTR_SERVER_VERSION);

                    return [
                        'success' => true,
                        'message' => "Connected to MySQL server. Database '{$database}' will be automatically created during installation.",
                        'server_version' => $version,
                        'database_exists' => false,
                    ];
                } catch (PDOException $serverError) {
                    return [
                        'success' => false,
                        'message' => 'Connection to MySQL server failed. Please verify host, port, username and password.',
                    ];
                }
            }

            // Return sanitized, safe error message without passwords
            $errorMsg = 'Could not connect to database. ';
            if (str_contains($e->getMessage(), 'Access denied')) {
                $errorMsg .= 'Access denied for user. Please verify database username and password.';
            } elseif (str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'actively refused')) {
                $errorMsg .= "Connection refused on {$host}:{$port}. Please ensure MySQL service is running.";
            } else {
                $errorMsg .= 'Please verify connection details.';
            }

            return [
                'success' => false,
                'message' => $errorMsg,
            ];
        }
    }

    /**
     * Safely update or create .env file values.
     */
    public function updateEnvironmentFile(array $values): bool
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (!file_exists($envPath)) {
            if (file_exists($examplePath)) {
                copy($examplePath, $envPath);
            } else {
                touch($envPath);
            }
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            // Format value safely: quote strings with spaces or special characters
            $formattedValue = $this->formatEnvValue($value);

            // Pattern to match existing key
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
            } else {
                $content = rtrim($content) . "\n{$key}={$formattedValue}\n";
            }
        }

        return (bool) file_put_contents($envPath, $content);
    }

    /**
     * Format environment variable value with quotes if needed.
     */
    protected function formatEnvValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        $str = (string) $value;

        if (str_contains($str, ' ') || str_contains($str, '#') || str_contains($str, '$') || str_contains($str, '"')) {
            $escaped = str_replace('"', '\"', $str);
            return "\"{$escaped}\"";
        }

        return $str;
    }

    /**
     * Generate secure 32-byte base64 application key.
     */
    public function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Master installation runner.
     */
    public function runInstallation(array $data): array
    {
        if (static::isInstalled()) {
            throw new Exception('Application is already installed.');
        }

        $lockPath = static::lockFilePath();
        if (file_exists($lockPath)) {
            // Check if lock file is older than 5 minutes (stale lock recovery)
            if (time() - filemtime($lockPath) < 300) {
                throw new Exception('Installation is currently in progress by another request. Please wait.');
            }
        }

        // Acquire installation lock
        file_put_contents($lockPath, (string) time());

        try {
            // 1. Validate database connection
            $dbConfig = $data['database'];
            $dbTest = $this->testDatabaseConnection($dbConfig);
            if (!$dbTest['success']) {
                throw new Exception($dbTest['message']);
            }

            // If database does not exist, attempt creation
            if (isset($dbTest['database_exists']) && $dbTest['database_exists'] === false) {
                $host = $dbConfig['host'] ?? '127.0.0.1';
                $port = (int) ($dbConfig['port'] ?? 3306);
                $database = $dbConfig['database'];
                $username = $dbConfig['username'] ?? 'root';
                $password = $dbConfig['password'] ?? '';

                $serverPdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $safeDbName = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            // 2. Dynamically set runtime database and app configuration
            $appConfig = $data['app'];
            $appKey = config('app.key') ?: $this->generateAppKey();

            config([
                'database.connections.mysql.host' => $dbConfig['host'] ?? '127.0.0.1',
                'database.connections.mysql.port' => $dbConfig['port'] ?? 3306,
                'database.connections.mysql.database' => $dbConfig['database'],
                'database.connections.mysql.username' => $dbConfig['username'] ?? 'root',
                'database.connections.mysql.password' => $dbConfig['password'] ?? '',
                'app.name' => $appConfig['app_name'] ?? 'Smart Stationery POS',
                'app.timezone' => $appConfig['app_timezone'] ?? 'Asia/Kolkata',
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');

            // 3. Run database migrations safely
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            // 4. Run essential initial seeders (RoleSeeder)
            Artisan::call('db:seed', [
                '--class' => RoleSeeder::class,
                '--force' => true,
            ]);

            // 5. Create Administrator Account
            $adminConfig = $data['admin'];
            $adminUser = User::updateOrCreate(
                ['email' => $adminConfig['email']],
                [
                    'name' => $adminConfig['name'],
                    'password' => Hash::make($adminConfig['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (!$adminUser->hasRole('Admin')) {
                $adminUser->assignRole('Admin');
            }

            // 6. Store Business & Application Settings
            $businessConfig = $data['business'] ?? [];
            Setting::set('site_name', $businessConfig['business_name'] ?? $appConfig['app_name'] ?? 'Smart Stationery Shop', 'general');
            Setting::set('site_tagline', $businessConfig['tagline'] ?? 'Quality retail essentials & stationery', 'general');
            Setting::set('contact_email', $businessConfig['email'] ?? $adminConfig['email'], 'general');
            Setting::set('contact_phone', $businessConfig['phone'] ?? '+91 98765 43210', 'general');
            Setting::set('contact_address', $businessConfig['address'] ?? '', 'general');
            Setting::set('default_language', $appConfig['default_language'] ?? 'en', 'localization');
            Setting::set('currency', $appConfig['currency'] ?? '₹', 'general');

            if (!empty($businessConfig['tax_number'])) {
                Setting::set('tax_registration_number', $businessConfig['tax_number'], 'general');
            }
            if (!empty($businessConfig['country'])) {
                Setting::set('store_country', $businessConfig['country'], 'general');
            }
            if (!empty($businessConfig['state'])) {
                Setting::set('store_state', $businessConfig['state'], 'general');
            }
            if (!empty($businessConfig['city'])) {
                Setting::set('store_city', $businessConfig['city'], 'general');
            }
            if (!empty($businessConfig['postal_code'])) {
                Setting::set('store_postal_code', $businessConfig['postal_code'], 'general');
            }

            // 7. Write .env configuration file
            $envUpdates = [
                'APP_NAME' => $appConfig['app_name'] ?? 'Smart Stationery POS',
                'APP_ENV' => $appConfig['app_env'] ?? 'production',
                'APP_KEY' => $appKey,
                'APP_DEBUG' => ($appConfig['app_env'] ?? 'production') === 'local' ? 'true' : 'false',
                'APP_URL' => $appConfig['app_url'] ?? url('/'),
                'APP_LOCALE' => $appConfig['default_language'] ?? 'en',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $dbConfig['host'] ?? '127.0.0.1',
                'DB_PORT' => $dbConfig['port'] ?? 3306,
                'DB_DATABASE' => $dbConfig['database'],
                'DB_USERNAME' => $dbConfig['username'] ?? 'root',
                'DB_PASSWORD' => $dbConfig['password'] ?? '',
                'SESSION_DRIVER' => $appConfig['session_driver'] ?? 'file',
                'CACHE_STORE' => 'file',
            ];

            $this->updateEnvironmentFile($envUpdates);

            // 8. Record installation completion marker
            $installedAt = now()->toIso8601String();
            static::markAsInstalled([
                'app_name' => $appConfig['app_name'] ?? 'Retail POS',
                'admin_email' => $adminConfig['email'],
                'installed_at' => $installedAt,
                'currency' => $appConfig['currency'] ?? '₹',
                'database' => $dbConfig['database'],
            ]);

            // Clean up installation lock
            @unlink($lockPath);

            return [
                'success' => true,
                'message' => 'Installation completed successfully.',
                'app_name' => $appConfig['app_name'] ?? 'Retail POS',
                'admin_email' => $adminConfig['email'],
                'installed_at' => $installedAt,
                'redirect_url' => '/admin/login',
            ];
        } catch (Exception $e) {
            // Clean up lock on failure
            @unlink($lockPath);
            Log::error('Installation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
