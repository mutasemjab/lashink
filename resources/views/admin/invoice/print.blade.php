<?php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; ?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }} — LashInk</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; max-width: 700px; margin: 30px auto; color: #111827; }
        h1 { font-size: 1.4rem; margin-bottom: 0; }
        .muted { color: #6b7280; font-size: .85rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: {{ $dir === 'rtl' ? 'right' : 'left' }}; font-size: .9rem; }
        .totals { margin-top: 16px; width: 280px; margin-{{ $dir === 'rtl' ? 'right' : 'left' }}: auto; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; }
        .grand { font-weight: 700; font-size: 1.1rem; border-top: 2px solid #111827; margin-top: 6px; padding-top: 6px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111827; padding-bottom: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <img src="{{ asset('assets/admin/img/logo.png') }}" alt="LashInk" style="height:56px;width:auto;display:block;margin-bottom:4px">
            <div class="muted">{{ __('messages.invoice_number') }}: {{ $invoice->invoice_number }}</div>
        </div>
        <div class="muted" style="text-align:{{ $dir === 'rtl' ? 'left' : 'right' }}">
            {{ __('messages.field_date') }}: {{ $invoice->issued_at->format('Y-m-d H:i') }}<br>
            {{ __('messages.clients') }}: {{ $invoice->client->name ?? '' }}<br>
            {{ $invoice->client->phone ?? '' }}
        </div>
    </div>

    <table>
        <thead><tr><th>{{ __('messages.field_description') }}</th><th>{{ __('messages.field_quantity') }}</th><th>{{ __('messages.field_price') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr><td>{{ $item->description }}</td><td>{{ number_format($item->quantity, 2) }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->total, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>{{ __('messages.subtotal') }}</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
        <div><span>{{ __('messages.field_discount') }}</span><span>-{{ number_format($invoice->discount_amount, 2) }}</span></div>
        <div><span>{{ __('messages.tax') }}</span><span>{{ number_format($invoice->tax_amount, 2) }}</span></div>
        <div class="grand"><span>{{ __('messages.field_total') }}</span><span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency->code ?? __('Currency') }}</span></div>
    </div>

    <p class="muted" style="margin-top:40px;text-align:center;">{{ __('messages.thank_you_note') }}</p>

    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button onclick="window.print()">{{ __('messages.print') }}</button>
    </div>
</body>
</html>
