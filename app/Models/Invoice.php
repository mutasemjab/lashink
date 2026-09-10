<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'client_id', 'appointment_id', 'employee_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total', 'paid_amount',
        'payment_status', 'status', 'notes', 'created_by', 'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'subtotal'  => 'decimal:2',
        'total'     => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function remainingAmount(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    public static function nextInvoiceNumber(): string
    {
        $last = static::orderByDesc('id')->first();
        $seq  = $last ? ((int) substr($last->invoice_number, 4)) + 1 : 1;

        return 'INV-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
