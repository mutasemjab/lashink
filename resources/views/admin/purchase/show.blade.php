@extends('admin.layouts.app')
@section('title', __('messages.purchases'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.purchases') }} #{{ $purchase->id }}</h1><p class="page-sub">{{ $purchase->purchase_date->format('Y-m-d') }} — {{ $purchase->supplier->name ?? '—' }}</p></div>
    <a href="{{ route('admin.purchase.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.products') }}</th><th>{{ __('messages.field_quantity') }}</th><th>{{ __('messages.field_cost') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
                <tbody>
                    @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_cost, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel-card-body border-top text-end">
        <strong>{{ __('messages.field_total') }}: {{ number_format($purchase->total, 2) }} {{ __('Currency') }}</strong>
    </div>
</div>
@if($purchase->notes)
<div class="panel-card mt-3"><div class="panel-card-body">{{ $purchase->notes }}</div></div>
@endif
@endsection
