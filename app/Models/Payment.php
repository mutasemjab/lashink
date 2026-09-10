<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'amount', 'method', 'paid_at', 'received_by', 'note'];

    protected $casts = ['paid_at' => 'datetime'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Admin::class, 'received_by');
    }
}
