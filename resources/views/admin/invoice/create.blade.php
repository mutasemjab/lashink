@extends('admin.layouts.app')
@section('title', __('messages.add_new'))

@section('content')
<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.invoices') }} — {{ __('messages.add_new') }}</h1></div>
    <a href="{{ route('admin.invoice.index') }}" class="btn-outline-sm"><i class="bi bi-arrow-right"></i> {{ __('messages.back_to_list') }}</a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3"><ul class="mb-0">@foreach($errors->all() as $er)<li>{{ $er }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form action="{{ route('admin.invoice.store') }}" method="POST">
@csrf
<input type="hidden" name="appointment_id" id="appointmentIdInput" value="{{ $appointment->id ?? '' }}">

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ __('messages.select_from_appointment') }}</label>
                <select id="appointmentSelect" class="form-select no-select2 js-appointment-select">
                    <option value="">—</option>
                    @if($appointment)
                        <option value="{{ $appointment->id }}" selected>{{ $appointment->client->name ?? '' }} — {{ $appointment->start_at->format('Y-m-d H:i') }}</option>
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('messages.clients') }} <span class="text-danger">*</span></label>
                <select name="client_id" id="client_id" class="form-select no-select2 js-client-select" required>
                    <option value="">—</option>
                    @if($appointment?->client)
                        <option value="{{ $appointment->client_id }}" selected>{{ $appointment->client->name }} ({{ $appointment->client->phone }})</option>
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('messages.employees') }}</label>
                <select name="employee_id" class="form-select">
                    <option value="">—</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ ($appointment?->employee_id) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('messages.default_line_employee_hint') }}</small>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('messages.select_currency') }} <span class="text-danger">*</span></label>
                <select name="currency_id" id="currencySelect" class="form-select" required>
                    @foreach($currencies as $cur)
                        <option value="{{ $cur->id }}" data-code="{{ $cur->code }}" {{ old('currency_id', \App\Models\Currency::default()?->id) == $cur->id ? 'selected' : '' }}>{{ $cur->code }} ({{ $cur->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('messages.field_discount') }}</label>
                <input type="number" step="0.01" min="0" name="discount_amount" value="0" class="form-control" id="discountInput">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('messages.tax') }}</label>
                <input type="number" step="0.01" min="0" name="tax_amount" value="0" class="form-control" id="taxInput">
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
        <h2 class="panel-card-title">{{ __('messages.field_description') }}</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn-outline-sm" id="addServiceBtn"><i class="bi bi-scissors"></i> {{ __('messages.services') }}</button>
            <button type="button" class="btn-outline-sm" id="addProductBtn"><i class="bi bi-box-seam"></i> {{ __('messages.products') }}</button>
        </div>
    </div>
    <div class="panel-card-body p-0">
        <div class="table-responsive">
            <table class="data-table" id="itemsTable">
                <thead><tr><th>{{ __('messages.field_description') }}</th><th style="width:110px">{{ __('messages.field_quantity') }}</th><th style="width:130px">{{ __('messages.field_price') }}</th><th style="width:160px">{{ __('messages.employees') }}</th><th style="width:110px">{{ __('messages.field_total') }}</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="panel-card-body border-top text-end">
        <div>{{ __('messages.field_total') }} ({{ __('messages.subtotal') }}): <span id="subtotalDisplay">0.00</span></div>
        <div class="fs-5 fw-bold">{{ __('messages.field_total') }}: <span id="grandTotal">0.00</span> <span id="grandTotalCurrency">{{ $currencies->firstWhere('id', \App\Models\Currency::default()?->id)?->code }}</span></div>
    </div>
</div>

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="mark_paid" name="mark_paid" value="1">
            <label class="form-check-label" for="mark_paid">{{ __('messages.mark_paid_now') }}</label>
        </div>
        <div id="paymentMethodWrap" class="mt-2 d-none">
            <label class="form-label">{{ __('messages.field_payment_method') }}</label>
            <select name="payment_method" class="form-select" style="max-width:220px">
                <option value="cash">{{ __('messages.pm_cash') }}</option>
                <option value="card">{{ __('messages.pm_card') }}</option>
                <option value="transfer">{{ __('messages.pm_transfer') }}</option>
            </select>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 pb-4">
    <button type="submit" class="btn-primary-sm"><i class="bi bi-save"></i> {{ __('messages.Save') }}</button>
    <a href="{{ route('admin.invoice.index') }}" class="btn-outline-sm">{{ __('messages.Cancel') }}</a>
</div>
</form>

@push('scripts')
<script>
const svcData = @json($services->map(fn($s) => ['id'=>$s->id,'name'=>$s->name,'price'=>(float)$s->price]));
const prodData = @json($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'price'=>(float)$p->sale_price]));
const employees = @json($employees->map(fn($e) => ['id'=>$e->id,'name'=>$e->name]));
const prefillServices = @json($appointment ? $appointment->services->map(fn($s) => ['id'=>$s->service_id,'name'=>$s->service->name ?? '','price'=>(float)$s->price]) : []);

