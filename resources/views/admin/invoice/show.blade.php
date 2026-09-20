@extends('admin.layouts.app')
@section('title', $invoice->invoice_number)

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ $invoice->invoice_number }}</h1><p class="page-sub">{{ $invoice->client->name ?? '' }} — {{ $invoice->issued_at->format('Y-m-d H:i') }}</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.invoice.print', $invoice->id) }}" target="_blank" class="btn-outline-sm"><i class="bi bi-printer"></i> {{ __('messages.print') }}</a>
        <a href="{{ route('admin.invoice.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="panel-card">
            <div class="panel-card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr><th>{{ __('messages.field_description') }}</th><th>{{ __('messages.field_quantity') }}</th><th>{{ __('messages.field_price') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-card-body border-top">
                <div class="d-flex justify-content-end">
                    <div style="min-width:260px">
                        <div class="d-flex justify-content-between"><span>{{ __('messages.subtotal') }}</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
                        <div class="d-flex justify-content-between"><span>{{ __('messages.field_discount') }}</span><span>-{{ number_format($invoice->discount_amount, 2) }}</span></div>
                        <div class="d-flex justify-content-between"><span>{{ __('messages.tax') }}</span><span>{{ number_format($invoice->tax_amount, 2) }}</span></div>
                        <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mt-2"><span>{{ __('messages.field_total') }}</span><span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency->code ?? __('Currency') }}</span></div>
                        <div class="d-flex justify-content-between text-success"><span>{{ __('messages.paid_amount') }}</span><span>{{ number_format($invoice->paid_amount, 2) }}</span></div>
                        <div class="d-flex justify-content-between text-danger"><span>{{ __('messages.remaining_amount') }}</span><span>{{ number_format($invoice->remainingAmount(), 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="panel-card mt-3"><div class="panel-card-body">{{ $invoice->notes }}</div></div>
        @endif
    </div>

    <div class="col-12 col-xl-4">
        <div class="panel-card mb-3">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.payments') }}</h2></div>
            <div class="panel-card-body">
                @forelse($invoice->payments as $pay)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ number_format($pay->amount, 2) }} {{ $invoice->currency->code ?? __('Currency') }} — {{ __('messages.pm_' . $pay->method) }}</span>
                    <span class="text-muted small">{{ $pay->paid_at->format('Y-m-d') }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">{{ __('messages.no_records') }}</p>
                @endforelse
            </div>
            @can('invoice-edit')
            @if($invoice->remainingAmount() > 0 && $invoice->status !== 'cancelled')
            <div class="panel-card-body border-top">
                <form action="{{ route('admin.invoice.payment', $invoice->id) }}" method="POST">
                    @csrf
                    <label class="form-label">{{ __('messages.field_amount') }}</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $invoice->remainingAmount() }}" name="amount" value="{{ $invoice->remainingAmount() }}" class="form-control mb-2" required>
                    <label class="form-label">{{ __('messages.field_payment_method') }}</label>
                    <select name="method" class="form-select mb-2">
                        <option value="cash">{{ __('messages.pm_cash') }}</option>
                        <option value="card">{{ __('messages.pm_card') }}</option>
                        <option value="transfer">{{ __('messages.pm_transfer') }}</option>
                    </select>
                    <button type="submit" class="btn-primary-sm w-100">{{ __('messages.add_payment') }}</button>
                </form>
            </div>
            @endif
            @endcan
        </div>

        @can('invoice-edit')
        @if($invoice->status !== 'cancelled')
        <form action="{{ route('admin.invoice.cancel', $invoice->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
            @csrf
            <button type="submit" class="btn-outline-sm text-danger w-100">{{ __('messages.cancel_invoice') }}</button>
        </form>
        @endif
        @endcan
    </div>
</div>

@endsection
