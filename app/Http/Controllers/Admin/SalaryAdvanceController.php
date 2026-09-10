<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SalaryAdvance;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('advance-table'))->only(['index']);
        $this->middleware($this->perm('advance-add'))->only(['create', 'store']);
        $this->middleware($this->perm('advance-edit'))->only(['approve', 'reject']);
        $this->middleware($this->perm('advance-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $advances = SalaryAdvance::with('employee')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.salary_advance.index', compact('advances'));
    }

    public function create()
    {
        $employees = Admin::where('is_super', false)->orderBy('name')->get();
        return view('admin.salary_advance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'          => 'required|exists:admins,id',
            'amount'                => 'required|numeric|min:1',
            'request_date'          => 'required|date',
            'reason'                => 'nullable|string|max:500',
            'repayment_type'        => 'required|in:single,installments',
            'installments_count'    => 'required_if:repayment_type,installments|nullable|integer|min:1|max:24',
        ]);
        $data['installments_count'] = $data['repayment_type'] === 'installments' ? $data['installments_count'] : 1;
        $data['status'] = 'pending';

        SalaryAdvance::create($data);

        return redirect()->route('admin.salary-advance.index')->with('success', __('messages.saved_successfully'));
    }

    public function approve(SalaryAdvance $salary_advance)
    {
        $salary_advance->update([
            'status'      => 'approved',
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function reject(SalaryAdvance $salary_advance)
    {
        $salary_advance->update([
            'status'      => 'rejected',
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(SalaryAdvance $salary_advance)
    {
        $salary_advance->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
