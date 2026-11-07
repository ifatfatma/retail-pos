<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'address',
        'city',
        'state',
        'pincode',
        'birthday',
        'loyalty_tier',
        'last_active_at',
        'gst_number',
        'total_spent',
        'total_orders',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'status' => 'boolean',
        'birthday' => 'date',
        'last_active_at' => 'datetime',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function notifications()
    {
        return $this->hasMany(CustomerNotification::class)->latest('id');
    }

    public function notificationPreference()
    {
        return $this->hasOne(CustomerNotificationPreference::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class)->latest('id');
    }
}
