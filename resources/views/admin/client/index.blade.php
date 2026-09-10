@extends('admin.layouts.app')
@section('title', __('messages.clients'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.clients') }}</h1></div>
    @can('client-add')
    <a href="{{ route('admin.client.create') }}" class="btn-primary-sm"><i class="bi bi-person-plus"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('messages.Search') }}...">
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i></button></div>
            @if(request('search'))
            <div class="col-auto"><a href="{{ route('admin.client.index') }}" class="btn-outline-sm"><i class="bi bi-x"></i> {{ __('messages.Reset') }}</a></div>
            @endif
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-header d-flex align-items-center justify-content-between">
        <h2 class="panel-card-title"><i class="bi bi-person-hearts"></i> {{ __('messages.clients') }}</h2>
        <span class="pill pill-info">{{ $clients->total() }} {{ __('messages.total_records') }}</span>
    </div>
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.field_name') }}</th>
                        <th>{{ __('messages.field_phone') }}</th>
                        <th>{{ __('messages.field_source') }}</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $c)
                    <tr>
                        <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                        <td><span class="fw-semibold">{{ $c->name }}</span></td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->source ?: '—' }}</td>
                        <td>
                            @if($c->is_blocked)
                                <span class="pill pill-danger">{{ __('messages.client_blocked') }}</span>
                            @else
                                <span class="pill pill-success">{{ __('Active') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('client-edit')
                                <a href="{{ route('admin.client.edit', $c->id) }}" class="btn-icon-sm btn-edit" title="{{ __('messages.Edit') }}"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('client-delete')
                                <form action="{{ route('admin.client.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete" title="{{ __('messages.Delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clients->hasPages())
    <div class="panel-card-body border-top pt-3">{{ $clients->links() }}</div>
    @endif
</div>

@endsection
