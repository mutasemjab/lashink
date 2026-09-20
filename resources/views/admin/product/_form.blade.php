@php $p = $product ?? null; @endphp
<div class="panel-card">
    <div class="panel-card-body">
        <div class="row g-3">
            @if($p && $p->image)
            <div class="col-12"><img src="{{ asset($p->image) }}" style="width:80px;height:80px;object-fit:cover;border-radius:10px;"></div>
            @endif
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_category') }}</label>
                <select name="category_id" class="form-select">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $p->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.suppliers') }}</label>
                <select name="supplier_id" class="form-select">
                    <option value="">—</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id', $p->supplier_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_unit') }}</label>
                <input type="text" name="unit" value="{{ old('unit', $p->unit ?? 'قطعة') }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_quantity') }}</label>
                <input type="number" step="0.01" min="0" name="quantity_in_stock" value="{{ old('quantity_in_stock', $p->quantity_in_stock ?? 0) }}" class="form-control" {{ $p ? 'readonly' : '' }}>
                @if($p)<small class="text-muted">{{ __('messages.use_adjust_stock') }}</small>@endif
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.min_stock_alert') }}</label>
                <input type="number" step="0.01" min="0" name="min_stock_alert" value="{{ old('min_stock_alert', $p->min_stock_alert ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_cost') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $p->cost_price ?? 0) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_price') }}</label>
                <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $p->sale_price ?? '') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.select_currency') }} <span class="text-danger">*</span></label>
                <select name="currency_id" class="form-select" required>
                    @foreach($currencies as $cur)
                        <option value="{{ $cur->id }}" {{ old('currency_id', $p->currency_id ?? \App\Models\Currency::default()?->id) == $cur->id ? 'selected' : '' }}>{{ $cur->code }} ({{ $cur->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_photo') }}</label>
                <input type="file" name="image" accept="image/*" class="form-control">
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="is_sellable" name="is_sellable" value="1" {{ old('is_sellable', $p->is_sellable ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_sellable">{{ __('messages.is_sellable') }}</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $p->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>
