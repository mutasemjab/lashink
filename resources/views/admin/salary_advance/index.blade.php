@extends('admin.layouts.app')
@section('title', __('messages.salary_advances'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.salary_advances') }}</h1></div>
    @can('advance-add')
    <a href="{{ route('admin.salary-advance.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.employees') }}</th><th>{{ __('messages.field_amount') }}</th><th>{{ __('messages.field_date') }}</th><th>{{ __('messages.repayment') }}</th><th>{{ __('messages.remaining_amount') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($advances as $a)
                    <tr>
                        <td class="fw-semibold">{{ $a->employee->name ?? '—' }}</td>
                        <td>{{ number_format($a->amount, 2) }} {{ $a->currency->code ?? __('Currency') }}</td>
                        <td>{{ $a->request_date->format('Y-m-d') }}</td>
                        <td>{{ $a->repayment_type === 'installments' ? $a->installments_count . '×' : __('messages.repayment_single') }}</td>
                        <td>{{ number_format($a->remainingAmount(), 2) }}</td>
                        <td>
                            @if($a->status==='approved')<span class="pill pill-success">{{ $a->is_settled ? __('messages.settled') : __('messages.approved') }}</span>
                            @elseif($a->status==='rejected')<span class="pill pill-danger">{{ __('messages.rejected') }}</span>
                            @else<span class="pill pill-warning">{{ __('messages.pending') }}</span>@endif
                        </td>
                        <td>
                            @can('advance-edit')
                            @if($a->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.salary-advance.approve', $a->id) }}" method="POST">@csrf<button type="submit" class="btn-icon-sm" style="color:#16a34a" title="{{ __('messages.approve') }}"><i class="bi bi-check-lg"></i></button></form>
                                <form action="{{ route('admin.salary-advance.reject', $a->id) }}" method="POST">@csrf<button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.reject') }}"><i class="bi bi-x-lg"></i></button></form>
                            </div>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($advances->hasPages())<div class="panel-card-body border-top pt-3">{{ $advances->links() }}</div>@endif
</div>

@endsection
