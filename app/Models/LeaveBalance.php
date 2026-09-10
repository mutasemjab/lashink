<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = ['employee_id', 'leave_type_id', 'year', 'allocated_days', 'used_days'];

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function remainingDays(): float
    {
        return max((float) $this->allocated_days - (float) $this->used_days, 0);
    }
}
