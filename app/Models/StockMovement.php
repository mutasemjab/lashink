<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'type', 'quantity', 'reason', 'reference_type',
        'reference_id', 'note', 'created_by', 'movement_date',
    ];

    protected $casts = ['movement_date' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
