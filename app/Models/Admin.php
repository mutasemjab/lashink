<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles,HasApiTokens; // Add this line


    protected $table = 'admins';
    protected string $guard_name = 'admin';

    protected $fillable = [
        'name', 'email', 'username', 'password',
        'phone', 'national_id', 'hire_date', 'base_salary', 'commission_percent',
        'employment_status', 'address', 'photo', 'notes',
    ];

    protected $casts = [
        'is_super'   => 'boolean',
        'hire_date'  => 'date',
        'base_salary' => 'decimal:2',
        'commission_percent' => 'decimal:2',
    ];
    protected $hidden = ['password'];

    public function qualifiedServices()
    {
        return $this->belongsToMany(Service::class, 'service_employee', 'admin_id', 'service_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'employee_id');
    }

}
