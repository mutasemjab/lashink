<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'days',
        'reason', 'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
