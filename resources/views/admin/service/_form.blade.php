@php $s = $service ?? null; @endphp

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="panel-card h-100">
            <div class="panel-card-header"><h2 class="panel-card-title"><i class="bi bi-scissors"></i> {{ __('messages.field_description') }}</h2></div>
            <div class="panel-card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $s->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_category') }}</label>
                        <select name="category_id" class="form-select">
                            <option value="">—</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $s->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_duration') }} <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $s->duration_minutes ?? 30) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_price') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $s->price ?? 0) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $s->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_commission_percent') }} — {{ __('messages.field_amount') }}</label>
                        <select name="commission_type" class="form-select">
                            <option value="percent" {{ old('commission_type', $s->commission_type ?? 'percent') === 'percent' ? 'selected' : '' }}>%</option>
                            <option value="fixed" {{ old('commission_type', $s->commission_type ?? '') === 'fixed' ? 'selected' : '' }}>{{ __('Currency') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_commission_percent') }}</label>
                        <input type="number" step="0.01" min="0" name="commission_value" value="{{ old('commission_value', $s->commission_value ?? 0) }}" class="form-control">
                        <small class="text-muted">0 = {{ __('messages.field_commission_percent') }} {{ __('messages.employees') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="panel-card h-100">
            <div class="panel-card-header"><h2 class="panel-card-title"><i class="bi bi-people"></i> {{ __('messages.employees') }}</h2></div>
            <div class="panel-card-body">
                <select name="employees[]" multiple class="form-select">
                    @php $assignedIds = $assigned ?? []; @endphp
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ in_array($emp->id, old('employees', $assignedIds)) ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
