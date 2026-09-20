@extends('admin.layouts.app')
@section('title', __('messages.payroll'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.payroll') }} — {{ $run->periodLabel() }}</h1></div>
    <div class="d-flex gap-2">
        @can('payroll-edit')
        @if($run->status === 'draft')
        <form action="{{ route('admin.payroll.finalize', $run->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_finalize') }}')">
            @csrf
            <button type="submit" class="btn-primary-sm"><i class="bi bi-lock"></i> {{ __('messages.finalize_payroll') }}</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('admin.payroll.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.employees') }}</th><th>{{ __('messages.field_base_salary') }}</th>
                        <th>{{ __('messages.commission') }}</th><th>{{ __('messages.leave_deduction') }}</th>
                        <th>{{ __('messages.advance_deduction') }}</th><th>{{ __('messages.net_salary') }}</th>
                        <th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->items as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->employee->name ?? '—' }}</td>
                        <td>{{ number_format($item->base_salary, 2) }}</td>
                        <td class="text-success">+{{ number_format($item->commission_amount, 2) }}</td>
                        <td class="text-danger">-{{ number_format($item->unpaid_leave_deduction, 2) }}</td>
                        <td class="text-danger">-{{ number_format($item->advance_deduction, 2) }}</td>
                        <td class="fw-bold">{{ number_format($item->net_salary, 2) }} {{ $item->currency->code ?? __('Currency') }}</td>
                        <td>
                            @if($item->payment_status === 'paid')<span class="pill pill-success">{{ __('messages.ps_paid') }}</span>
                            @else<span class="pill pill-warning">{{ __('messages.ps_unpaid') }}</span>@endif
                        </td>
                        <td>
                            @can('payroll-edit')
                            @if($run->status === 'finalized' && $item->payment_status !== 'paid')
                            <button type="button" class="btn-icon-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $item->id }}"><i class="bi bi-cash"></i></button>
                            <div class="modal fade" id="payModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.payroll.mark-paid', $item->id) }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header"><h5 class="modal-title">{{ __('messages.mark_paid') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <label class="form-label">{{ __('messages.field_payment_method') }}</label>
                                            <select name="payment_method" class="form-select">
                                                <option value="cash">{{ __('messages.pm_cash') }}</option>
                                                <option value="card">{{ __('messages.pm_card') }}</option>
                                                <option value="transfer">{{ __('messages.pm_transfer') }}</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.mark_paid') }}</button></div>
                                    </form>
                                </div>
                            </div>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel-card-body border-top text-end fw-bold">
        {{ __('messages.field_total') }}:
        @forelse($run->items->groupBy(fn($i) => $i->currency->code ?? __('Currency')) as $code => $group)
            {{ number_format($group->sum('net_salary'), 2) }} {{ $code }}@if(!$loop->last), @endif
        @empty
            0.00
        @endforelse
    </div>
</div>

@endsection
