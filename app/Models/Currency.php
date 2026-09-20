<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    protected $fillable = ['code', 'symbol', 'is_default', 'is_active'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public static function default(): self
    {
        return Cache::rememberForever('currency.default', fn() => static::where('is_default', true)->first() ?? static::first());
    }

    public static function forgetDefaultCache(): void
    {
        Cache::forget('currency.default');
    }
}
