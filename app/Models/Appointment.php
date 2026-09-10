<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'client_id', 'employee_id', 'status', 'start_at', 'end_at',
        'notes', 'cancelled_reason', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function services()
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function totalPrice(): float
    {
        return (float) $this->services->sum('price');
    }
}
