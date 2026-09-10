@extends('admin.layouts.app')
@section('title', __('messages.settings'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.settings') }}</h1></div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="panel-card">
    <div class="panel-card-header"><h2 class="panel-card-title"><i class="bi bi-shop"></i> {{ __('messages.salon_info') }}</h2></div>
    <div class="panel-card-body">
        <div class="row g-3">
            @if($settings['salon_logo'])
            <div class="col-12"><img src="{{ asset($settings['salon_logo']) }}" style="height:70px;border-radius:10px;"></div>
            @endif
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.salon_name') }}</label>
                <input type="text" name="salon_name" value="{{ old('salon_name', $settings['salon_name']) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_phone') }}</label>
                <input type="text" name="salon_phone" value="{{ old('salon_phone', $settings['salon_phone']) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_address') }}</label>
                <input type="text" name="salon_address" value="{{ old('salon_address', $settings['salon_address']) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.currency_symbol') }}</label>
                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'د.أ') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.salon_logo') }}</label>
                <input type="file" name="salon_logo" accept="image/*" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.working_hours') }}</label>
                <textarea name="working_hours" rows="2" class="form-control" placeholder="{{ __('messages.working_hours_ph') }}">{{ old('working_hours', $settings['working_hours']) }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
</div>
</form>

@endsection
