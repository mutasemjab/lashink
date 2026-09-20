@extends('admin.layouts.app')
@section('title', __('messages.products'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.products') }}</h1></div>
    @can('product-add')
    <a href="{{ route('admin.product.create') }}" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('messages.Search') }}...">
            </div>
            <div class="col-auto">
                <div class="form-check mt-2">
                    <input type="checkbox" name="low_stock" value="1" class="form-check-input" id="lowstock" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="lowstock">{{ __('messages.low_stock_only') }}</label>
                </div>
            </div>
            <div class="col-auto"><button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i></button></div>
        </form>
    </div>
</div>

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>{{ __('messages.field_name') }}</th><th>{{ __('messages.field_category') }}</th>
                        <th>{{ __('messages.field_quantity') }}</th><th>{{ __('messages.field_cost') }}</th>
                        <th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td>
                            {{ number_format($p->quantity_in_stock, 2) }} {{ $p->unit }}
                            @if($p->isLowStock())
                                <span class="pill pill-danger">{{ __('messages.low_stock') }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($p->cost_price, 2) }} {{ $p->currency->code ?? __('Currency') }}</td>
                        <td>
                            @if($p->is_active)<span class="pill pill-success">{{ __('Active') }}</span>@else<span class="pill pill-neutral">{{ __('Inactive') }}</span>@endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('product-edit')
                                <a href="{{ route('admin.product.edit', $p->id) }}" class="btn-icon-sm btn-edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('product-delete')
                                <form action="{{ route('admin.product.destroy', $p->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
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
    @if($products->hasPages())<div class="panel-card-body border-top pt-3">{{ $products->links() }}</div>@endif
</div>

@endsection
