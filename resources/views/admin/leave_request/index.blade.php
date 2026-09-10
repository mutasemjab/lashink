@extends('admin.layouts.app')
@section('title', __('messages.leave_requests'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.leave_requests') }}</h1></div>
    @can('leave-add')
    <a href="{{ route('admin.leave-request.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm no-select2">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending" {{ request('status')==='pending'?'selected':'' }}>{{ __('messages.pending') }}</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>{{ __('messages.approved') }}</option>
                    <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>{{ __('messages.rejected') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.employees') }}</option>
                    @foreach($employees as $emp)<option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i> {{ __('messages.filter') }}</button></div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.employees') }}</th><th>{{ __('messages.leave_type') }}</th><th>{{ __('messages.from_date') }}</th><th>{{ __('messages.to_date') }}</th><th>{{ __('messages.days') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($leaveRequests as $lr)
                    <tr>
                        <td class="fw-semibold">{{ $lr->employee->name ?? '—' }}</td>
                        <td>{{ $lr->leaveType->name ?? '—' }}</td>
                        <td>{{ $lr->start_date->format('Y-m-d') }}</td>
                        <td>{{ $lr->end_date->format('Y-m-d') }}</td>
                        <td>{{ $lr->days }}</td>
                        <td>
                            @if($lr->status==='approved')<span class="pill pill-success">{{ __('messages.approved') }}</span>
                            @elseif($lr->status==='rejected')<span class="pill pill-danger">{{ __('messages.rejected') }}</span>
                            @else<span class="pill pill-warning">{{ __('messages.pending') }}</span>@endif
                        </td>
                        <td>
                            @can('leave-edit')
                            @if($lr->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.leave-request.approve', $lr->id) }}" method="POST">@csrf<button type="submit" class="btn-icon-sm" style="color:#16a34a" title="{{ __('messages.approve') }}"><i class="bi bi-check-lg"></i></button></form>
                                <form action="{{ route('admin.leave-request.reject', $lr->id) }}" method="POST">@csrf<button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.reject') }}"><i class="bi bi-x-lg"></i></button></form>
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
    @if($leaveRequests->hasPages())<div class="panel-card-body border-top pt-3">{{ $leaveRequests->links() }}</div>@endif
</div>

@endsection
