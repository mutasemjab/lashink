@extends('admin.layouts.app')
@section('title', __('messages.add_new'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.leave_requests') }} — {{ __('messages.add_new') }}</h1></div>
    <a href="{{ route('admin.leave-request.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.leave-request.store') }}" method="POST">
@csrf
<div class="panel-card">
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.employees') }} <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select" required>
                    <option value="">—</option>
                    @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.leave_type') }} <span class="text-danger">*</span></label>
                <select name="leave_type_id" class="form-select" required>
                    <option value="">—</option>
                    @foreach($leaveTypes as $lt)<option value="{{ $lt->id }}">{{ $lt->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.from_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.to_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.field_reason') }}</label>
                <textarea name="reason" rows="2" class="form-control"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.leave-request.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>
@endsection
