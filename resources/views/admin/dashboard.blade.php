@extends('admin.layouts.app')

@section('title', __('messages.page_dashboard'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.page_dashboard') }}</h1>
        <p class="page-sub">{{ __('messages.welcome_back') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-calendar2-week"></i></div>
            <div class="stat-value">{{ $todayAppointments->count() }}</div>
            <div class="stat-label">{{ __('messages.today_appointments') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-cash-coin"></i></div>
            <div class="stat-value">
                @forelse($todayRevenueByCurrency as $code => $sum)
                    <div>{{ number_format($sum, 2) }} {{ $code }}</div>
                @empty
                    0.00
                @endforelse
            </div>
            <div class="stat-label">{{ __('messages.today_revenue') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f9ff;color:#0284c7"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-value">
                @forelse($monthProfitByCurrency as $code => $sum)
                    <div>{{ number_format($sum, 2) }} {{ $code }}</div>
                @empty
                    0.00
                @endforelse
            </div>
            <div class="stat-label">{{ __('messages.month_profit') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef9c3;color:#a16207"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $lowStockCount }}</div>
            <div class="stat-label">{{ __('messages.low_stock') }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="panel-card">
            <div class="panel-card-header d-flex align-items-center justify-content-between">
                <h2 class="panel-card-title"><i class="bi bi-calendar2-week"></i> {{ __('messages.today_appointments') }}</h2>
                @can('appointment-table')
                <a href="{{ route('admin.appointment.index') }}" class="btn-outline-sm">{{ __('messages.nav_appointments') }}</a>
                @endcan
            </div>
            <div class="panel-card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('messages.field_date') }}</th><th>{{ __('messages.clients') }}</th><th>{{ __('messages.employees') }}</th><th>{{ __('messages.Status') }}</th></tr></thead>
                    <tbody>
                        @forelse($todayAppointments as $a)
                        <tr>
                            <td>{{ $a->start_at->format('H:i') }}</td>
                            <td>{{ $a->client->name ?? '—' }}</td>
                            <td>{{ $a->employee->name ?? '—' }}</td>
                            <td>{{ __('messages.appt_status_' . $a->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="panel-card mb-4">
            <div class="panel-card-header"><h2 class="panel-card-title"><i class="bi bi-bell"></i> {{ __('messages.pending_requests') }}</h2></div>
            <div class="panel-card-body">
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ __('messages.leave_requests') }}</span>
                    <span class="pill pill-warning">{{ $pendingLeaves }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>{{ __('messages.salary_advances') }}</span>
                    <span class="pill pill-warning">{{ $pendingAdvances }}</span>
                </div>
            </div>
        </div>

        @if($topEmployee)
        <div class="panel-card">
            <div class="panel-card-header"><h2 class="panel-card-title"><i class="bi bi-star"></i> {{ __('messages.top_employee_month') }}</h2></div>
            <div class="panel-card-body">
                <div class="fw-bold fs-5">{{ $topEmployee->employee->name ?? '—' }}</div>
                <div class="text-muted">{{ number_format($topEmployee->revenue, 2) }} {{ __('Currency') }}</div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
