<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'phone', 'phone2', 'gender', 'birthdate',
        'address', 'source', 'is_blocked', 'notes',
    ];

    protected $casts = [
        'birthdate'  => 'date',
        'is_blocked' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
