<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id', 'currency_id', 'name', 'duration_minutes', 'price',
        'commission_type', 'commission_value', 'is_active',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'commission_value'  => 'decimal:2',
        'is_active'         => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function qualifiedEmployees()
    {
        return $this->belongsToMany(Admin::class, 'service_employee', 'service_id', 'admin_id');
    }

    public function recipeProducts()
    {
        return $this->belongsToMany(Product::class, 'service_product', 'service_id', 'product_id')
            ->withPivot('qty_consumed');
    }

    /**
     * Commission amount for a given line price, falling back to the
     * employee's default commission percent when the service has none set.
     */
    public function commissionFor(float $lineTotal, ?Admin $employee = null): float
    {
        if ($this->commission_type === 'fixed' && $this->commission_value > 0) {
            return (float) $this->commission_value;
        }

        $percent = (float) $this->commission_value;
        if ($percent <= 0 && $employee) {
            $percent = (float) $employee->commission_percent;
        }

        return round($lineTotal * ($percent / 100), 2);
    }
}
