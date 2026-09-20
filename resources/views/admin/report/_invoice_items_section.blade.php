@php $withPayment = $withPayment ?? true; @endphp
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.select_currency') }}</label>
        <select name="currency_id" class="form-select no-select2" data-role="currency-select">
            @foreach($currencies as $cur)
                <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" {{ \App\Models\Currency::default()?->id == $cur->id ? 'selected' : '' }}>{{ $cur->code }} ({{ $cur->symbol }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('messages.field_discount') }}</label>
        <input type="number" step="0.01" min="0" name="discount_amount" value="0" class="form-control" data-role="discount">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('messages.tax') }}</label>
        <input type="number" step="0.01" min="0" name="tax_amount" value="0" class="form-control" data-role="tax">
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">{{ __('messages.field_description') }}</h6>
    <div class="d-flex gap-2">
        <button type="button" class="btn-outline-sm" data-role="add-service"><i class="bi bi-scissors"></i> {{ __('messages.services') }}</button>
        <button type="button" class="btn-outline-sm" data-role="add-product"><i class="bi bi-box-seam"></i> {{ __('messages.products') }}</button>
    </div>
</div>
<div class="table-responsive">
    <table class="data-table">
        <thead><tr><th>{{ __('messages.field_description') }}</th><th style="width:100px">{{ __('messages.field_quantity') }}</th><th style="width:120px">{{ __('messages.field_price') }}</th><th style="width:150px">{{ __('messages.employees') }}</th><th style="width:100px">{{ __('messages.field_total') }}</th><th></th></tr></thead>
        <tbody data-role="items-body"></tbody>
    </table>
</div>
<div class="text-end mt-2">
    <div>{{ __('messages.subtotal') }}: <span data-role="subtotal">0.00</span></div>
    <div class="fs-5 fw-bold">{{ __('messages.field_total') }}: <span data-role="grand-total">0.00</span> <span data-role="grand-total-currency"></span></div>
</div>

@if($withPayment)
<div class="form-check mt-3">
    <input type="checkbox" class="form-check-input" name="mark_paid" value="1" data-role="mark-paid">
    <label class="form-check-label">{{ __('messages.mark_paid_now') }}</label>
</div>
<div class="mt-2 d-none" data-role="payment-method-wrap">
    <label class="form-label">{{ __('messages.field_payment_method') }}</label>
    <select name="payment_method" class="form-select no-select2" style="max-width:220px">
        <option value="cash">{{ __('messages.pm_cash') }}</option>
        <option value="card">{{ __('messages.pm_card') }}</option>
        <option value="transfer">{{ __('messages.pm_transfer') }}</option>
    </select>
</div>
@endif

<template data-role="employee-options">
    <option value="">—</option>
    @foreach($employees as $emp)
    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
    @endforeach
</template>
