@extends('admin.layouts.app')
@section('title', __('messages.nav_reports'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.nav_reports') }}</h1></div>
</div>

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label">{{ __('messages.from_date') }}</label>
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">{{ __('messages.to_date') }}</label>
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i> {{ __('messages.filter') }}</button></div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('admin.report.export-revenue', request()->query()) }}" class="btn-outline-sm"><i class="bi bi-file-earmark-excel"></i> {{ __('messages.revenue') }}</a>
                <a href="{{ route('admin.report.export-expenses', request()->query()) }}" class="btn-outline-sm"><i class="bi bi-file-earmark-excel"></i> {{ __('messages.expenses') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value">{{ number_format($revenue, 2) }}</div>
            <div class="stat-label">{{ __('messages.revenue') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">{{ number_format($expenses, 2) }}</div>
            <div class="stat-label">{{ __('messages.expenses') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;color:#2563eb"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-value">{{ number_format($profit, 2) }}</div>
            <div class="stat-label">{{ __('messages.net_profit') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef9c3;color:#a16207"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $lowStock->count() }}</div>
            <div class="stat-label">{{ __('messages.low_stock') }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="panel-card mb-4">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.top_services') }}</h2></div>
            <div class="panel-card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('messages.services') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
                    <tbody>
                        @forelse($topServices as $s)
                        <tr><td>{{ $s->description }}</td><td>{{ number_format($s->revenue, 2) }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('messages.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.expenses_by_category') }}</h2></div>
            <div class="panel-card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('messages.field_category') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
                    <tbody>
                        @forelse($expenseByCategory as $ec)
                        <tr><td>{{ $ec->category->name ?? '—' }}</td><td>{{ number_format($ec->total, 2) }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('messages.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="panel-card mb-4">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.top_employees') }}</h2></div>
            <div class="panel-card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('messages.employees') }}</th><th>{{ __('messages.revenue') }}</th><th>{{ __('messages.commission') }}</th></tr></thead>
                    <tbody>
                        @forelse($topEmployees as $e)
                        <tr><td>{{ $e->employee->name ?? '—' }}</td><td>{{ number_format($e->revenue, 2) }}</td><td>{{ number_format($e->commission, 2) }}</td></tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-card-header"><h2 class="panel-card-title">{{ __('messages.low_stock') }}</h2></div>
            <div class="panel-card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('messages.products') }}</th><th>{{ __('messages.field_quantity') }}</th></tr></thead>
                    <tbody>
                        @forelse($lowStock as $p)
                        <tr><td>{{ $p->name }}</td><td class="text-danger">{{ number_format($p->quantity_in_stock, 2) }} {{ $p->unit }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('messages.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
