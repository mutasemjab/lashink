<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\InvoiceItem;
use App\Models\LeaveRequest;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\SalaryAdvance;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate (or regenerate, while still draft) the payroll run for a period.
     */
    public function generate(int $month, int $year): PayrollRun
    {
        return DB::transaction(function () use ($month, $year) {
            $run = PayrollRun::firstOrCreate(
                ['period_month' => $month, 'period_year' => $year],
                ['status' => 'draft', 'generated_by' => auth('admin')->id()]
            );

            if ($run->status !== 'draft') {
                return $run;
            }

            $run->items()->delete();

            $periodStart = Carbon::create($year, $month, 1)->startOfDay();
            $periodEnd   = $periodStart->copy()->endOfMonth()->endOfDay();

            $employees = Admin::where('is_super', false)
                ->where('employment_status', '!=', 'terminated')
                ->get();

            $totalNet = 0;

            $employeeDefaultCurrencyId = \App\Models\Currency::default()?->id;

            foreach ($employees as $employee) {
                // Only commission earned on invoices in the employee's own payroll
                // currency is included — mixing currencies into one salary figure
                // would require a conversion rate, which this app doesn't have.
                $commission = (float) InvoiceItem::where('employee_id', $employee->id)
                    ->whereHas('invoice', fn($q) => $q->whereBetween('issued_at', [$periodStart, $periodEnd])
                        ->where('status', '!=', 'cancelled')
                        ->where('currency_id', $employee->currency_id ?? $employeeDefaultCurrencyId))
                    ->sum('commission_amount');

                $unpaidLeaveDays = (float) LeaveRequest::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereHas('leaveType', fn($q) => $q->where('is_paid', false))
                    ->where('start_date', '<=', $periodEnd)
                    ->where('end_date', '>=', $periodStart)
                    ->sum('days');

                $dailyRate = $employee->base_salary > 0 ? $employee->base_salary / 30 : 0;
                $leaveDeduction = round($unpaidLeaveDays * $dailyRate, 2);

                $advances = SalaryAdvance::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('is_settled', false)
                    ->get();

                $advanceDeduction = 0;
                foreach ($advances as $advance) {
                    $advanceDeduction += $advance->installmentAmount();
                }

                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$periodStart, $periodEnd])
                    ->get();
                $lateMinutesTotal     = (int) $attendances->sum('late_minutes');
                $overtimeMinutesTotal = (int) $attendances->sum('overtime_minutes');

                $shiftStart = Carbon::parse(Setting::get('shift_start_time', '09:00'));
                $shiftEnd   = Carbon::parse(Setting::get('shift_end_time', '18:00'));
                $shiftHours = max($shiftStart->diffInMinutes($shiftEnd) / 60, 1);

                $perMinuteRate  = $employee->base_salary > 0 ? ($employee->base_salary / (30 * $shiftHours)) / 60 : 0;
                $lateDeduction  = round($lateMinutesTotal * $perMinuteRate, 2);
                $overtimeAmount = round($overtimeMinutesTotal * $perMinuteRate, 2);

                $netSalary = (float) $employee->base_salary + $commission + $overtimeAmount - $leaveDeduction - $advanceDeduction - $lateDeduction;

                PayrollItem::create([
                    'payroll_run_id'          => $run->id,
                    'employee_id'             => $employee->id,
                    'currency_id'             => $employee->currency_id ?? \App\Models\Currency::default()?->id,
                    'base_salary'             => $employee->base_salary,
                    'commission_amount'       => $commission,
                    'unpaid_leave_deduction'  => $leaveDeduction,
                    'advance_deduction'       => $advanceDeduction,
                    'late_deduction'          => $lateDeduction,
                    'overtime_amount'         => $overtimeAmount,
                    'net_salary'              => max($netSalary, 0),
                ]);

                $totalNet += max($netSalary, 0);
            }

            $run->update(['generated_at' => now(), 'total_net' => $totalNet]);

            return $run->fresh('items.employee');
        });
    }

    /**
     * Finalize a draft run: locks it and applies advance repayments.
     */
    public function finalize(PayrollRun $run): void
    {
        DB::transaction(function () use ($run) {
            foreach ($run->items as $item) {
                if ($item->advance_deduction <= 0) {
                    continue;
                }

                $remaining = $item->advance_deduction;
                $advances = SalaryAdvance::where('employee_id', $item->employee_id)
                    ->where('status', 'approved')
                    ->where('is_settled', false)
                    ->get();

                foreach ($advances as $advance) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $apply = min($advance->installmentAmount(), $remaining);
                    $advance->repaid_amount += $apply;
                    $advance->is_settled = $advance->repaid_amount >= $advance->amount;
                    $advance->save();
                    $remaining -= $apply;
                }
            }

            $run->update(['status' => 'finalized', 'finalized_at' => now()]);
        });
    }

    public function markPaid(PayrollItem $item, string $method): void
    {
        $item->update(['payment_status' => 'paid', 'paid_at' => now(), 'payment_method' => $method]);
    }
}
