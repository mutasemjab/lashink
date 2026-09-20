<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('attendance-table'))->only(['index']);
        $this->middleware($this->perm('attendance-add'))->only(['store']);
        $this->middleware($this->perm('attendance-edit'))->only(['update']);
        $this->middleware($this->perm('attendance-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $attendances = Attendance::with('employee')
            ->when($request->employee_id, fn($q, $e) => $q->where('employee_id', $e))
            ->when($request->from_date, fn($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->to_date, fn($q, $d) => $q->whereDate('date', '<=', $d))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $employees = Admin::where('is_super', false)->orderBy('name')->get();

        return view('admin.attendance.index', compact('attendances', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAttendance($request);

        $computed = $this->computeLateAndOvertime($data['date'], $data['check_in'], $data['check_out']);

        Attendance::create([
            'employee_id'      => $data['employee_id'],
            'date'             => $data['date'],
            'check_in'         => Carbon::parse($data['date'] . ' ' . $data['check_in']),
            'check_out'        => Carbon::parse($data['date'] . ' ' . $data['check_out']),
            'late_minutes'     => $computed['late_minutes'],
            'overtime_minutes' => $computed['overtime_minutes'],
            'notes'            => $data['notes'] ?? null,
            'created_by'       => auth('admin')->id(),
        ]);

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $this->validateAttendance($request, $attendance);

        $computed = $this->computeLateAndOvertime($data['date'], $data['check_in'], $data['check_out']);

        $attendance->update([
            'employee_id'      => $data['employee_id'],
            'date'             => $data['date'],
            'check_in'         => Carbon::parse($data['date'] . ' ' . $data['check_in']),
            'check_out'        => Carbon::parse($data['date'] . ' ' . $data['check_out']),
            'late_minutes'     => $computed['late_minutes'],
            'overtime_minutes' => $computed['overtime_minutes'],
            'notes'            => $data['notes'] ?? null,
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', __('messages.deleted_successfully'));
    }

    private function validateAttendance(Request $request, ?Attendance $attendance = null): array
    {
        return $request->validate([
            'employee_id' => 'required|exists:admins,id',
            'date'        => [
                'required',
                'date',
                Rule::unique('attendances')
                    ->where(fn($q) => $q->where('employee_id', $request->employee_id)->where('date', $request->date))
                    ->ignore($attendance?->id),
            ],
            'check_in'  => 'required|date_format:H:i',
            'check_out' => 'required|date_format:H:i|after:check_in',
            'notes'     => 'nullable|string|max:500',
        ], [
            'date.unique' => __('messages.attendance_already_recorded'),
        ]);
    }

    private function computeLateAndOvertime(string $date, string $checkIn, string $checkOut): array
    {
        $shiftStart = Carbon::parse($date . ' ' . Setting::get('shift_start_time', '09:00'));
        $shiftEnd   = Carbon::parse($date . ' ' . Setting::get('shift_end_time', '18:00'));
        $checkInAt  = Carbon::parse($date . ' ' . $checkIn);
        $checkOutAt = Carbon::parse($date . ' ' . $checkOut);

        return [
            'late_minutes'     => $checkInAt->gt($shiftStart) ? $checkInAt->diffInMinutes($shiftStart) : 0,
            'overtime_minutes' => $checkOutAt->gt($shiftEnd) ? $checkOutAt->diffInMinutes($shiftEnd) : 0,
        ];
    }
}
