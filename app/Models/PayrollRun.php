<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'period_month', 'period_year', 'status', 'generated_at',
        'finalized_at', 'generated_by', 'total_net',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function generator()
    {
        return $this->belongsTo(Admin::class, 'generated_by');
    }

    public function periodLabel(): string
    {
        return \Carbon\Carbon::createFromDate($this->period_year, $this->period_month, 1)->translatedFormat('F Y');
    }
}
