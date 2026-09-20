@extends('admin.layouts.app')
@section('title', __('messages.suppliers'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.suppliers') }}</h1></div>
    @can('supplier-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>#</th><th>{{ __('messages.field_name') }}</th><th>{{ __('messages.field_phone') }}</th><th>{{ __('messages.products') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td>{{ $s->phone ?: '—' }}</td>
                        <td><span class="pill pill-info">{{ $s->products_count }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('supplier-edit')
                                <button type="button" class="btn-icon-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $s->id }}"><i class="bi bi-pencil"></i></button>
                                @endcan
                                @can('supplier-delete')
                                <form action="{{ route('admin.supplier.destroy', $s->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
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

@foreach($suppliers as $s)
<div class="modal fade" id="editSupplierModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.supplier.update', $s->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.Edit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.field_name') }}</label>
                <input type="text" name="name" value="{{ $s->name }}" class="form-control mb-2" required>
                <label class="form-label">{{ __('messages.field_phone') }}</label>
                <input type="text" name="phone" value="{{ $s->phone }}" class="form-control mb-2">
                <label class="form-label">{{ __('messages.field_address') }}</label>
                <input type="text" name="address" value="{{ $s->address }}" class="form-control mb-2">
                <label class="form-label">{{ __('messages.field_notes') }}</label>
                <textarea name="notes" class="form-control">{{ $s->notes }}</textarea>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.supplier.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.add_new') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.field_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control mb-2" required>
                <label class="form-label">{{ __('messages.field_phone') }}</label>
                <input type="text" name="phone" class="form-control mb-2">
                <label class="form-label">{{ __('messages.field_address') }}</label>
                <input type="text" name="address" class="form-control mb-2">
                <label class="form-label">{{ __('messages.field_notes') }}</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
