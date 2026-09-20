<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'supplier_id', 'currency_id', 'name', 'unit', 'quantity_in_stock',
        'min_stock_alert', 'cost_price', 'sale_price', 'is_sellable', 'is_active', 'image',
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
        'min_stock_alert'   => 'decimal:2',
        'cost_price'        => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'is_sellable'       => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock_alert > 0 && $this->quantity_in_stock <= $this->min_stock_alert;
    }

    /**
     * Apply a stock movement and persist the resulting balance on the product.
     */
    public function adjustStock(string $type, float $quantity, string $reason, ?string $note = null, $reference = null): StockMovement
    {
        $delta = $type === 'out' ? -$quantity : $quantity;
        $this->increment('quantity_in_stock', $delta);

        return $this->movements()->create([
            'type'           => $type,
            'quantity'       => $quantity,
            'reason'         => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'note'           => $note,
            'created_by'     => auth('admin')->id(),
            'movement_date'  => now()->toDateString(),
        ]);
    }
}
