@extends('admin.layouts.app')
@section('title', __('messages.product_categories'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.product_categories') }}</h1></div>
    @can('product-category-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addPCModal"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>#</th><th>{{ __('messages.field_name') }}</th><th>{{ __('messages.products') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($categories as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td><span class="pill pill-info">{{ $c->products_count }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('product-category-edit')
                                <button type="button" class="btn-icon-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editPCModal{{ $c->id }}"><i class="bi bi-pencil"></i></button>
                                @endcan
                                @can('product-category-delete')
                                <form action="{{ route('admin.product-category.destroy', $c->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="editPCModal{{ $c->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.product-category.update', $c->id) }}" method="POST" class="modal-content">
                                @csrf @method('PUT')
                                <div class="modal-header"><h5 class="modal-title">{{ __('messages.Edit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="text" name="name" value="{{ $c->name }}" class="form-control" required>
                                </div>
                                <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPCModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.product-category.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.add_new') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><input type="text" name="name" class="form-control" required></div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
