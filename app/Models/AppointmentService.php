<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    protected $fillable = ['appointment_id', 'service_id', 'employee_id', 'price', 'duration_minutes'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
}
