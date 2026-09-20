@extends('admin.layouts.app')
@section('title', __('messages.currencies'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.currencies') }}</h1></div>
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addCurrencyModal"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>#</th><th>{{ __('messages.field_code') }}</th><th>{{ __('messages.field_symbol') }}</th><th>{{ __('messages.field_status') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($currencies as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $c->code }} @if($c->is_default)<span class="pill pill-info">{{ __('messages.default') }}</span>@endif</td>
                        <td>{{ $c->symbol }}</td>
                        <td>
                            @if($c->is_active)
                                <span class="pill pill-success">{{ __('messages.Active') }}</span>
                            @else
                                <span class="pill pill-secondary">{{ __('messages.Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$c->is_default)
                                <form action="{{ route('admin.currency.make-default', $c->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-outline-sm btn-sm">{{ __('messages.make_default') }}</button>
                                </form>
                                @endif
                                <button type="button" class="btn-icon-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editCurrencyModal{{ $c->id }}"><i class="bi bi-pencil"></i></button>
                                @if(!$c->is_default)
                                <form action="{{ route('admin.currency.destroy', $c->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($currencies as $c)
<div class="modal fade" id="editCurrencyModal{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.currency.update', $c->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.Edit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.field_code') }}</label>
                    <input type="text" name="code" value="{{ $c->code }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.field_symbol') }}</label>
                    <input type="text" name="symbol" value="{{ $c->symbol }}" class="form-control" required>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive{{ $c->id }}" {{ $c->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive{{ $c->id }}">{{ __('messages.Active') }}</label>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addCurrencyModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.currency.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.add_new') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.field_code') }}</label>
                    <input type="text" name="code" class="form-control" placeholder="USD" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.field_symbol') }}</label>
                    <input type="text" name="symbol" class="form-control" placeholder="$" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
