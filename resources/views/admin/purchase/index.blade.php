@extends('admin.layouts.app')
@section('title', __('messages.purchases'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.purchases') }}</h1></div>
    @can('purchase-add')
    <a href="{{ route('admin.purchase.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>#</th><th>{{ __('messages.field_date') }}</th><th>{{ __('messages.suppliers') }}</th><th>{{ __('messages.field_total') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($purchases as $pu)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pu->purchase_date->format('Y-m-d') }}</td>
                        <td>{{ $pu->supplier->name ?? '—' }}</td>
                        <td>{{ number_format($pu->total, 2) }} {{ __('Currency') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.purchase.show', $pu->id) }}" class="btn-icon-sm"><i class="bi bi-eye"></i></a>
                                @can('purchase-delete')
                                <form action="{{ route('admin.purchase.destroy', $pu->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon-sm btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())<div class="panel-card-body border-top pt-3">{{ $purchases->links() }}</div>@endif
</div>

@endsection
