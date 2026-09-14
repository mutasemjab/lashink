@php $p = $prefix ?? ''; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.clients') }} <span class="text-danger">*</span></label>
        <select name="client_id" id="{{ $p }}client_id" class="form-select no-select2 js-client-select" required>
            <option value="">—</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.employees') }} <span class="text-danger">*</span></label>
        <select name="employee_id" id="{{ $p }}employee_id" class="form-select no-select2 js-staff-select" required>
            <option value="">—</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.field_date') }} / {{ __('messages.field_duration') }} <span class="text-danger">*</span></label>
        <input type="datetime-local" name="start_at" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.services') }} <span class="text-danger">*</span></label>
        <select name="services[]" id="{{ $p }}services" class="form-select" multiple required>
            @foreach($services as $s)
                <option value="{{ $s->id }}">{{ $s->name }} — {{ number_format($s->price, 2) }} {{ __('Currency') }} ({{ $s->duration_minutes }}{{ __('messages.minutes_short') }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('messages.field_notes') }}</label>
        <textarea name="notes" rows="2" class="form-control"></textarea>
    </div>
</div>
