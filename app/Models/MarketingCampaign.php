<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'banner_url',
        'image_url',
        'campaign_type',
        'target_type',
        'segment_id',
        'channels',
        'discount_type',
        'discount_value',
        'coupon_id',
        'target_product_ids',
        'target_category_ids',
        'target_brand_ids',
        'start_date',
        'end_date',
        'status',
        'total_targeted',
        'total_sent',
        'total_delivered',
        'total_opened',
        'total_clicked',
        'total_conversions',
        'revenue_generated',
        'created_by',
    ];

    protected $casts = [
        'channels' => 'array',
        'target_product_ids' => 'array',
        'target_category_ids' => 'array',
        'target_brand_ids' => 'array',
        'discount_value' => 'decimal:2',
        'revenue_generated' => 'decimal:2',
        'total_targeted' => 'integer',
        'total_sent' => 'integer',
        'total_delivered' => 'integer',
        'total_opened' => 'integer',
        'total_clicked' => 'integer',
        'total_conversions' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(MarketingSegment::class, 'segment_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'campaign_id');
    }

    public function customerNotifications(): HasMany
    {
        return $this->hasMany(CustomerNotification::class, 'campaign_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('start_date');
    }

    public function isCurrentlyLive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->start_date && now()->isBefore($this->start_date)) return false;
        if ($this->end_date && now()->isAfter($this->end_date)) return false;
        return true;
    }
}
