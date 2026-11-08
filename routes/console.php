<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\MarketingService;
use App\Models\MarketingCampaign;
use App\Models\AutomatedRule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('marketing:run-rules', function (MarketingService $service) {
    $this->info('Starting automated marketing rules processing...');
    $service->ensureDefaults();

    $rules = AutomatedRule::where('is_active', true)->get();
    $count = 0;

    foreach ($rules as $rule) {
        $this->line("Executing rule: {$rule->name} ({$rule->event_type})");
        $result = $service->executeAutomatedRule($rule);
        $this->info(" -> Sent: {$result['total_sent']} notifications to {$result['total_targeted']} targeted customers.");
        $count++;
    }

    $this->info("Completed processing {$count} automated rules.");
})->purpose('Execute active automated marketing notification rules');

Artisan::command('marketing:send-scheduled', function (MarketingService $service) {
    $this->info('Checking for scheduled promotional campaigns...');
    $service->ensureDefaults();

    $campaigns = MarketingCampaign::where('status', 'scheduled')
        ->whereNotNull('start_date')
        ->where('start_date', '<=', now())
        ->get();

    $count = 0;
    foreach ($campaigns as $campaign) {
        $this->line("Dispatching scheduled campaign: {$campaign->title}");
        $result = $service->sendCampaign($campaign);
        $this->info(" -> Sent: {$result['total_sent']} notifications to {$result['total_targeted']} customers.");
        $count++;
    }

    $this->info("Processed {$count} scheduled campaigns.");
})->purpose('Send scheduled marketing campaigns whose start time has arrived');

// Scheduled Jobs
Schedule::command('marketing:run-rules')->dailyAt('09:00');
Schedule::command('marketing:send-scheduled')->everyFifteenMinutes();
