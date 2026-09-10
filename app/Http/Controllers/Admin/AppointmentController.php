<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('appointment-table'))->only(['index', 'events']);
        $this->middleware($this->perm('appointment-add'))->only(['store']);
        $this->middleware($this->perm('appointment-edit'))->only(['update', 'reschedule', 'updateStatus']);
        $this->middleware($this->perm('appointment-delete'))->only(['destroy']);
    }

    public function index()
    {
        $clients    = Client::where('is_blocked', false)->orderBy('name')->get();
        $employees  = Admin::where('is_super', false)->where('employment_status', 'active')->orderBy('name')->get();
        $services   = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.appointment.index', compact('clients', 'employees', 'services'));
    }

    /**
     * JSON feed consumed by FullCalendar.
     */
    public function events(Request $request)
    {
        $appointments = Appointment::with(['client', 'employee', 'services.service'])
            ->when($request->start, fn($q, $s) => $q->where('end_at', '>=', $s))
            ->when($request->end, fn($q, $e) => $q->where('start_at', '<=', $e))
            ->get();

        $colors = [
            'pending'   => '#f59e0b',
            'confirmed' => '#2563eb',
            'completed' => '#16a34a',
            'cancelled' => '#94a3b8',
            'no_show'   => '#dc2626',
        ];

        $events = $appointments->map(function (Appointment $a) use ($colors) {
            $serviceNames = $a->services->map(fn($s) => $s->service->name ?? '')->implode('، ');
            return [
                'id'    => $a->id,
                'title' => $a->client->name . ' — ' . $serviceNames,
                'start' => $a->start_at->toIso8601String(),
                'end'   => $a->end_at->toIso8601String(),
                'color' => $colors[$a->status] ?? '#64748b',
                'extendedProps' => [
                    'client_id'   => $a->client_id,
                    'employee_id' => $a->employee_id,
                    'status'      => $a->status,
                    'notes'       => $a->notes,
                    'services'    => $a->services->pluck('service_id'),
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $data = $this->validateAppointment($request);

        DB::transaction(function () use ($data, $request) {
            $appointment = Appointment::create([
                'client_id'   => $data['client_id'],
                'employee_id' => $data['employee_id'],
                'status'      => 'pending',
                'start_at'    => $data['start_at'],
                'end_at'      => $data['end_at'],
                'notes'       => $data['notes'] ?? null,
                'created_by'  => auth('admin')->id(),
            ]);

            $this->syncServices($appointment, $request->input('services', []));
        });

        return redirect()->route('admin.appointment.index')->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validateAppointment($request);

        DB::transaction(function () use ($appointment, $data, $request) {
            $appointment->update([
                'client_id'   => $data['client_id'],
                'employee_id' => $data['employee_id'],
                'start_at'    => $data['start_at'],
                'end_at'      => $data['end_at'],
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->syncServices($appointment, $request->input('services', []));
        });

        return redirect()->route('admin.appointment.index')->with('success', __('messages.updated_successfully'));
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $request->validate([
            'start_at' => 'required|date',
            'end_at'   => 'required|date|after:start_at',
        ]);

        $appointment->update($request->only('start_at', 'end_at'));

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Appointment::STATUSES),
            'cancelled_reason' => 'nullable|string|max:255',
        ]);

        $appointment->update([
            'status'           => $request->status,
            'cancelled_reason' => $request->status === 'cancelled' ? $request->cancelled_reason : null,
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }

    private function syncServices(Appointment $appointment, array $serviceIds): void
    {
        $appointment->services()->delete();

        $start = Carbon::parse($appointment->start_at);
        foreach ($serviceIds as $serviceId) {
            $service = Service::find($serviceId);
            if (!$service) {
                continue;
            }
            AppointmentService::create([
                'appointment_id'    => $appointment->id,
                'service_id'        => $service->id,
                'employee_id'       => $appointment->employee_id,
                'price'             => $service->price,
                'duration_minutes'  => $service->duration_minutes,
            ]);
        }
    }

    private function validateAppointment(Request $request): array
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'employee_id' => 'required|exists:admins,id',
            'start_at'    => 'required|date',
            'services'    => 'required|array|min:1',
            'services.*'  => 'exists:services,id',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $totalMinutes = Service::whereIn('id', $data['services'])->sum('duration_minutes');
        $start = Carbon::parse($data['start_at']);

        $data['start_at'] = $start;
        $data['end_at']   = $start->copy()->addMinutes(max((int) $totalMinutes, 15));

        return $data;
    }
}
