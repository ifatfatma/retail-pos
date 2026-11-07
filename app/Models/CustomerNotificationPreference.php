<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotificationPreference extends Model
{
    protected $fillable = [
        'customer_id',
        'promotional_enabled',
        'channel_in_app',
        'channel_email',
        'channel_sms',
        'channel_whatsapp',
        'channel_push',
        'category_flash_sales',
        'category_back_in_stock',
        'category_rewards',
        'category_new_arrivals',
        'category_festivals',
        'unsubscribed_at',
    ];

    protected $casts = [
        'promotional_enabled' => 'boolean',
        'channel_in_app' => 'boolean',
        'channel_email' => 'boolean',
        'channel_sms' => 'boolean',
        'channel_whatsapp' => 'boolean',
        'channel_push' => 'boolean',
        'category_flash_sales' => 'boolean',
        'category_back_in_stock' => 'boolean',
        'category_rewards' => 'boolean',
        'category_new_arrivals' => 'boolean',
        'category_festivals' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Check if customer allows a specific channel and category
     */
    public function allows(string $channel, ?string $category = null): bool
    {
        if (!$this->promotional_enabled) {
            return false;
        }

        $channelField = 'channel_' . $channel;
        if (isset($this->$channelField) && !$this->$channelField) {
            return false;
        }

        if ($category) {
            $catField = 'category_' . $category;
            if (isset($this->$catField) && !$this->$catField) {
                return false;
            }
        }

        return true;
    }
}