function addItemRow(type, data) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    const items = type === 'service' ? svcData : prodData;
    tr.innerHTML = `
        <td>
            <input type="hidden" name="item_type[]" value="${type}">
            <select name="item_id[]" class="form-select form-select-sm item-select" required>
                <option value="">—</option>
                ${items.map(it => `<option value="${it.id}" data-price="${it.price}">${it.name}</option>`).join('')}
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="item_qty[]" class="form-control form-control-sm qty-input" value="1"></td>
        <td><input type="number" step="0.01" min="0" name="item_price[]" class="form-control form-control-sm price-input" value="0"></td>
        <td><select name="item_employee[]" class="form-select form-select-sm">
            <option value="">—</option>
            ${employees.map(e => `<option value="${e.id}">${e.name}</option>`).join('')}
        </select></td>
        <td><span class="row-total">0.00</span></td>
        <td><button type="button" class="btn-icon-sm btn-delete remove-row"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);

    const select = tr.querySelector('.item-select');
    const qty = tr.querySelector('.qty-input');
    const price = tr.querySelector('.price-input');
    const rowTotal = tr.querySelector('.row-total');

    function recalc() {
        const t = (parseFloat(qty.value)||0) * (parseFloat(price.value)||0);
        rowTotal.textContent = t.toFixed(2);
        recalcGrand();
    }
    function onSelectChange() {
        const opt = select.options[select.selectedIndex];
        price.value = opt.dataset.price || 0;
        recalc();
    }
    qty.addEventListener('input', recalc);
    price.addEventListener('input', recalc);

    tr.querySelector('.remove-row').addEventListener('click', function () { tr.remove(); recalcGrand(); });

    if (data) {
        select.value = data.id;
        price.value = data.price;
        recalc();
    }
    if (window.jQuery) {
        jQuery(select).select2({ theme: 'bootstrap-5', width: '100%' }).on('change', onSelectChange);
    } else {
        select.addEventListener('change', onSelectChange);
    }
}

function recalcGrand() {
    let sum = 0;
    document.querySelectorAll('.row-total').forEach(el => sum += parseFloat(el.textContent) || 0);
    document.getElementById('subtotalDisplay').textContent = sum.toFixed(2);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;
    document.getElementById('grandTotal').textContent = Math.max(sum - discount + tax, 0).toFixed(2);
}

document.getElementById('addServiceBtn').addEventListener('click', () => addItemRow('service'));
document.getElementById('addProductBtn').addEventListener('click', () => addItemRow('product'));
document.getElementById('discountInput').addEventListener('input', recalcGrand);
document.getElementById('taxInput').addEventListener('input', recalcGrand);
document.getElementById('mark_paid').addEventListener('change', function () {
    document.getElementById('paymentMethodWrap').classList.toggle('d-none', !this.checked);
});
function syncCurrencyLabel() {
    const select = document.getElementById('currencySelect');
    const opt = select.options[select.selectedIndex];
    document.getElementById('grandTotalCurrency').textContent = opt?.dataset.code || '';
}
document.getElementById('currencySelect').addEventListener('change', syncCurrencyLabel);
if (window.jQuery) jQuery('#currencySelect').on('change', syncCurrencyLabel);

if (prefillServices.length) {
    prefillServices.forEach(s => addItemRow('service', s));
} else {
    addItemRow('service');
}

if (window.jQuery) {
    jQuery('.js-client-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '{{ __('messages.clients') }}',
        minimumInputLength: 0,
        allowClear: true,
        ajax: {
            url: '{{ route('admin.client.search') }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data }),
        },
    });

    jQuery('.js-appointment-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '{{ __('messages.select_from_appointment') }}',
        minimumInputLength: 0,
        allowClear: true,
        ajax: {
            url: '{{ route('admin.invoice.appointments.search') }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data }),
        },
    }).on('change', function () {
        const appointmentId = this.value;
        if (!appointmentId) {
            document.getElementById('appointmentIdInput').value = '';
            return;
        }
        fetch(`/admin/invoice/appointments/${appointmentId}`)
            .then(r => r.json())
            .then(data => fillFromAppointment(appointmentId, data));
    });
}

function fillFromAppointment(appointmentId, data) {
    document.getElementById('appointmentIdInput').value = appointmentId;

    if (window.jQuery && data.client_id) {
        jQuery('.js-client-select').empty()
            .append(new Option(data.client_name, data.client_id, true, true))
            .trigger('change');
    }

    const employeeSelect = document.querySelector('select[name="employee_id"]');
    if (employeeSelect) {
        employeeSelect.value = data.employee_id || '';
    }

    document.getElementById('itemsBody').innerHTML = '';
    if (data.services && data.services.length) {
        data.services.forEach(s => addItemRow('service', s));
    } else {
        addItemRow('service');
    }
}
</script>
@endpush
@endsection
