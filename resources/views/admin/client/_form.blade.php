@php $c = $client ?? null; @endphp

<div class="panel-card">
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_full_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $c->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_phone') }} <span class="text-danger">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $c->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('messages.field_phone2') }}</label>
                <input type="text" name="phone2" value="{{ old('phone2', $c->phone2 ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('messages.field_gender') }}</label>
                <select name="gender" class="form-select">
                    <option value="female" {{ old('gender', $c->gender ?? 'female') === 'female' ? 'selected' : '' }}>{{ __('messages.gender_female') }}</option>
                    <option value="male" {{ old('gender', $c->gender ?? '') === 'male' ? 'selected' : '' }}>{{ __('messages.gender_male') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('messages.field_birthdate') }}</label>
                <input type="date" name="birthdate" value="{{ old('birthdate', optional($c->birthdate ?? null)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('messages.field_source') }}</label>
                <input type="text" name="source" value="{{ old('source', $c->source ?? '') }}" class="form-control" placeholder="Instagram / Referral...">
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.field_address') }}</label>
                <input type="text" name="address" value="{{ old('address', $c->address ?? '') }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.field_notes') }}</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $c->notes ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_blocked" name="is_blocked" value="1" {{ old('is_blocked', $c->is_blocked ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_blocked">{{ __('messages.client_blocked') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>
