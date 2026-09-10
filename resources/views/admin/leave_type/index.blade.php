@extends('admin.layouts.app')
@section('title', __('messages.leave_types'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.leave_types') }}</h1></div>
    @can('leave-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addLTModal"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>#</th><th>{{ __('messages.field_name') }}</th><th>{{ __('messages.default_days') }}</th><th>{{ __('messages.is_paid_leave') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($leaveTypes as $lt)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $lt->name }}</td>
                        <td>{{ $lt->default_days_per_year }}</td>
                        <td>@if($lt->is_paid)<span class="pill pill-success">{{ __('Yes') }}</span>@else<span class="pill pill-neutral">{{ __('No') }}</span>@endif</td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('leave-edit')
                                <button type="button" class="btn-icon-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editLTModal{{ $lt->id }}"><i class="bi bi-pencil"></i></button>
                                @endcan
                                @can('leave-delete')
                                <form action="{{ route('admin.leave-type.destroy', $lt->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="editLTModal{{ $lt->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.leave-type.update', $lt->id) }}" method="POST" class="modal-content">
                                @csrf @method('PUT')
                                <div class="modal-header"><h5 class="modal-title">{{ __('messages.Edit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <label class="form-label">{{ __('messages.field_name') }}</label>
                                    <input type="text" name="name" value="{{ $lt->name }}" class="form-control mb-2" required>
                                    <label class="form-label">{{ __('messages.default_days') }}</label>
                                    <input type="number" min="0" name="default_days_per_year" value="{{ $lt->default_days_per_year }}" class="form-control mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="is_paid" value="1" id="paid{{ $lt->id }}" {{ $lt->is_paid ? 'checked' : '' }}>
                                        <label class="form-check-label" for="paid{{ $lt->id }}">{{ __('messages.is_paid_leave') }}</label>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addLTModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.leave-type.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.add_new') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.field_name') }}</label>
                <input type="text" name="name" class="form-control mb-2" required>
                <label class="form-label">{{ __('messages.default_days') }}</label>
                <input type="number" min="0" name="default_days_per_year" value="0" class="form-control mb-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_paid" value="1" id="paidNew" checked>
                    <label class="form-check-label" for="paidNew">{{ __('messages.is_paid_leave') }}</label>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
