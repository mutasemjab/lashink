@extends('admin.layouts.app')
@section('title', __('messages.add_new'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.salary_advances') }} — {{ __('messages.add_new') }}</h1></div>
    <a href="{{ route('admin.salary-advance.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.salary-advance.store') }}" method="POST">
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
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_amount') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="1" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.select_currency') }} <span class="text-danger">*</span></label>
                <select name="currency_id" class="form-select" required>
                    @foreach($currencies as $cur)
                        <option value="{{ $cur->id }}" {{ \App\Models\Currency::default()?->id == $cur->id ? 'selected' : '' }}>{{ $cur->code }} ({{ $cur->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="request_date" value="{{ date('Y-m-d') }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.repayment') }}</label>
                <select name="repayment_type" id="repaymentType" class="form-select">
                    <option value="single">{{ __('messages.repayment_single') }}</option>
                    <option value="installments">{{ __('messages.repayment_installments') }}</option>
                </select>
            </div>
            <div class="col-md-3" id="installmentsWrap" style="display:none">
                <label class="form-label">{{ __('messages.installments_count') }}</label>
                <input type="number" min="1" max="24" name="installments_count" value="1" class="form-control">
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
    <a href="{{ route('admin.salary-advance.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>

@push('scripts')
<script>
document.getElementById('repaymentType').addEventListener('change', function () {
    document.getElementById('installmentsWrap').style.display = this.value === 'installments' ? '' : 'none';
});
</script>
@endpush
@endsection
