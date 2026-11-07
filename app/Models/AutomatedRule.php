<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomatedRule extends Model
{
    protected $fillable = [
        'name',
        'event_type',
        'description',
        'segment_id',
        'template_id',
        'channels',
        'conditions',
        'frequency',
        'run_time',
        'is_active',
        'last_run_at',
        'total_runs',
        'total_triggers',
    ];

    protected $casts = [
        'channels' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'total_runs' => 'integer',
        'total_triggers' => 'integer',
        'last_run_at' => 'datetime',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(MarketingSegment::class, 'segment_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'automated_rule_id');
    }
}
