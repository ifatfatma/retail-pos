<?php

namespace App\Console\Commands;

use App\Services\InstallationService;
use Illuminate\Console\Command;

class MarkInstalledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-installed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks the application as installed by writing the secure storage/installed marker';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $success = InstallationService::markAsInstalled([
            'admin_email' => 'admin@retailpos.local',
            'installed_at' => now()->toIso8601String(),
            'notes' => 'Generated via artisan command',
        ]);

        if ($success) {
            $this->info('Application has been successfully marked as installed.');
            $this->info('Path: ' . InstallationService::installedFilePath());
            return Command::SUCCESS;
        }

        $this->error('Failed to create installation marker.');
        return Command::FAILURE;
    }
}
