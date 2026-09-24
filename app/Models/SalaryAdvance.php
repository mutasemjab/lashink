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
        $count       = max((int) $this->installments_count, 1);
        $installment = round($this->amount / $count, 2);

        // The last installment takes whatever is left, so rounding never leaves a stray remainder.
        $paidCount = $installment > 0 ? (int) round($this->repaid_amount / $installment) : 0;
        if ($paidCount >= $count - 1) {
            return round($this->remainingAmount(), 2);
        }

        return round(min($installment, $this->remainingAmount()), 2);
    }
}
