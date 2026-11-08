<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MarketingSegment extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'criteria',
        'customer_count',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'customer_count' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingCampaign::class, 'segment_id');
    }

    public function automatedRules(): HasMany
    {
        return $this->hasMany(AutomatedRule::class, 'segment_id');
    }

    /**
     * Build customer query matching segment criteria
     */
    public function getCustomersQuery(): Builder
    {
        $query = Customer::query()->where('status', true);
        $criteria = $this->criteria ?? [];

        // Spending bounds
        if (!empty($criteria['min_spent'])) {
            $query->where('total_spent', '>=', (float)$criteria['min_spent']);
        }
        if (!empty($criteria['max_spent'])) {
            $query->where('total_spent', '<=', (float)$criteria['max_spent']);
        }

        // Orders count bounds
        if (isset($criteria['min_orders']) && $criteria['min_orders'] !== '') {
            $query->where('total_orders', '>=', (int)$criteria['min_orders']);
        }
        if (isset($criteria['max_orders']) && $criteria['max_orders'] !== '') {
            $query->where('total_orders', '<=', (int)$criteria['max_orders']);
        }

        // Loyalty tiers
        if (!empty($criteria['loyalty_tiers']) && is_array($criteria['loyalty_tiers'])) {
            $query->whereIn('loyalty_tier', $criteria['loyalty_tiers']);
        }

        // Inactivity (Days without order or login)
        if (!empty($criteria['inactivity_days'])) {
            $cutoffDate = now()->subDays((int)$criteria['inactivity_days']);
            $query->where(function ($q) use ($cutoffDate) {
                $q->whereNull('last_active_at')
                  ->orWhere('last_active_at', '<=', $cutoffDate);
            });
        }

        // Cities
        if (!empty($criteria['cities']) && is_array($criteria['cities'])) {
            $query->whereIn('city', $criteria['cities']);
        }

        // Birthday Month
        if (!empty($criteria['birthday_month'])) {
            $query->whereRaw('MONTH(birthday) = ?', [(int)$criteria['birthday_month']]);
        }

        return $query;
    }

    /**
     * Recalculate customer count
     */
    public function recalculateCount(): int
    {
        $count = $this->getCustomersQuery()->count();
        $this->update(['customer_count' => $count]);
        return $count;
    }
}
