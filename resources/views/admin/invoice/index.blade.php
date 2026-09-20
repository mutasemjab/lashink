@extends('admin.layouts.app')
@section('title', __('messages.invoices'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.invoices') }}</h1></div>
    @can('invoice-add')
    <a href="{{ route('admin.invoice.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('messages.Search') }}...">
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select form-select-sm no-select2">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="unpaid" {{ request('payment_status')==='unpaid'?'selected':'' }}>{{ __('messages.ps_unpaid') }}</option>
                    <option value="partial" {{ request('payment_status')==='partial'?'selected':'' }}>{{ __('messages.ps_partial') }}</option>
                    <option value="paid" {{ request('payment_status')==='paid'?'selected':'' }}>{{ __('messages.ps_paid') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i></button></div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.invoice_number') }}</th><th>{{ __('messages.clients') }}</th><th>{{ __('messages.field_date') }}</th><th>{{ __('messages.field_total') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->client->name ?? '—' }}</td>
                        <td>{{ $inv->issued_at->format('Y-m-d') }}</td>
                        <td>{{ number_format($inv->total, 2) }} {{ $inv->currency->code ?? __('Currency') }}</td>
                        <td>
                            @if($inv->payment_status === 'paid')<span class="pill pill-success">{{ __('messages.ps_paid') }}</span>
                            @elseif($inv->payment_status === 'partial')<span class="pill pill-warning">{{ __('messages.ps_partial') }}</span>
                            @else<span class="pill pill-danger">{{ __('messages.ps_unpaid') }}</span>@endif
                            @if($inv->status === 'cancelled')<span class="pill pill-neutral">{{ __('messages.appt_status_cancelled') }}</span>@endif
                        </td>
                        <td><a href="{{ route('admin.invoice.show', $inv->id) }}" class="btn-icon-sm"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())<div class="panel-card-body border-top pt-3">{{ $invoices->links() }}</div>@endif
</div>

@endsection
