@php $e = $expense ?? null; @endphp
<div class="panel-card">
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_category') }} <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $e->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_amount') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $e->amount ?? '') }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.select_currency') }} <span class="text-danger">*</span></label>
                <select name="currency_id" class="form-select" required>
                    @foreach($currencies as $cur)
                        <option value="{{ $cur->id }}" {{ old('currency_id', $e->currency_id ?? \App\Models\Currency::default()?->id) == $cur->id ? 'selected' : '' }}>{{ $cur->code }} ({{ $cur->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_date') }} <span class="text-danger">*</span></label>
                <input type="date" name="expense_date" value="{{ old('expense_date', optional($e->expense_date ?? null)->format('Y-m-d') ?: date('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_payment_method') }}</label>
                <select name="payment_method" class="form-select">
                    <option value="cash" {{ old('payment_method', $e->payment_method ?? 'cash') === 'cash' ? 'selected' : '' }}>{{ __('messages.pm_cash') }}</option>
                    <option value="card" {{ old('payment_method', $e->payment_method ?? '') === 'card' ? 'selected' : '' }}>{{ __('messages.pm_card') }}</option>
                    <option value="transfer" {{ old('payment_method', $e->payment_method ?? '') === 'transfer' ? 'selected' : '' }}>{{ __('messages.pm_transfer') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_attachment') }}</label>
                <input type="file" name="attachment" class="form-control">
                @if($e && $e->attachment)<small class="text-muted"><a href="{{ asset($e->attachment) }}" target="_blank">{{ __('messages.field_attachment') }}</a></small>@endif
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.field_description') }}</label>
                <textarea name="description" rows="2" class="form-control">{{ old('description', $e->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>
