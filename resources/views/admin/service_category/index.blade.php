@extends('admin.layouts.app')
@section('title', __('messages.service_categories'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.service_categories') }}</h1>
    </div>
    @can('service-category-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}
    </button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.field_name') }}</th>
                        <th>{{ __('messages.services') }}</th>
                        <th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-semibold">{{ $cat->name }}</span></td>
                        <td><span class="pill pill-info">{{ $cat->services_count }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('service-category-edit')
                                <button type="button" class="btn-icon-sm btn-edit" title="{{ __('messages.Edit') }}"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endcan
                                @can('service-category-delete')
                                <form action="{{ route('admin.service-category.destroy', $cat->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    {{-- Edit modal --}}
                    <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.service-category.update', $cat->id) }}" method="POST" class="modal-content">
                                @csrf @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('messages.Edit') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">{{ __('messages.field_name') }}</label>
                                    <input type="text" name="name" value="{{ $cat->name }}" class="form-control mb-2" required>
                                    <label class="form-label">Bootstrap Icon (bi-*)</label>
                                    <input type="text" name="icon" value="{{ $cat->icon }}" class="form-control mb-2">
                                    <label class="form-label">{{ __('messages.field_sort_order') }}</label>
                                    <input type="number" name="sort_order" value="{{ $cat->sort_order }}" class="form-control">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
                                </div>
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

{{-- Add modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.service-category.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_new') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.field_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control mb-2" required>
                <label class="form-label">Bootstrap Icon (bi-*)</label>
                <input type="text" name="icon" placeholder="bi-scissors" class="form-control mb-2">
                <label class="form-label">{{ __('messages.field_sort_order') }}</label>
                <input type="number" name="sort_order" value="0" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
