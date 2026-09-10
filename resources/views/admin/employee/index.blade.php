@extends('admin.layouts.app')
@section('title', __('messages.employees'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.employees') }}</h1>
    </div>
    @can('employee-add')
    <a href="{{ route('admin.employee.create') }}" class="btn-primary-sm">
        <i class="bi bi-person-plus"></i> {{ __('messages.add_employee') }}
    </a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control form-control-sm" placeholder="{{ __('messages.Search') }}...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i></button>
            </div>
            @if(request('search'))
            <div class="col-auto">
                <a href="{{ route('admin.employee.index') }}" class="btn-outline-sm"><i class="bi bi-x"></i> {{ __('messages.Reset') }}</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header d-flex align-items-center justify-content-between">
        <h2 class="panel-card-title"><i class="bi bi-people"></i> {{ __('messages.employees') }}</h2>
        <span class="pill pill-info">{{ $employees->total() }} {{ __('messages.total_records') }}</span>
    </div>
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.field_name') }}</th>
                        <th>{{ __('messages.field_username') }}</th>
                        <th>{{ __('messages.field_phone') }}</th>
                        <th>{{ __('messages.employee_roles') }}</th>
                        <th>{{ __('messages.field_employment_status') }}</th>
                        <th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td>{{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($emp->photo)
                                    <img src="{{ asset($emp->photo) }}" style="width:32px;height:32px;object-fit:cover;border-radius:50%;">
                                @endif
                                <span class="fw-semibold">{{ $emp->name }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted">{{ $emp->username }}</span></td>
                        <td>{{ $emp->phone ?: '—' }}</td>
                        <td>
                            @forelse($emp->roles as $role)
                                <span class="pill pill-info">{{ $role->name }}</span>
                            @empty
                                <span class="pill pill-neutral">{{ __('messages.no_role') }}</span>
                            @endforelse
                        </td>
                        <td>
                            @if($emp->employment_status === 'active')
                                <span class="pill pill-success">{{ __('messages.status_active') }}</span>
                            @elseif($emp->employment_status === 'on_leave')
                                <span class="pill pill-warning">{{ __('messages.status_on_leave') }}</span>
                            @else
                                <span class="pill pill-neutral">{{ __('messages.status_terminated') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('employee-edit')
                                <a href="{{ route('admin.employee.edit', $emp->id) }}"
                                   class="btn-icon-sm btn-edit" title="{{ __('messages.Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('employee-delete')
                                <form action="{{ route('admin.employee.destroy', $emp->id) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            {{ __('messages.no_records') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
    <div class="panel-card-body border-top pt-3">
        {{ $employees->links() }}
    </div>
    @endif
</div>

@endsection
