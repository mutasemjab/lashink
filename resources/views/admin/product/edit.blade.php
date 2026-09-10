@extends('admin.layouts.app')
@section('title', __('messages.Edit'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.products') }} — {{ __('messages.Edit') }}</h1><p class="page-sub">{{ $product->name }}</p></div>
    <div class="d-flex gap-2">
        @can('product-edit')
        <button type="button" class="btn-outline-sm" data-bs-toggle="modal" data-bs-target="#adjustStockModal"><i class="bi bi-box-seam"></i> {{ __('messages.adjust_stock') }}</button>
        @endcan
        <a href="{{ route('admin.product.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
@include('admin.product._form')
<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.product.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>

<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.product.adjust-stock', $product->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.adjust_stock') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.movement_type') }}</label>
                <select name="type" class="form-select mb-2">
                    <option value="in">{{ __('messages.movement_in') }}</option>
                    <option value="out">{{ __('messages.movement_out') }}</option>
                </select>
                <label class="form-label">{{ __('messages.field_quantity') }}</label>
                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control mb-2" required>
                <label class="form-label">{{ __('messages.field_notes') }}</label>
                <textarea name="note" class="form-control"></textarea>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
