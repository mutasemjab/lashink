<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('leave-table'))->only(['index']);
        $this->middleware($this->perm('leave-add'))->only(['create', 'store']);
        $this->middleware($this->perm('leave-edit'))->only(['approve', 'reject']);
        $this->middleware($this->perm('leave-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->employee_id, fn($q, $e) => $q->where('employee_id', $e))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $employees = Admin::where('is_super', false)->orderBy('name')->get();

        return view('admin.leave_request.index', compact('leaveRequests', 'employees'));
    }

    public function create()
    {
        $employees  = Admin::where('is_super', false)->orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.leave_request.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'    => 'required|exists:admins,id',
            'leave_type_id'  => 'required|exists:leave_types,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'nullable|string|max:500',
        ]);

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

        LeaveRequest::create($data + ['days' => $days, 'status' => 'pending']);

        return redirect()->route('admin.leave-request.index')->with('success', __('messages.saved_successfully'));
    }

    public function approve(LeaveRequest $leave_request)
    {
        $leave_request->update([
            'status'      => 'approved',
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
        ]);

        $year = $leave_request->start_date->year;
        $balance = LeaveBalance::firstOrCreate(
            ['employee_id' => $leave_request->employee_id, 'leave_type_id' => $leave_request->leave_type_id, 'year' => $year],
            ['allocated_days' => $leave_request->leaveType->default_days_per_year]
        );
        $balance->increment('used_days', $leave_request->days);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function reject(Request $request, LeaveRequest $leave_request)
    {
        $request->validate(['rejection_reason' => 'nullable|string|max:255']);

        $leave_request->update([
            'status'            => 'rejected',
            'approved_by'       => auth('admin')->id(),
            'approved_at'       => now(),
            'rejection_reason'  => $request->rejection_reason,
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(LeaveRequest $leave_request)
    {
        $leave_request->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
