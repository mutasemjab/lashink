<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'base_salary', 'commission_amount', 'bonus',
        'unpaid_leave_deduction', 'advance_deduction', 'other_deductions', 'net_salary',
        'payment_status', 'paid_at', 'payment_method', 'notes',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
