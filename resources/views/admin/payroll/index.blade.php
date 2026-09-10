@extends('admin.layouts.app')
@section('title', __('messages.payroll'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.payroll') }}</h1></div>
    @can('payroll-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#generateModal"><i class="bi bi-plus-lg"></i> {{ __('messages.generate_payroll') }}</button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>{{ __('messages.period') }}</th><th>{{ __('messages.field_total') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($runs as $run)
                    <tr>
                        <td class="fw-semibold">{{ $run->periodLabel() }}</td>
                        <td>{{ number_format($run->total_net, 2) }} {{ __('Currency') }}</td>
                        <td>
                            @if($run->status === 'finalized')<span class="pill pill-success">{{ __('messages.finalized') }}</span>
                            @else<span class="pill pill-warning">{{ __('messages.draft') }}</span>@endif
                        </td>
                        <td><a href="{{ route('admin.payroll.show', $run->id) }}" class="btn-icon-sm"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($runs->hasPages())<div class="panel-card-body border-top pt-3">{{ $runs->links() }}</div>@endif
</div>

<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.payroll.generate') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('messages.generate_payroll') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">{{ __('messages.field_month') }}</label>
                <select name="period_month" class="form-select mb-2">
                    @for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>@endfor
                </select>
                <label class="form-label">{{ __('messages.field_year') }}</label>
                <input type="number" name="period_year" value="{{ date('Y') }}" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="submit" class="btn-primary-sm">{{ __('messages.generate_payroll') }}</button></div>
        </form>
    </div>
</div>

@endsection
