@php $p = $prefix ?? ''; $showStaff = $showStaff ?? true; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.clients') }} <span class="text-danger">*</span></label>
        <select name="client_id" id="{{ $p }}client_id" class="form-select no-select2 js-client-select" required>
            <option value="">—</option>
        </select>
    </div>
    @if($showStaff)
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.employees') }}</label>
        <select name="employee_id" id="{{ $p }}employee_id" class="form-select no-select2 js-staff-select">
            <option value="">—</option>
        </select>
    </div>
    @endif
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.field_date') }} <span class="text-danger">*</span></label>
        <input type="date" name="appt_date" id="{{ $p }}appt_date" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.field_time') }} <span class="text-danger">*</span></label>
        <input type="text" name="appt_time" id="{{ $p }}appt_time" class="form-control js-time-picker" autocomplete="off" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('messages.services') }} <span class="text-danger">*</span></label>
        <select name="services[]" id="{{ $p }}services" class="form-select" multiple required>
            @foreach($services as $s)
                <option value="{{ $s->id }}">{{ $s->name }} — {{ number_format($s->price, 2) }} {{ $s->currency->code ?? __('Currency') }} ({{ $s->duration_minutes }}{{ __('messages.minutes_short') }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('messages.field_notes') }}</label>
        <textarea name="notes" rows="2" class="form-control"></textarea>
    </div>
</div>
