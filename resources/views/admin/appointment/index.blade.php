@extends('admin.layouts.app')
@section('title', __('messages.nav_appointments'))

@section('content')

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div><h1 class="page-title">{{ __('messages.nav_appointments') }}</h1></div>
    @can('appointment-add')
    <button type="button" class="btn-primary-sm" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
        <i class="bi bi-plus-lg"></i> {{ __('messages.add_appointment') }}
    </button>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="panel-card">
    <div class="panel-card-body">
        <div id="calendar"></div>
    </div>
</div>

{{-- ── Add Appointment Modal ───────────────────────────────────── --}}
<div class="modal fade" id="addAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.appointment.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_appointment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @include('admin.appointment._form', ['showStaff' => false])
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ── View / Edit Appointment Modal ───────────────────────────── --}}
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
                        @can('invoice-add')
                        <a href="#" class="btn-outline-sm" id="invoiceActionBtn" target="_blank"></a>
                        @endcan
                        @can('appointment-delete')
                        <button type="button" class="btn-outline-sm text-danger" id="deleteAppointmentBtn">{{ __('messages.Delete') }}</button>
                        @endcan
                        @can('appointment-edit')
                        <button type="submit" class="btn-primary-sm">{{ __('messages.Save') }}</button>
                        @endcan
                    </div>
                </div>
            </form>
            <form id="deleteAppointmentForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
            <form id="statusAppointmentForm" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="status" id="statusInput">
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
<style>
#calendar { direction: ltr; }
.fc-event { cursor: pointer; border: none; }
.fc {
    --fc-button-bg-color: #171717;
    --fc-button-border-color: #171717;
    --fc-button-hover-bg-color: #cb6984;
    --fc-button-hover-border-color: #cb6984;
    --fc-button-active-bg-color: #a94a67;
    --fc-button-active-border-color: #a94a67;
    --fc-today-bg-color: #fbeff3;
    --fc-now-indicator-color: #cb6984;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const canEdit = {{ auth('admin')->user()?->can('appointment-edit') ? 'true' : 'false' }};

    document.querySelectorAll('.js-time-picker').forEach(function (el) {
        flatpickr(el, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            altInput: true,
            altFormat: 'h:i K',
            time_24hr: false,
            minuteIncrement: 5,
        });
    });

    function setTimeValue(input, timeStr) {
        if (input && input._flatpickr) {
            input._flatpickr.setDate(timeStr || null, true);
        } else if (input) {
            input.value = timeStr || '';
        }
    }

    if (window.jQuery) {
        const dir = document.documentElement.getAttribute('dir') || 'ltr';

      
        function initAjaxSelect(selector, url, placeholder) {
            jQuery(selector).each(function () {
                jQuery(this).select2({
                    theme: 'bootstrap-5',
                    dir: dir,
                    width: '100%',
                    placeholder: placeholder,
                    // 0 (not 1) so opening the field with an empty box still fires the
                    // ajax call, which is what gives us the preload of first results.
                    minimumInputLength: 0,
                    allowClear: true,
                    dropdownParent: jQuery(this).closest('.modal'),
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
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

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: '{{ app()->getLocale() }}',
        direction: '{{ app()->getLocale() === "ar" ? "rtl" : "ltr" }}',
        headerToolbar: { start: 'prev,next today', center: 'title', end: 'dayGridMonth,timeGridWeek,timeGridDay' },
        initialView: 'timeGridWeek',
        slotMinTime: '08:00:00',
        slotMaxTime: '23:00:00',
        height: 'auto',
        editable: canEdit,
        eventStartEditable: canEdit,
        eventDurationEditable: canEdit,
        events: function (info, success, failure) {
            fetch(`{{ route('admin.appointment.events') }}?start=${info.startStr}&end=${info.endStr}`)
                .then(r => r.json()).then(success).catch(failure);
        },
        eventDrop: function (info) { reschedule(info.event); },
        eventResize: function (info) { reschedule(info.event); },
        eventClick: function (info) { openEditModal(info.event); },
        dateClick: function (info) {
            const [datePart, timePart] = info.dateStr.slice(0,16).split('T');
            document.querySelector('#addAppointmentModal input[name="appt_date"]').value = datePart;
            document.querySelector('#addAppointmentModal input[name="appt_time"]').value = timePart || '';
            new bootstrap.Modal(document.getElementById('addAppointmentModal')).show();
        },
    });
    calendar.render();

    function reschedule(event) {
        fetch(`/admin/appointment/${event.id}/reschedule`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ start_at: event.start.toISOString(), end_at: event.end.toISOString() }),
        }).then(r => { if (!r.ok) calendar.refetchEvents(); });
    }

    function openEditModal(event) {
        const p = event.extendedProps;
        const form = document.getElementById('editAppointmentForm');
        form.action = `/admin/appointment/${event.id}`;
        document.getElementById('deleteAppointmentForm').action = `/admin/appointment/${event.id}`;
        document.getElementById('statusAppointmentForm').action = `/admin/appointment/${event.id}/status`;

        
        function setAjaxSelectValue(select, id, text) {
            const $select = jQuery(select);
            $select.empty();
            if (id) {
                $select.append(new Option(text, id, true, true));
            }
            $select.trigger('change');
        }

        if (window.jQuery) {
            setAjaxSelectValue(form.querySelector('[name="client_id"]'), p.client_id, p.client_name);
            setAjaxSelectValue(form.querySelector('[name="employee_id"]'), p.employee_id, p.employee_name);
        } else {
            form.querySelector('[name="client_id"]').value = p.client_id;
            form.querySelector('[name="employee_id"]').value = p.employee_id;
        }

        const [editDatePart, editTimePart] = event.startStr.slice(0,16).split('T');
        form.querySelector('[name="appt_date"]').value = editDatePart;
        form.querySelector('[name="appt_time"]').value = editTimePart || '';
        form.querySelector('[name="notes"]').value = p.notes || '';
        const svcSelect = form.querySelector('[name="services[]"]');
        Array.from(svcSelect.options).forEach(o => o.selected = p.services.includes(parseInt(o.value)));
        if (window.jQuery) { jQuery(svcSelect).trigger('change'); }

        const statusActions = document.getElementById('statusActions');
        statusActions.innerHTML = '';
        if (canEdit) {
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
        }

        document.getElementById('deleteAppointmentBtn').onclick = function () {
            if (confirm('{{ __('messages.confirm_delete') }}')) document.getElementById('deleteAppointmentForm').submit();
        };

        const invoiceActionBtn = document.getElementById('invoiceActionBtn');
        if (invoiceActionBtn) {
            if (p.invoice_id) {
                invoiceActionBtn.textContent = '{{ __('messages.view_invoice') }}';
                invoiceActionBtn.href = `/admin/invoice/${p.invoice_id}`;
            } else {
                invoiceActionBtn.textContent = '{{ __('messages.create_invoice') }}';
                invoiceActionBtn.href = `/admin/invoice/create?appointment_id=${event.id}`;
            }
        }

        new bootstrap.Modal(document.getElementById('viewAppointmentModal')).show();
    }
});
</script>
@endpush

@endsection
