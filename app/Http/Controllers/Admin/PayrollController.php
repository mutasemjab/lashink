<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService)
    {
        $this->middleware($this->perm('payroll-table'))->only(['index', 'show']);
        $this->middleware($this->perm('payroll-add'))->only(['generate']);
        $this->middleware($this->perm('payroll-edit'))->only(['finalize', 'markPaid']);
    }

    public function index()
    {
        $runs = PayrollRun::orderByDesc('period_year')->orderByDesc('period_month')->paginate(12);
        return view('admin.payroll.index', compact('runs'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year'  => 'required|integer|min:2020|max:2100',
        ]);

        $run = $this->payrollService->generate((int) $request->period_month, (int) $request->period_year);

        return redirect()->route('admin.payroll.show', $run->id)->with('success', __('messages.saved_successfully'));
    }

    public function show(PayrollRun $payroll)
    {
        $payroll->load('items.employee');
        return view('admin.payroll.show', ['run' => $payroll]);
    }

    public function finalize(PayrollRun $payroll)
    {
        if ($payroll->status === 'draft') {
            $this->payrollService->finalize($payroll);
        }

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function markPaid(Request $request, PayrollItem $item)
    {
        $request->validate(['payment_method' => 'required|in:cash,card,transfer']);
        $this->payrollService->markPaid($item, $request->payment_method);

        return back()->with('success', __('messages.updated_successfully'));
    }
}
