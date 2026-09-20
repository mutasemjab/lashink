@extends('admin.layouts.app')
@section('title', __('messages.services'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title">{{ __('messages.services') }}</h1>
    </div>
    @can('service-add')
    <a href="{{ route('admin.service.create') }}" class="btn-primary-sm">
        <i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}
    </a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control form-control-sm" placeholder="{{ __('messages.Search') }}...">
            </div>
            <div class="col-12 col-md-4">
                <select name="category_id" class="form-select form-select-sm no-select2">
                    <option value="">{{ __('messages.field_category') }}: {{ __('messages.All Status') }}</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i></button>
            </div>
            @if(request('search') || request('category_id'))
            <div class="col-auto">
                <a href="{{ route('admin.service.index') }}" class="btn-outline-sm"><i class="bi bi-x"></i> {{ __('messages.Reset') }}</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.field_name') }}</th>
                        <th>{{ __('messages.field_category') }}</th>
                        <th>{{ __('messages.field_duration') }}</th>
                        <th>{{ __('messages.field_price') }}</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $s)
                    <tr>
                        <td>{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                        <td><span class="fw-semibold">{{ $s->name }}</span></td>
                        <td>{{ $s->category->name ?? '—' }}</td>
                        <td>{{ $s->duration_minutes }}</td>
                        <td>{{ number_format($s->price, 2) }} {{ $s->currency->code ?? __('Currency') }}</td>
                        <td>
                            @if($s->is_active)
                                <span class="pill pill-success">{{ __('Active') }}</span>
                            @else
                                <span class="pill pill-neutral">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('service-edit')
                                <a href="{{ route('admin.service.edit', $s->id) }}" class="btn-icon-sm btn-edit" title="{{ __('messages.Edit') }}"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('service-delete')
                                <form action="{{ route('admin.service.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.Delete') }}"><i class="bi bi-trash"></i></button>
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
    @if($services->hasPages())
    <div class="panel-card-body border-top pt-3">{{ $services->links() }}</div>
    @endif
</div>

@endsection
