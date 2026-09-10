<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('leave-table'))->only(['index']);
        $this->middleware($this->perm('leave-add'))->only(['store']);
        $this->middleware($this->perm('leave-edit'))->only(['update']);
        $this->middleware($this->perm('leave-delete'))->only(['destroy']);
    }

    public function index()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.leave_type.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:100',
            'default_days_per_year'  => 'required|integer|min:0',
        ]);
        $data['is_paid'] = $request->boolean('is_paid', true);

        LeaveType::create($data);

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, LeaveType $leave_type)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:100',
            'default_days_per_year'  => 'required|integer|min:0',
        ]);
        $data['is_paid'] = $request->boolean('is_paid');

        $leave_type->update($data);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(LeaveType $leave_type)
    {
        $leave_type->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
