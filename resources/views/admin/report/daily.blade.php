@extends('admin.layouts.app')
@section('title', __('messages.daily_accounts'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.daily_accounts') }}</h1></div>
    <div class="d-flex gap-2">
        @can('invoice-add')
        <button type="button" class="btn-outline-sm" onclick="openCreateInvoiceModal()">
            <i class="bi bi-receipt"></i> {{ __('messages.invoices') }} — {{ __('messages.add_new') }}
        </button>
        @endcan
        @can('appointment-add')
        <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="bi bi-plus-lg"></i> {{ __('messages.add_appointment') }}
        </button>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card mb-3">
    <div class="panel-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <a href="{{ route('admin.report.daily', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="btn-outline-sm"><i class="bi bi-chevron-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"></i></a>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">{{ __('messages.field_date') }}</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.report.daily', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="btn-outline-sm"><i class="bi bi-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i></a>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.report.daily') }}" class="btn-outline-sm">{{ __('messages.today') }}</a>
            </div>
            <div class="col-auto ms-auto">
                <button type="submit" class="btn-primary-sm"><i class="bi bi-search"></i> {{ __('messages.filter') }}</button>
            </div>
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
                        <th>{{ __('messages.invoice_number') }}</th>
                        <th>{{ __('messages.clients') }}</th>
                        <th>{{ __('messages.field_phone') }}</th>
                        <th>{{ __('messages.services') }}</th>
                        <th>{{ __('messages.employees') }}</th>
                        <th>{{ __('messages.field_payment_method') }}</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.field_total') }}</th>
                        @canany(['appointment-edit', 'invoice-table'])
                        <th>{{ __('messages.Actions') }}</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td class="fw-semibold">{{ $invoice->client->name ?? '-' }}</td>
                        <td>{{ $invoice->client->phone ?? '-' }}</td>
                        <td>
                            @forelse($invoice->items->where('item_type', 'service') as $item)
                                <span class="pill pill-info">{{ $item->service->name ?? $item->description }}</span>
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>
                            {{ $invoice->items->pluck('employee.name')->filter()->unique()->implode(', ') ?: '-' }}
                        </td>
                        <td>
                            {{ $invoice->payments->pluck('method')->unique()->map(fn($m) => __('messages.pm_' . $m))->implode(', ') ?: '-' }}
                        </td>
                        <td>
                            @if($invoice->payment_status === 'partial')<span class="pill pill-warning">{{ __('messages.ps_partial') }}</span>
                            @else<span class="pill pill-danger">{{ __('messages.ps_unpaid') }}</span>@endif
                        </td>
                        <td class="fw-semibold">{{ number_format($invoice->total, 2) }} {{ $invoice->currency->code ?? '' }}</td>
                        @canany(['appointment-edit', 'invoice-table'])
                        <td>
                            <div class="d-flex gap-1">
                                @can('invoice-table')
                                <button type="button" class="btn-icon-sm" onclick="openInvoiceModal({{ $invoice->id }})"><i class="bi bi-receipt"></i></button>
                                @endcan
                                @can('appointment-edit')
                                @if($invoice->appointment_id)
                                <button type="button" class="btn-icon-sm btn-edit" onclick="openEditModal({{ $invoice->appointment_id }})"><i class="bi bi-pencil"></i></button>
                                @endif
                                @endcan
                            </div>
                        </td>
                        @endcanany
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">{{ __('messages.no_records') }}</td></tr>
                    @endforelse
                </tbody>
                @if($invoices->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">{{ __('messages.field_total') }}</th>
                        <th colspan="2">
                            @foreach($invoices->groupBy(fn($i) => $i->currency->code ?? '') as $code => $group)
                                <div>{{ number_format($group->sum('total'), 2) }} {{ $code }}</div>
                            @endforeach
                        </th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@can('appointment-edit')
{{-- ── Edit Appointment Modal (same as appointments page) ────────── --}}
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.appointment_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAppointmentForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editAppointmentBody">
                    @include('admin.appointment._form', ['prefix' => 'edit_'])
                </div>
                <div class="modal-footer flex-wrap justify-content-between">
                    <div class="d-flex gap-1 flex-wrap" id="statusActions"></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="#" class="btn-outline-sm" id="invoiceActionBtn" target="_blank"></a>
                        <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
                    </div>
                </div>
            </form>
            <form id="statusAppointmentForm" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="status" id="statusInput">
            </form>
        </div>
    </div>
</div>
@endcan

@can('invoice-table')
{{-- ── Invoice Details Modal (same actions as the invoice page) ──── --}}
<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalTitle">{{ __('messages.invoice_number') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoiceModalBody"></div>
        </div>
    </div>
</div>
@endcan

@can('appointment-add')
{{-- ── Add Appointment Modal (same as appointments page) ──────────── --}}
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="addAppointmentForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_appointment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="addAppointmentErrors"></div>
                @include('admin.appointment._form', ['prefix' => 'add_', 'showStaff' => false])

                @can('invoice-add')
                <div class="form-check mt-2 border-top pt-3">
                    <input type="checkbox" class="form-check-input" id="alsoCreateInvoice">
                    <label class="form-check-label" for="alsoCreateInvoice">{{ __('messages.also_create_invoice') }}</label>
                </div>
                <div id="apptInvoiceSection" class="d-none mt-3">
                    @include('admin.report._invoice_items_section', ['currencies' => $currencies, 'employees' => $employees])
                </div>
                @endcan
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('invoice-add')
{{-- ── Create Invoice Modal (standalone, or for an appointment without one yet) ── --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="createInvoiceForm" class="modal-content">
            <input type="hidden" name="appointment_id" id="ciAppointmentId">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.invoices') }} — {{ __('messages.add_new') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="createInvoiceErrors"></div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.clients') }}</label>
                        <select name="client_id" id="ciClientId" class="form-select no-select2 js-client-select" required>
                            <option value="">—</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.employees') }}</label>
                        <select name="employee_id" id="ciEmployeeId" class="form-select no-select2">
                            <option value="">—</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="ciItemsSection">
                    @include('admin.report._invoice_items_section', ['currencies' => $currencies, 'employees' => $employees])
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endcan

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.js-time-picker').forEach(function (el) {
        flatpickr(el, {
            enableTime: true, noCalendar: true, dateFormat: 'H:i',
            altInput: true, altFormat: 'h:i K', time_24hr: false, minuteIncrement: 5,
        });
    });

    function setTimeValue(input, timeStr) {
        if (input && input._flatpickr) { input._flatpickr.setDate(timeStr || null, true); }
        else if (input) { input.value = timeStr || ''; }
    }

    if (window.jQuery) {
        const dir = document.documentElement.getAttribute('dir') || 'ltr';
        function initAjaxSelect(selector, url, placeholder) {
            jQuery(selector).each(function () {
                jQuery(this).select2({
                    theme: 'bootstrap-5', dir: dir, width: '100%', placeholder: placeholder,
                    minimumInputLength: 0, allowClear: true,
                    dropdownParent: jQuery(this).closest('.modal'),
                    ajax: {
                        url: url, dataType: 'json', delay: 250,
                        data: params => ({ q: params.term || '' }),
                        processResults: data => ({ results: data }),
                    },
                });
            });
        }
        initAjaxSelect('.js-client-select', '{{ route('admin.appointment.clients.search') }}', '{{ __('messages.clients') }}');
        initAjaxSelect('.js-staff-select', '{{ route('admin.appointment.staff.search') }}', '{{ __('messages.employees') }}');
    }

    const statusLabels = {
        pending: '{{ __('messages.appt_status_pending') }}',
        confirmed: '{{ __('messages.appt_status_confirmed') }}',
        completed: '{{ __('messages.appt_status_completed') }}',
        cancelled: '{{ __('messages.appt_status_cancelled') }}',
        no_show: '{{ __('messages.appt_status_no_show') }}',
    };

    window.openEditModal = function (appointmentId) {
        fetch(`{{ route('admin.appointment.events') }}?id=${appointmentId}`)
            .then(r => r.json())
            .then(list => {
                const event = list[0];
                if (!event) return;
                const p = event.extendedProps;
                const form = document.getElementById('editAppointmentForm');
                form.action = `/admin/appointment/${event.id}`;
                document.getElementById('statusAppointmentForm').action = `/admin/appointment/${event.id}/status`;

                function setAjaxSelectValue(select, id, text) {
                    const $select = jQuery(select);
                    $select.empty();
                    if (id) { $select.append(new Option(text, id, true, true)); }
                    $select.trigger('change');
                }

                if (window.jQuery) {
                    setAjaxSelectValue(form.querySelector('[name="client_id"]'), p.client_id, p.client_name);
                    setAjaxSelectValue(form.querySelector('[name="employee_id"]'), p.employee_id, p.employee_name);
                } else {
                    form.querySelector('[name="client_id"]').value = p.client_id;
                    form.querySelector('[name="employee_id"]').value = p.employee_id;
                }

                const [editDatePart, editTimePart] = event.start.slice(0, 16).split('T');
                form.querySelector('[name="appt_date"]').value = editDatePart;
                setTimeValue(form.querySelector('[name="appt_time"]'), editTimePart || '');
                form.querySelector('[name="notes"]').value = p.notes || '';
                const svcSelect = form.querySelector('[name="services[]"]');
                Array.from(svcSelect.options).forEach(o => o.selected = p.services.includes(parseInt(o.value)));
                if (window.jQuery) { jQuery(svcSelect).trigger('change'); }

                const statusActions = document.getElementById('statusActions');
                statusActions.innerHTML = '';
                Object.keys(statusLabels).forEach(function (st) {
                    if (st === p.status) return;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-outline-sm btn-sm';
                    btn.textContent = statusLabels[st];
                    btn.onclick = function () {
                        document.getElementById('statusInput').value = st;
                        document.getElementById('statusAppointmentForm').submit();
                    };
                    statusActions.appendChild(btn);
                });

                const invoiceActionBtn = document.getElementById('invoiceActionBtn');
                if (invoiceActionBtn) {
                    if (p.invoice_id) {
                        invoiceActionBtn.textContent = '{{ __('messages.view_invoice') }}';
                        invoiceActionBtn.href = '#';
                        invoiceActionBtn.onclick = function (e) {
                            e.preventDefault();
                            if (typeof openInvoiceModal === 'function') openInvoiceModal(p.invoice_id);
                        };
                    } else {
                        invoiceActionBtn.textContent = '{{ __('messages.create_invoice') }}';
                        invoiceActionBtn.href = '#';
                        invoiceActionBtn.onclick = function (e) {
                            e.preventDefault();
                            if (typeof openCreateInvoiceModal === 'function') openCreateInvoiceModal(event.id);
                        };
                    }
                }

                new bootstrap.Modal(document.getElementById('viewAppointmentModal')).show();
            });
    };

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }

    const pmLabels = {
        cash: '{{ __('messages.pm_cash') }}',
        card: '{{ __('messages.pm_card') }}',
        transfer: '{{ __('messages.pm_transfer') }}',
        other: '{{ __('messages.pm_other') }}',
    };

    window.openInvoiceModal = function (invoiceId) {
        fetch(`/admin/invoice/${invoiceId}/details`)
            .then(r => r.json())
            .then(inv => {
                document.getElementById('invoiceModalTitle').textContent = inv.invoice_number + ' — ' + inv.client_name;

                const itemsRows = inv.items.map(i => `
                    <tr>
                        <td>${escapeHtml(i.description)}</td>
                        <td>${i.quantity.toFixed(2)}</td>
                        <td>${i.unit_price.toFixed(2)}</td>
                        <td>${i.total.toFixed(2)}</td>
                    </tr>
                `).join('');

                const paymentsRows = inv.payments.length ? inv.payments.map(p => `
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>${p.amount.toFixed(2)} ${escapeHtml(inv.currency)} — ${escapeHtml(pmLabels[p.method] || p.method)}</span>
                        <span class="text-muted small">${escapeHtml(p.paid_at)}</span>
                    </div>
                `).join('') : `<p class="text-muted small mb-0">{{ __('messages.no_records') }}</p>`;

                const paymentFormHtml = (inv.can_edit && inv.remaining_amount > 0 && inv.status !== 'cancelled') ? `
                    <div class="border-top pt-3 mt-2">
                        <label class="form-label">{{ __('messages.field_amount') }}</label>
                        <input type="number" step="0.01" min="0.01" max="${inv.remaining_amount}" id="invPaymentAmount" value="${inv.remaining_amount}" class="form-control mb-2">
                        <label class="form-label">{{ __('messages.field_payment_method') }}</label>
                        <select id="invPaymentMethod" class="form-select mb-2">
                            <option value="cash">{{ __('messages.pm_cash') }}</option>
                            <option value="card">{{ __('messages.pm_card') }}</option>
                            <option value="transfer">{{ __('messages.pm_transfer') }}</option>
                        </select>
                        <button type="button" class="btn-primary-sm w-100" id="invAddPaymentBtn">{{ __('messages.add_payment') }}</button>
                    </div>
                ` : '';

                const cancelBtnHtml = (inv.can_edit && inv.status !== 'cancelled') ? `
                    <button type="button" class="btn-outline-sm text-danger w-100 mt-2" id="invCancelBtn">{{ __('messages.cancel_invoice') }}</button>
                ` : '';

                document.getElementById('invoiceModalBody').innerHTML = `
                    <div class="alert alert-danger d-none" id="invActionErrors"></div>
                    <div class="row g-4">
                        <div class="col-12 col-lg-7">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead><tr><th>{{ __('messages.field_description') }}</th><th>{{ __('messages.field_quantity') }}</th><th>{{ __('messages.field_price') }}</th><th>{{ __('messages.field_total') }}</th></tr></thead>
                                    <tbody>${itemsRows}</tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <div style="min-width:240px">
                                    <div class="d-flex justify-content-between"><span>{{ __('messages.subtotal') }}</span><span>${inv.subtotal.toFixed(2)}</span></div>
                                    <div class="d-flex justify-content-between"><span>{{ __('messages.field_discount') }}</span><span>-${inv.discount_amount.toFixed(2)}</span></div>
                                    <div class="d-flex justify-content-between"><span>{{ __('messages.tax') }}</span><span>${inv.tax_amount.toFixed(2)}</span></div>
                                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>{{ __('messages.field_total') }}</span><span>${inv.total.toFixed(2)} ${escapeHtml(inv.currency)}</span></div>
                                    <div class="d-flex justify-content-between text-success"><span>{{ __('messages.paid_amount') }}</span><span>${inv.paid_amount.toFixed(2)}</span></div>
                                    <div class="d-flex justify-content-between text-danger"><span>{{ __('messages.remaining_amount') }}</span><span>${inv.remaining_amount.toFixed(2)}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <h6>{{ __('messages.payments') }}</h6>
                            ${paymentsRows}
                            ${paymentFormHtml}
                            ${cancelBtnHtml}
                            <a href="${inv.print_url}" target="_blank" class="btn-outline-sm w-100 mt-2 d-block text-center"><i class="bi bi-printer"></i> {{ __('messages.print') }}</a>
                        </div>
                    </div>
                `;

                const invErrBox = document.getElementById('invActionErrors');

                const addPaymentBtn = document.getElementById('invAddPaymentBtn');
                if (addPaymentBtn) {
                    addPaymentBtn.onclick = function () {
                        const amount = document.getElementById('invPaymentAmount').value;
                        const method = document.getElementById('invPaymentMethod').value;
                        invErrBox.classList.add('d-none');
                        addPaymentBtn.disabled = true;
                        fetch(inv.payment_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({ amount, method }),
                        }).then(r => {
                            if (r.ok) { location.reload(); return; }
                            r.json().then(data => showFormErrors(invErrBox, data.errors || {})).catch(() => showFormErrors(invErrBox, {}));
                            addPaymentBtn.disabled = false;
                        }).catch(() => { showFormErrors(invErrBox, {}); addPaymentBtn.disabled = false; });
                    };
                }

                const cancelBtn = document.getElementById('invCancelBtn');
                if (cancelBtn) {
                    cancelBtn.onclick = function () {
                        if (!confirm('{{ __('messages.confirm_cancel_invoice') }}')) return;
                        invErrBox.classList.add('d-none');
                        cancelBtn.disabled = true;
                        fetch(inv.cancel_url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        }).then(r => {
                            if (r.ok) { location.reload(); return; }
                            r.json().then(data => showFormErrors(invErrBox, data.errors || {})).catch(() => showFormErrors(invErrBox, {}));
                            cancelBtn.disabled = false;
                        }).catch(() => { showFormErrors(invErrBox, {}); cancelBtn.disabled = false; });
                    };
                }

                new bootstrap.Modal(document.getElementById('invoiceModal')).show();
            });
    };

    const genericErrorMsg = '{{ __('messages.generic_error') }}';

    function showFormErrors(container, errors) {
        const msgs = Object.values(errors).flat();
        container.innerHTML = (msgs.length ? msgs : [genericErrorMsg]).map(escapeHtml).join('<br>');
        container.classList.remove('d-none');
    }

    function submitFormViaFetch(form, url, errBox) {
        errBox.classList.add('d-none');
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: new FormData(form),
        }).then(r => {
            if (r.ok) { location.reload(); return; }
            r.json()
                .then(data => showFormErrors(errBox, data.errors || {}))
                .catch(() => showFormErrors(errBox, {}));
        }).catch(() => {
            showFormErrors(errBox, {});
        }).finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
    }

    // ── Generic items-table controller, shared by both invoice sections ──
    const svcData = @json($services->map(fn($s) => ['id'=>$s->id,'name'=>$s->name,'price'=>(float)$s->price]));
    const prodData = @json($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'price'=>(float)$p->sale_price]));

    function setupItemsSection(root) {
        const body = root.querySelector('[data-role="items-body"]');
        const addServiceBtn = root.querySelector('[data-role="add-service"]');
        const addProductBtn = root.querySelector('[data-role="add-product"]');
        const subtotalEl = root.querySelector('[data-role="subtotal"]');
        const grandTotalEl = root.querySelector('[data-role="grand-total"]');
        const grandCurrencyEl = root.querySelector('[data-role="grand-total-currency"]');
        const discountEl = root.querySelector('[data-role="discount"]');
        const taxEl = root.querySelector('[data-role="tax"]');
        const currencySelect = root.querySelector('[data-role="currency-select"]');
        const markPaid = root.querySelector('[data-role="mark-paid"]');
        const paymentWrap = root.querySelector('[data-role="payment-method-wrap"]');
        const employeeTemplate = root.querySelector('[data-role="employee-options"]');
        const employeeOptionsHtml = employeeTemplate ? employeeTemplate.innerHTML : '<option value="">—</option>';

        function recalcGrand() {
            let sum = 0;
            body.querySelectorAll('.row-total').forEach(el => sum += parseFloat(el.textContent) || 0);
            subtotalEl.textContent = sum.toFixed(2);
            const discount = parseFloat(discountEl?.value) || 0;
            const tax = parseFloat(taxEl?.value) || 0;
            grandTotalEl.textContent = Math.max(sum - discount + tax, 0).toFixed(2);
        }

        function addItemRow(type, data) {
            const tr = document.createElement('tr');
            const items = type === 'service' ? svcData : prodData;
            tr.innerHTML = `
                <td>
                    <input type="hidden" name="item_type[]" value="${type}">
                    <select name="item_id[]" class="form-select form-select-sm item-select no-select2" required>
                        <option value="">—</option>
                        ${items.map(it => `<option value="${it.id}" data-price="${it.price}">${escapeHtml(it.name)}</option>`).join('')}
                    </select>
                </td>
                <td><input type="number" step="0.01" min="0.01" name="item_qty[]" class="form-control form-control-sm qty-input" value="1"></td>
                <td><input type="number" step="0.01" min="0" name="item_price[]" class="form-control form-control-sm price-input" value="0"></td>
                <td><select name="item_employee[]" class="form-select form-select-sm no-select2">${employeeOptionsHtml}</select></td>
                <td><span class="row-total">0.00</span></td>
                <td><button type="button" class="btn-icon-sm btn-delete remove-row"><i class="bi bi-trash"></i></button></td>
            `;
            body.appendChild(tr);

            const select = tr.querySelector('.item-select');
            const qty = tr.querySelector('.qty-input');
            const price = tr.querySelector('.price-input');
            const rowTotal = tr.querySelector('.row-total');

            function recalcRow() {
                rowTotal.textContent = ((parseFloat(qty.value) || 0) * (parseFloat(price.value) || 0)).toFixed(2);
                recalcGrand();
            }
            function onSelectChange() {
                const opt = select.options[select.selectedIndex];
                price.value = opt.dataset.price || 0;
                recalcRow();
            }
            qty.addEventListener('input', recalcRow);
            price.addEventListener('input', recalcRow);
            tr.querySelector('.remove-row').addEventListener('click', function () { tr.remove(); recalcGrand(); });

            if (data) { select.value = data.id; price.value = data.price; recalcRow(); }
            if (window.jQuery) {
                jQuery(select).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: jQuery(root).closest('.modal') }).on('change', onSelectChange);
            } else {
                select.addEventListener('change', onSelectChange);
            }
        }

        if (addServiceBtn) addServiceBtn.addEventListener('click', () => addItemRow('service'));
        if (addProductBtn) addProductBtn.addEventListener('click', () => addItemRow('product'));
        if (discountEl) discountEl.addEventListener('input', recalcGrand);
        if (taxEl) taxEl.addEventListener('input', recalcGrand);
        if (markPaid && paymentWrap) markPaid.addEventListener('change', function () {
            paymentWrap.classList.toggle('d-none', !this.checked);
        });
        function syncCurrencyLabel() {
            const opt = currencySelect.options[currencySelect.selectedIndex];
            if (grandCurrencyEl) grandCurrencyEl.textContent = opt?.dataset.code || '';
        }
        if (currencySelect) { currencySelect.addEventListener('change', syncCurrencyLabel); syncCurrencyLabel(); }

        return {
            addItemRow,
            reset() { body.innerHTML = ''; recalcGrand(); },
        };
    }

    // ── Add Appointment (inline, stays on this page) ────────────────
    const addAppointmentForm = document.getElementById('addAppointmentForm');
    const apptInvoiceSection = document.getElementById('apptInvoiceSection');
    const apptItems = apptInvoiceSection ? setupItemsSection(apptInvoiceSection) : null;

    if (addAppointmentForm) {
        addAppointmentForm.querySelector('[name="appt_date"]').value = '{{ $date->format('Y-m-d') }}';

        const alsoCreateInvoice = document.getElementById('alsoCreateInvoice');
        if (alsoCreateInvoice && apptInvoiceSection) {
            alsoCreateInvoice.addEventListener('change', function () {
                apptInvoiceSection.classList.toggle('d-none', !this.checked);
                if (this.checked && apptItems && !apptInvoiceSection.querySelector('[data-role="items-body"]').children.length) {
                    apptItems.addItemRow('service');
                }
            });
        }

        addAppointmentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const errBox = document.getElementById('addAppointmentErrors');
            errBox.classList.add('d-none');
            const submitBtn = addAppointmentForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch('{{ route('admin.appointment.store') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(addAppointmentForm),
            }).then(r => {
                if (!r.ok) {
                    r.json().then(data => showFormErrors(errBox, data.errors || {})).catch(() => showFormErrors(errBox, {}));
                    if (submitBtn) submitBtn.disabled = false;
                    return null;
                }
                return r.json();
            }).then(apptData => {
                if (!apptData) return;

                if (alsoCreateInvoice && alsoCreateInvoice.checked && apptInvoiceSection) {
                    const invoiceFormData = new FormData();
                    invoiceFormData.append('client_id', apptData.client_id);
                    invoiceFormData.append('appointment_id', apptData.id);
                    apptInvoiceSection.querySelectorAll('[name]').forEach(el => {
                        if (el.type === 'checkbox') { if (el.checked) invoiceFormData.append(el.name, el.value); }
                        else { invoiceFormData.append(el.name, el.value); }
                    });

                    fetch('{{ route('admin.invoice.store') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: invoiceFormData,
                    }).then(r2 => {
                        if (r2.ok) { location.reload(); return; }
                        r2.json().then(data => showFormErrors(errBox, data.errors || {})).catch(() => showFormErrors(errBox, {}));
                        if (submitBtn) submitBtn.disabled = false;
                    }).catch(() => { showFormErrors(errBox, {}); if (submitBtn) submitBtn.disabled = false; });
                } else {
                    location.reload();
                }
            }).catch(() => { showFormErrors(errBox, {}); if (submitBtn) submitBtn.disabled = false; });
        });
    }

    // ── Create Invoice (standalone walk-in, or for an appointment) ───
    const ciItemsSection = document.getElementById('ciItemsSection');
    const ciItems = ciItemsSection ? setupItemsSection(ciItemsSection) : null;

    window.openCreateInvoiceModal = function (appointmentId) {
        document.getElementById('ciAppointmentId').value = appointmentId || '';
        document.getElementById('createInvoiceErrors').classList.add('d-none');
        if (ciItems) ciItems.reset();

        const show = () => new bootstrap.Modal(document.getElementById('createInvoiceModal')).show();

        if (window.jQuery) {
            jQuery('#ciClientId').empty().trigger('change');
        }
        document.getElementById('ciEmployeeId').value = '';

        if (!appointmentId) {
            if (ciItems) ciItems.addItemRow('service');
            show();
            return;
        }

        fetch(`/admin/invoice/appointments/${appointmentId}`)
            .then(r => r.json())
            .then(data => {
                if (window.jQuery && data.client_id) {
                    jQuery('#ciClientId').empty().append(new Option(data.client_name, data.client_id, true, true)).trigger('change');
                }
                document.getElementById('ciEmployeeId').value = data.employee_id || '';

                if (ciItems) {
                    if (data.services && data.services.length) {
                        data.services.forEach(s => ciItems.addItemRow('service', s));
                    } else {
                        ciItems.addItemRow('service');
                    }
                }

                show();
            });
    };

    const createInvoiceForm = document.getElementById('createInvoiceForm');
    if (createInvoiceForm) {
        createInvoiceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitFormViaFetch(createInvoiceForm, '{{ route('admin.invoice.store') }}', document.getElementById('createInvoiceErrors'));
        });
    }
});
</script>
@endpush

@endsection
