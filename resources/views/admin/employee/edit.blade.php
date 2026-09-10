@extends('admin.layouts.app')
@section('title', __('messages.edit_employee'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.edit_employee') }}</h1>
        <p class="page-sub">{{ $employee->name }}</p>
    </div>
    <a href="{{ route('admin.employee.index') }}" class="btn-outline-sm">
        <i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.employee.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="row g-4">

    {{-- Account info --}}
    <div class="col-12 col-xl-7">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h2 class="panel-card-title"><i class="bi bi-person-badge"></i> {{ __('messages.employee_account_info') }}</h2>
            </div>
            <div class="panel-card-body">
                <div class="row g-3">
                    @if($employee->photo)
                    <div class="col-12">
                        <img src="{{ asset($employee->photo) }}" alt="{{ $employee->name }}" style="width:64px;height:64px;object-fit:cover;border-radius:50%;">
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_full_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_username') }} <span class="text-danger">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $employee->username) }}"
                               class="form-control @error('username') is-invalid @enderror" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            {{ __('messages.field_password') }}
                            <small class="text-muted">({{ __('messages.keep_current_password') }})</small>
                        </label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_password_confirm') }}</label>
                        <input type="password" name="password_confirmation"
                               class="form-control" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_photo') }}</label>
                        <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-card h-100 mt-4">
            <div class="panel-card-header">
                <h2 class="panel-card-title"><i class="bi bi-briefcase"></i> {{ __('messages.employee_hr_info') }}</h2>
            </div>
            <div class="panel-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_national_id') }}</label>
                        <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_hire_date') }}</label>
                        <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_base_salary') }}</label>
                        <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary', $employee->base_salary) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_commission_percent') }}</label>
                        <input type="number" step="0.01" min="0" max="100" name="commission_percent" value="{{ old('commission_percent', $employee->commission_percent) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.field_employment_status') }}</label>
                        <select name="employment_status" class="form-select">
                            @php $st = old('employment_status', $employee->employment_status); @endphp
                            <option value="active" {{ $st === 'active' ? 'selected' : '' }}>{{ __('messages.status_active') }}</option>
                            <option value="on_leave" {{ $st === 'on_leave' ? 'selected' : '' }}>{{ __('messages.status_on_leave') }}</option>
                            <option value="terminated" {{ $st === 'terminated' ? 'selected' : '' }}>{{ __('messages.status_terminated') }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $employee->address) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $employee->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Roles --}}
    <div class="col-12 col-xl-5">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h2 class="panel-card-title"><i class="bi bi-shield-lock"></i> {{ __('messages.employee_roles') }}</h2>
            </div>
            <div class="panel-card-body">
                @if($roles->isEmpty())
                    <p class="text-muted small mb-0">
                        <a href="{{ route('admin.role.create') }}">{{ __('messages.add_new') }}</a>
                    </p>
                @else
                @php $checked = old('roles') !== null ? array_map('intval', old('roles', [])) : $assignedRoles; @endphp
                <div class="d-flex flex-column gap-2">
                    @foreach($roles as $role)
                    @php $isChecked = in_array($role->id, $checked); @endphp
                    <label class="d-flex align-items-center gap-2 p-2 rounded border cursor-pointer role-item
                           {{ $isChecked ? 'selected' : '' }}">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                               class="role-checkbox"
                               {{ $isChecked ? 'checked' : '' }}>
                        <span class="fw-semibold">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.employee.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>

</form>

@push('styles')
<style>
.cursor-pointer { cursor: pointer; }
.role-item { cursor: pointer; transition: background .15s, border-color .15s; }
.role-item:hover, .role-item.selected { background: var(--primary-50, #eff6ff); border-color: var(--primary-400, #60a5fa) !important; }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.role-checkbox').forEach(function (cb) {
    cb.addEventListener('change', function () {
        this.closest('.role-item').classList.toggle('selected', this.checked);
    });
});
</script>
@endpush

@endsection
