<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'automated_rule_id',
        'customer_id',
        'channel',
        'recipient',
        'title',
        'content',
        'action_url',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'is_test_mode',
    ];

    protected $casts = [
        'is_test_mode' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomatedRule::class, 'automated_rule_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
