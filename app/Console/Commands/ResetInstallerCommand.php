<?php

namespace App\Console\Commands;

use App\Services\InstallationService;
use Illuminate\Console\Command;

class ResetInstallerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-installer {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely resets the installation lock file to test the web installation wizard';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = InstallationService::installedFilePath();
        $lockPath = InstallationService::lockFilePath();

        if (!file_exists($path) && !file_exists($lockPath)) {
            $this->info('Installer is already unlocked. Application is marked as uninstalled.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('This will unlock the /install route. Are you sure you want to reset the installer marker?')) {
                $this->warn('Reset cancelled.');
                return Command::SUCCESS;
            }
        }

        if (file_exists($path)) {
            @unlink($path);
            $this->info("Deleted installation marker: {$path}");
        }

        if (file_exists($lockPath)) {
            @unlink($lockPath);
            $this->info("Deleted installation lock: {$lockPath}");
        }

        $this->call('config:clear');
        $this->call('cache:clear');

        $this->info('Installer successfully reset! You can now visit /install in your browser.');

        return Command::SUCCESS;
    }
}
