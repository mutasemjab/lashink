@extends('admin.layouts.app')
@section('title', __('messages.expenses'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.expenses') }}</h1></div>
    @can('expense-add')
    <a href="{{ route('admin.expense.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label">{{ __('messages.from_date') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">{{ __('messages.to_date') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">{{ __('messages.field_category') }}</label>
                <select name="category_id" class="form-select form-select-sm no-select2">
                    <option value="">{{ __('All Status') }}</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i> {{ __('messages.filter') }}</button></div>
            @if(request()->hasAny(['from','to','category_id']))
            <div class="col-auto"><a href="{{ route('admin.expense.index') }}" class="btn-outline-sm"><i class="bi bi-x"></i> {{ __('Reset') }}</a></div>
            @endif
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header d-flex align-items-center justify-content-between">
        <h2 class="panel-card-title"><i class="bi bi-cash-stack"></i> {{ __('messages.expenses') }}</h2>
        <div class="d-flex gap-1 flex-wrap">
            @forelse($totalsByCurrency as $code => $sum)
                <span class="pill pill-danger">{{ number_format($sum, 2) }} {{ $code }}</span>
            @empty
                <span class="pill pill-danger">0.00</span>
            @endforelse
        </div>
    </div>
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.field_date') }}</th><th>{{ __('messages.field_category') }}</th><th>{{ __('messages.field_amount') }}</th><th>{{ __('messages.field_payment_method') }}</th><th>{{ __('messages.field_description') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($expenses as $e)
                    <tr>
                        <td>{{ $e->expense_date->format('Y-m-d') }}</td>
                        <td>{{ $e->category->name ?? '—' }}</td>
                        <td class="fw-semibold">{{ number_format($e->amount, 2) }} {{ $e->currency->code ?? __('Currency') }}</td>
                        <td>{{ __('messages.pm_' . $e->payment_method) }}</td>
                        <td class="text-muted">{{ \Illuminate\Support\Str::limit($e->description, 40) }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($e->attachment)<a href="{{ asset($e->attachment) }}" target="_blank" class="btn-icon-sm"><i class="bi bi-paperclip"></i></a>@endif
                                @can('expense-edit')
                                <a href="{{ route('admin.expense.edit', $e->id) }}" class="btn-icon-sm btn-edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('expense-delete')
                                <form action="{{ route('admin.expense.destroy', $e->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($expenses->hasPages())<div class="panel-card-body border-top pt-3">{{ $expenses->links() }}</div>@endif
</div>

@endsection
