<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount_amount',
        'min_order_amount',
        'buy_qty',
        'get_qty',
        'get_discount_percent',
        'tier_rules',
        'channel',
        'applies_to',
        'usage_limit_total',
        'usage_limit_per_customer',
        'times_used',
        'is_first_order_only',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'buy_qty' => 'integer',
        'get_qty' => 'integer',
        'get_discount_percent' => 'decimal:2',
        'tier_rules' => 'array',
        'usage_limit_total' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'times_used' => 'integer',
        'is_first_order_only' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationships
    public function restrictions(): HasMany
    {
        return $this->hasMany(CouponRestriction::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CouponAuditLog::class)->latest('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValidChannel($query, string $channel)
    {
        return $query->where(function ($q) use ($channel) {
            $q->where('channel', 'all')
              ->orWhere('channel', $channel);
        });
    }

    // Helper checks
    public function isExpired(): bool
    {
        if ($this->end_date && now()->isAfter($this->end_date)) {
            return true;
        }
        return false;
    }

    public function hasStarted(): bool
    {
        if ($this->start_date && now()->isBefore($this->start_date)) {
            return false;
        }
        return true;
    }

    public function hasReachedTotalLimit(): bool
    {
        if ($this->usage_limit_total !== null && $this->times_used >= $this->usage_limit_total) {
            return true;
        }
        return false;
    }
}

