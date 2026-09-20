@extends('admin.layouts.app')
@section('title', __('messages.attendances'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.attendances') }}</h1></div>
    @can('attendance-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addAttendanceModal"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.employees') }}</option>
                    @foreach($employees as $emp)<option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm" placeholder="{{ __('messages.from_date') }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm" placeholder="{{ __('messages.to_date') }}">
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i> {{ __('messages.filter') }}</button></div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.employees') }}</th><th>{{ __('messages.field_date') }}</th>
                        <th>{{ __('messages.field_check_in') }}</th><th>{{ __('messages.field_check_out') }}</th>
                        <th>{{ __('messages.late_minutes') }}</th><th>{{ __('messages.overtime_minutes') }}</th>
                        <th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $a)
                    <tr>
                        <td class="fw-semibold">{{ $a->employee->name ?? '—' }}</td>
                        <td>{{ $a->date->format('Y-m-d') }}</td>
                        <td>{{ $a->check_in->format('H:i') }}</td>
                        <td>{{ $a->check_out->format('H:i') }}</td>
                        <td>@if($a->late_minutes > 0)<span class="text-danger">{{ $a->late_minutes }} {{ __('messages.minutes_short') }}</span>@else - @endif</td>
                        <td>@if($a->overtime_minutes > 0)<span class="text-success">{{ $a->overtime_minutes }} {{ __('messages.minutes_short') }}</span>@else - @endif</td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('attendance-edit')
                                <button type="button" class="btn-icon-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editAttendanceModal{{ $a->id }}"><i class="bi bi-pencil"></i></button>
                                @endcan
                                @can('attendance-delete')
                                <form action="{{ route('admin.attendance.destroy', $a->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($attendances->hasPages())<div class="panel-card-body border-top pt-3">{{ $attendances->links() }}</div>@endif
</div>

@foreach($attendances as $a)
<div class="modal fade" id="editAttendanceModal{{ $a->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.attendance.update', $a->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.Edit') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.employees') }}</label>
                        <select name="employee_id" class="form-select" required>
                            @foreach($employees as $emp)<option value="{{ $emp->id }}" {{ $a->employee_id==$emp->id?'selected':'' }}>{{ $emp->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_date') }}</label>
                        <input type="date" name="date" value="{{ $a->date->format('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_check_in') }}</label>
                        <input type="time" name="check_in" value="{{ $a->check_in->format('H:i') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_check_out') }}</label>
                        <input type="time" name="check_out" value="{{ $a->check_out->format('H:i') }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control">{{ $a->notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.attendance.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.add_new') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.employees') }}</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-</option>
                            @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_date') }}</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_check_in') }}</label>
                        <input type="time" name="check_in" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.field_check_out') }}</label>
                        <input type="time" name="check_out" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('messages.field_notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button></div>
        </form>
    </div>
</div>

@endsection
