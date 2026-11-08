<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'short_name',
        'short_name_ar',
        'allow_decimal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'allow_decimal' => 'boolean',
            'status' => 'boolean',
        ];
    }
}
