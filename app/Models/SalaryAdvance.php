<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'employee_id', 'currency_id', 'amount', 'request_date', 'reason', 'status',
        'approved_by', 'approved_at', 'repayment_type', 'installments_count',
        'repaid_amount', 'is_settled',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at'  => 'datetime',
        'is_settled'   => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function remainingAmount(): float
    {
        return max((float) $this->amount - (float) $this->repaid_amount, 0);
    }

    public function installmentAmount(): float
    {
        $installment = $this->installments_count > 0 ? $this->amount / $this->installments_count : $this->amount;
        return round(min($installment, $this->remainingAmount()), 2);
    }
}
