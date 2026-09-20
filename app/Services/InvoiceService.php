<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create an invoice with its line items, computing commissions and
     * deducting stock (direct product sales + service recipe consumption).
     *
     * $lines: array of ['type' => 'service'|'product', 'id' => int, 'quantity' => float, 'employee_id' => ?int]
     */
    public function create(array $attributes, array $lines): Invoice
    {
        return DB::transaction(function () use ($attributes, $lines) {
            $invoice = Invoice::create([
                'invoice_number'  => Invoice::nextInvoiceNumber(),
                'client_id'       => $attributes['client_id'],
                'appointment_id'  => $attributes['appointment_id'] ?? null,
                'employee_id'     => $attributes['employee_id'] ?? null,
                'currency_id'     => $attributes['currency_id'] ?? \App\Models\Currency::default()?->id,
                'discount_amount' => $attributes['discount_amount'] ?? 0,
                'tax_amount'      => $attributes['tax_amount'] ?? 0,
                'notes'           => $attributes['notes'] ?? null,
                'created_by'      => auth('admin')->id(),
                'issued_at'       => now(),
                'status'          => 'issued',
                'payment_status'  => 'unpaid',
                'subtotal'        => 0,
                'total'           => 0,
            ]);

            $subtotal = 0;

            foreach ($lines as $line) {
                $quantity = (float) ($line['quantity'] ?? 1);
                $employee = !empty($line['employee_id']) ? Admin::find($line['employee_id']) : null;

                if ($line['type'] === 'service') {
                    $service   = Service::findOrFail($line['id']);
                    $unitPrice = isset($line['unit_price']) ? (float) $line['unit_price'] : $service->price;
                    $lineTotal = $unitPrice * $quantity;
                    $commission = $service->commissionFor($lineTotal, $employee);

                    InvoiceItem::create([
                        'invoice_id'         => $invoice->id,
                        'item_type'          => 'service',
                        'service_id'         => $service->id,
                        'employee_id'        => $employee?->id,
                        'description'        => $service->name,
                        'quantity'           => $quantity,
                        'unit_price'         => $unitPrice,
                        'total'              => $lineTotal,
                        'commission_amount'  => $commission,
                    ]);

                    // Deduct recipe products consumed by this service, if any.
                    foreach ($service->recipeProducts as $product) {
                        $consume = (float) $product->pivot->qty_consumed * $quantity;
                        $product->adjustStock('out', $consume, 'service_consumption', $service->name, $invoice);
                    }

                    $subtotal += $lineTotal;
                } else {
                    $product   = Product::findOrFail($line['id']);
                    $unitPrice = isset($line['unit_price']) ? (float) $line['unit_price'] : ($product->sale_price ?? 0);
                    $lineTotal = $unitPrice * $quantity;

                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'item_type'   => 'product',
                        'product_id'  => $product->id,
                        'employee_id' => $employee?->id,
                        'description' => $product->name,
                        'quantity'    => $quantity,
                        'unit_price'  => $unitPrice,
                        'total'       => $lineTotal,
                    ]);

                    $product->adjustStock('out', $quantity, 'sale', null, $invoice);

                    $subtotal += $lineTotal;
                }
            }

            $total = $subtotal - $invoice->discount_amount + $invoice->tax_amount;

            $invoice->update([
                'subtotal' => $subtotal,
                'total'    => max($total, 0),
            ]);

            return $invoice->fresh('items');
        });
    }

    public function recordPayment(Invoice $invoice, float $amount, string $method, ?string $note = null): void
    {
        $invoice->payments()->create([
            'amount'      => $amount,
            'method'      => $method,
            'paid_at'     => now(),
            'received_by' => auth('admin')->id(),
            'note'        => $note,
        ]);

        $paid = (float) $invoice->payments()->sum('amount');
        $status = $paid <= 0 ? 'unpaid' : ($paid >= (float) $invoice->total ? 'paid' : 'partial');

        $invoice->update(['paid_amount' => $paid, 'payment_status' => $status]);
    }
}
