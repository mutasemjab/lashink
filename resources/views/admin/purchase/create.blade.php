@extends('admin.layouts.app')
@section('title', __('messages.add_new'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.purchases') }} — {{ __('messages.add_new') }}</h1></div>
    <a href="{{ route('admin.purchase.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.purchase.store') }}" method="POST">
@csrf

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.suppliers') }}</label>
                <select name="supplier_id" class="form-select">
                    <option value="">—</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.field_date') }}</label>
                <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('messages.field_notes') }}</label>
                <textarea name="notes" rows="2" class="form-control"></textarea>
            </div>
        </div>
    </div>
</div>

<div class="panel-card mb-3">
    <div class="panel-card-header d-flex justify-content-between align-items-center">
        <h2 class="panel-card-title">{{ __('messages.products') }}</h2>
        <button type="button" class="btn-outline-sm" id="addRowBtn"><i class="bi bi-plus-lg"></i> {{ __('messages.add_new') }}</button>
    </div>
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table" id="itemsTable">
                <thead><tr><th>{{ __('messages.products') }}</th><th style="width:140px">{{ __('messages.field_quantity') }}</th><th style="width:160px">{{ __('messages.field_cost') }}</th><th style="width:140px">{{ __('messages.field_total') }}</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="panel-card-body border-top text-end">
        <strong>{{ __('messages.field_total') }}: <span id="grandTotal">0.00</span> {{ __('Currency') }}</strong>
    </div>
</div>

<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.purchase.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>

@push('scripts')
<script>
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'cost' => (float)$p->cost_price]));
let rowIndex = 0;

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><select name="product_id[]" class="form-select form-select-sm product-select" required>
            <option value="">—</option>
            ${products.map(p => `<option value="${p.id}" data-cost="${p.cost}">${p.name}</option>`).join('')}
        </select></td>
        <td><input type="number" step="0.01" min="0.01" name="quantity[]" class="form-control form-control-sm qty-input" value="1" required></td>
        <td><input type="number" step="0.01" min="0" name="unit_cost[]" class="form-control form-control-sm cost-input" value="0" required></td>
        <td><span class="row-total">0.00</span></td>
        <td><button type="button" class="btn-icon-sm btn-delete remove-row"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);

    const select = tr.querySelector('.product-select');
    const qtyInput = tr.querySelector('.qty-input');
    const costInput = tr.querySelector('.cost-input');
    const rowTotal = tr.querySelector('.row-total');

    function recalcRow() {
        const total = (parseFloat(qtyInput.value) || 0) * (parseFloat(costInput.value) || 0);
        rowTotal.textContent = total.toFixed(2);
        recalcGrand();
    }
    select.addEventListener('change', function () {
        const opt = select.options[select.selectedIndex];
        costInput.value = opt.dataset.cost || 0;
        recalcRow();
    });
    qtyInput.addEventListener('input', recalcRow);
    costInput.addEventListener('input', recalcRow);
    tr.querySelector('.remove-row').addEventListener('click', function () { tr.remove(); recalcGrand(); });

    if (window.jQuery) jQuery(select).select2({ theme: 'bootstrap-5', width: '100%' }).on('change', function(){ select.dispatchEvent(new Event('change')); });
}

function recalcGrand() {
    let sum = 0;
    document.querySelectorAll('.row-total').forEach(el => sum += parseFloat(el.textContent) || 0);
    document.getElementById('grandTotal').textContent = sum.toFixed(2);
}

document.getElementById('addRowBtn').addEventListener('click', addRow);
addRow();
</script>
@endpush
@endsection
