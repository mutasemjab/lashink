<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\SalaryAdvance;
use App\Models\Setting;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Payroll calculation tests.
 *
 * RUN ONLY WITH:  DB_DATABASE=lashink_test vendor/bin/phpunit tests/Feature/PayrollCalculationTest.php
 * The real `lashink` database must never be used (RefreshDatabase wipes it).
 *
 * Period under test: March 2026 (month 3, year 2026), 31 days.
 * Default shift 09:00-18:00 = 9h, so per-minute rate for salary 9000 = 9000/30/9/60 = 0.5555.. JOD/min.
 * Currencies seeded by migrations: id 1 = JOD (default), id 2 = USD.
 * Admin id 1 is the seeded super admin (used for HTTP tests).
 */
class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    private const M = 3;
    private const Y = 2026;

    /** Hard guard: runs BEFORE migrate:fresh. Aborts if not the throwaway DB. */
    protected function beforeRefreshingDatabase()
    {
        $this->assertSame('lashink_test', config('database.connections.mysql.database'), 'Refusing to run on a non-test DB');
        $this->assertSame('lashink_test', DB::connection()->getDatabaseName(), 'Refusing to run on a non-test DB');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('lashink_test', DB::connection()->getDatabaseName());
    }

    // ---------- helpers ----------

    private int $seq = 0;

    private function emp(array $o = []): Admin
    {
        $this->seq++;
        return Admin::forceCreate(array_merge([
            'name' => "Emp{$this->seq}", 'username' => "emp{$this->seq}", 'password' => bcrypt('x'),
            'is_super' => false, 'base_salary' => 9000, 'employment_status' => 'active', 'currency_id' => 1,
        ], $o));
    }

    private function att(Admin $e, string $date, int $late, int $ot): Attendance
    {
        return Attendance::create([
            'employee_id' => $e->id, 'date' => $date, 'check_in' => "$date 09:00", 'check_out' => "$date 18:00",
            'late_minutes' => $late, 'overtime_minutes' => $ot,
        ]);
    }

    private function leaveType(bool $paid): LeaveType
    {
        return LeaveType::create(['name' => $paid ? 'Paid' : 'Unpaid', 'default_days_per_year' => 10, 'is_paid' => $paid]);
    }

    private function leave(Admin $e, LeaveType $t, string $s, string $en, float $days, string $status = 'approved'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $e->id, 'leave_type_id' => $t->id, 'start_date' => $s, 'end_date' => $en,
            'days' => $days, 'status' => $status,
        ]);
    }

    private function advance(Admin $e, float $amount, int $installments = 1, string $status = 'approved', float $repaid = 0, bool $settled = false): SalaryAdvance
    {
        return SalaryAdvance::create([
            'employee_id' => $e->id, 'currency_id' => 1, 'amount' => $amount, 'request_date' => '2026-01-15', 'status' => $status,
            'repayment_type' => $installments > 1 ? 'installments' : 'single', 'installments_count' => $installments,
            'repaid_amount' => $repaid, 'is_settled' => $settled,
        ]);
    }

    private function commissionInvoice(Admin $e, float $commission, string $issuedAt = '2026-03-15 12:00:00', string $status = 'issued', int $currency = 1): Invoice
    {
        static $n = 0;
        $n++;
        $clientId = DB::table('clients')->value('id') ?? DB::table('clients')->insertGetId([
            'name' => 'C', 'phone' => '0500000000', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $inv = Invoice::create([
            'invoice_number' => 'INV-T' . $n, 'client_id' => $clientId, 'employee_id' => $e->id, 'currency_id' => $currency,
            'subtotal' => 1000, 'total' => 1000, 'paid_amount' => 1000, 'payment_status' => 'paid', 'status' => $status, 'issued_at' => $issuedAt,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv->id, 'item_type' => 'service', 'employee_id' => $e->id, 'description' => 'x',
            'quantity' => 1, 'unit_price' => 1000, 'total' => 1000, 'commission_amount' => $commission,
        ]);
        return $inv;
    }

    private function gen(int $m = self::M, int $y = self::Y): PayrollRun
    {
        return app(PayrollService::class)->generate($m, $y);
    }

    private function item(PayrollRun $run, Admin $e): PayrollItem
    {
        return $run->items()->where('employee_id', $e->id)->firstOrFail();
    }

    private function assertMoney(float $expected, $actual, string $msg = ''): void
    {
        $this->assertEqualsWithDelta($expected, (float) $actual, 0.005, $msg);
    }

    // ---------- 1. baseline ----------

    public function test_01_baseline_salary_only(): void
    {
        $e = $this->emp();
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(9000, $i->base_salary);
        $this->assertMoney(0, $i->commission_amount);
        $this->assertMoney(0, $i->overtime_amount);
        $this->assertMoney(0, $i->late_deduction);
        $this->assertMoney(0, $i->unpaid_leave_deduction);
        $this->assertMoney(0, $i->advance_deduction);
        $this->assertMoney(9000, $i->net_salary);
        $this->assertSame(1, (int) $i->currency_id);
    }

    // ---------- 2. overtime + attendance computation ----------

    public function test_02_overtime_from_attendance_rows(): void
    {
        $e = $this->emp();
        $this->att($e, '2026-03-02', 0, 60);
        $i = $this->item($this->gen(), $e);
        // 60 * 9000/30/9/60 = 33.33
        $this->assertMoney(33.33, $i->overtime_amount);
        $this->assertMoney(9033.33, $i->net_salary);
    }

    public function test_02b_attendance_store_endpoint_computes_late_and_overtime(): void
    {
        $admin = Admin::find(1);
        $e = $this->emp();
        $cases = [
            // date, in, out, expected late, expected overtime
            ['2026-03-02', '09:00', '19:00', 0, 60],   // 1h after 18:00
            ['2026-03-03', '09:20', '18:00', 20, 0],   // 20 min late, out exactly at end
            ['2026-03-04', '08:30', '18:00', 0, 0],    // early arrival is not late (and no credit)
            ['2026-03-05', '09:00', '19:30', 0, 90],
        ];
        foreach ($cases as [$d, $in, $out, $late, $ot]) {
            $this->actingAs($admin, 'admin')->post(route('admin.attendance.store'), [
                'employee_id' => $e->id, 'date' => $d, 'check_in' => $in, 'check_out' => $out,
            ])->assertSessionHasNoErrors();
            $row = Attendance::where('employee_id', $e->id)->whereDate('date', $d)->firstOrFail();
            $this->assertSame($late, (int) $row->late_minutes, "late for $d");
            $this->assertSame($ot, (int) $row->overtime_minutes, "overtime for $d");
        }

        // payroll: late 20 (0.5555*20=11.11), overtime 150 (83.33) -> 9000 + 83.33 - 11.11 = 9072.22
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(11.11, $i->late_deduction);
        $this->assertMoney(83.33, $i->overtime_amount);
        $this->assertMoney(9072.22, $i->net_salary);
    }

    // ---------- 3. late ----------

    public function test_03_late_deduction(): void
    {
        $e = $this->emp();
        $this->att($e, '2026-03-02', 30, 0);
        $i = $this->item($this->gen(), $e);
        // 30 * 0.5555 = 16.67
        $this->assertMoney(16.67, $i->late_deduction);
        $this->assertMoney(8983.33, $i->net_salary);
    }

    public function test_03b_attendance_outside_period_ignored(): void
    {
        $e = $this->emp();
        $this->att($e, '2026-02-28', 30, 60);
        $this->att($e, '2026-04-01', 30, 60);
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(9000, $i->net_salary);
        // last day of month is included
        $this->att($e, '2026-03-31', 30, 0);
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(16.67, $i->late_deduction);
    }

    // ---------- 4. custom shift ----------

    public function test_04_custom_shift_settings_change_rate(): void
    {
        Setting::set('shift_start_time', '08:00');
        Setting::set('shift_end_time', '16:00');
        $e = $this->emp();
        $this->att($e, '2026-03-02', 30, 60);
        $i = $this->item($this->gen(), $e);
        // 8h shift: rate = 9000/30/8/60 = 0.625/min. overtime 60 -> 37.50, late 30 -> 18.75
        $this->assertMoney(37.50, $i->overtime_amount);
        $this->assertMoney(18.75, $i->late_deduction);
        $this->assertMoney(9018.75, $i->net_salary);
    }

    public function test_04b_attendance_endpoint_uses_custom_shift(): void
    {
        Setting::set('shift_start_time', '08:00');
        Setting::set('shift_end_time', '16:00');
        $e = $this->emp();
        $this->actingAs(Admin::find(1), 'admin')->post(route('admin.attendance.store'), [
            'employee_id' => $e->id, 'date' => '2026-03-02', 'check_in' => '08:30', 'check_out' => '17:00',
        ])->assertSessionHasNoErrors();
        $row = Attendance::where('employee_id', $e->id)->firstOrFail();
        $this->assertSame(30, (int) $row->late_minutes);
        $this->assertSame(60, (int) $row->overtime_minutes);
    }

    // ---------- 5. unpaid leave ----------

    public function test_05_unpaid_leave_deduction_and_exclusions(): void
    {
        $unpaid = $this->leaveType(false);
        $paid = $this->leaveType(true);

        $a = $this->emp(); // approved unpaid 2 days in period -> 2 * 300 = 600
        $this->leave($a, $unpaid, '2026-03-10', '2026-03-11', 2);
        $b = $this->emp(); // paid leave -> none
        $this->leave($b, $paid, '2026-03-10', '2026-03-11', 2);
        $c = $this->emp(); // pending + rejected -> none
        $this->leave($c, $unpaid, '2026-03-10', '2026-03-11', 2, 'pending');
        $this->leave($c, $unpaid, '2026-03-12', '2026-03-13', 2, 'rejected');
        $d = $this->emp(); // outside period -> none
        $this->leave($d, $unpaid, '2026-02-10', '2026-02-11', 2);
        $this->leave($d, $unpaid, '2026-04-10', '2026-04-11', 2);

        $run = $this->gen();
        $this->assertMoney(600, $this->item($run, $a)->unpaid_leave_deduction);
        $this->assertMoney(8400, $this->item($run, $a)->net_salary);
        foreach ([$b, $c, $d] as $x) {
            $this->assertMoney(0, $this->item($run, $x)->unpaid_leave_deduction);
            $this->assertMoney(9000, $this->item($run, $x)->net_salary);
        }
    }

    /**
     * KNOWN BUG (expected to FAIL): a leave spanning two months is deducted in FULL in every month it overlaps.
     * Leave 2026-03-30..2026-04-02 = 4 days total (2 in March, 2 in April). Correct: 600 in March, 600 in April.
     * Actual: 4 * 300 = 1200 in BOTH months (2400 total, double the correct 1200).
     * PayrollService.php ~line 55: sums LeaveRequest.days without prorating to the overlap with the period.
     */
    public function test_05b_BUG_cross_month_leave_is_double_counted(): void
    {
        $e = $this->emp();
        $this->leave($e, $this->leaveType(false), '2026-03-30', '2026-04-02', 4);
        $this->assertMoney(600, $this->item($this->gen(3, 2026), $e)->unpaid_leave_deduction, 'March should only deduct its 2 days');
        $this->assertMoney(600, $this->item($this->gen(4, 2026), $e)->unpaid_leave_deduction, 'April should only deduct its 2 days');
    }

    // ---------- 6. advances ----------

    public function test_06a_advance_selection_and_amounts(): void
    {
        $single = $this->emp();   $this->advance($single, 3000, 1);                    // 3000
        $inst = $this->emp();     $this->advance($inst, 3000, 3);                      // 1000
        $pending = $this->emp();  $this->advance($pending, 3000, 1, 'pending');       // 0
        $rejected = $this->emp(); $this->advance($rejected, 3000, 1, 'rejected');      // 0
        $settled = $this->emp();  $this->advance($settled, 3000, 1, 'approved', 3000, true); // 0
        $two = $this->emp();      $this->advance($two, 3000, 3); $this->advance($two, 500, 1); // 1000 + 500

        $run = $this->gen();
        $this->assertMoney(3000, $this->item($run, $single)->advance_deduction);
        $this->assertMoney(6000, $this->item($run, $single)->net_salary);
        $this->assertMoney(1000, $this->item($run, $inst)->advance_deduction);
        $this->assertMoney(8000, $this->item($run, $inst)->net_salary);
        $this->assertMoney(0, $this->item($run, $pending)->advance_deduction);
        $this->assertMoney(0, $this->item($run, $rejected)->advance_deduction);
        $this->assertMoney(0, $this->item($run, $settled)->advance_deduction);
        $this->assertMoney(1500, $this->item($run, $two)->advance_deduction);
        $this->assertMoney(7500, $this->item($run, $two)->net_salary);
    }

    public function test_06b_installments_across_months_with_finalize(): void
    {
        $e = $this->emp();
        $adv = $this->advance($e, 3000, 3);
        $svc = app(PayrollService::class);

        foreach ([[3, 1000, false], [4, 2000, false], [5, 3000, true]] as [$m, $repaid, $settled]) {
            $run = $this->gen($m, 2026);
            $this->assertMoney(1000, $this->item($run, $e)->advance_deduction, "month $m deduction");
            $this->assertMoney(8000, $this->item($run, $e)->net_salary);
            $svc->finalize($run);
            $adv->refresh();
            $this->assertMoney($repaid, $adv->repaid_amount, "repaid after month $m");
            $this->assertSame($settled, (bool) $adv->is_settled, "settled after month $m");
            $this->assertSame('finalized', $run->fresh()->status);
        }

        // 4th month: nothing left
        $run = $this->gen(6, 2026);
        $this->assertMoney(0, $this->item($run, $e)->advance_deduction);
        $this->assertMoney(9000, $this->item($run, $e)->net_salary);
    }

    public function test_06c_installment_amount_capped_at_remaining(): void
    {
        // 1000 in 3 installments: the third installment takes the remainder so total repaid == 1000 exactly.
        $e = $this->emp();
        $adv = $this->advance($e, 1000, 3);
        $svc = app(PayrollService::class);
        $deductions = [];
        foreach ([3, 4, 5, 6] as $m) {
            $run = $this->gen($m, 2026);
            $deductions[] = (float) $this->item($run, $e)->advance_deduction;
            $svc->finalize($run);
        }
        // the last installment absorbs the rounding remainder: 333.33 + 333.33 + 333.34 = 1000, nothing in month 4
        $this->assertMoney(333.33, $deductions[0]);
        $this->assertMoney(333.33, $deductions[1]);
        $this->assertMoney(333.34, $deductions[2]);
        $this->assertMoney(0, $deductions[3]);
        $adv->refresh();
        $this->assertMoney(1000, $adv->repaid_amount, 'advance must end up repaid exactly');
        $this->assertTrue((bool) $adv->is_settled);
    }

    public function test_06d_regenerate_draft_does_not_duplicate_and_finalized_is_locked(): void
    {
        $e = $this->emp();
        $run1 = $this->gen();
        $run2 = $this->gen();
        $this->assertSame($run1->id, $run2->id);
        $this->assertSame(1, PayrollItem::where('payroll_run_id', $run1->id)->where('employee_id', $e->id)->count());
        $this->assertSame(1, PayrollRun::count());

        // draft regeneration picks up new data
        $this->att($e, '2026-03-02', 30, 0);
        $this->assertMoney(8983.33, $this->item($this->gen(), $e)->net_salary);

        // finalize, then change data and regenerate: items frozen, status remains finalized
        app(PayrollService::class)->finalize($run1);
        $this->att($e, '2026-03-03', 60, 0);
        $run3 = $this->gen();
        $this->assertSame('finalized', $run3->status);
        $this->assertMoney(8983.33, $this->item($run3, $e)->net_salary);
    }

    public function test_06e_advance_larger_than_salary_floors_net_at_zero(): void
    {
        $e = $this->emp(['base_salary' => 500]);
        $this->advance($e, 3000, 1);
        $run = $this->gen();
        $i = $this->item($run, $e);
        // only the 500 actually available is withheld; the rest of the advance stays outstanding
        $this->assertMoney(500, $i->advance_deduction);
        $this->assertMoney(0, $i->net_salary);
        $this->assertMoney(0, $run->total_net);
    }

    /**
     * KNOWN BUG (expected to FAIL): when the advance installment exceeds what the employee can pay (net floored at 0),
     * only 500 was really recovered, yet finalize() records repaid_amount = 3000 and marks the advance settled.
     * The unrecovered 2500 silently vanishes. PayrollService.php: advance_deduction is not capped to available pay
     * (generate ~line 76) and finalize() (~line 130) trusts item->advance_deduction.
     */
    public function test_06f_BUG_finalize_marks_unrecovered_advance_as_repaid(): void
    {
        $e = $this->emp(['base_salary' => 500]);
        $adv = $this->advance($e, 3000, 1);
        $svc = app(PayrollService::class);
        $run = $this->gen();
        $svc->finalize($run);
        $adv->refresh();
        // Only 500 was actually withheld from the employee's pay.
        $this->assertMoney(500, $adv->repaid_amount, 'repaid should equal what was really deducted');
        $this->assertFalse((bool) $adv->is_settled, 'advance must not be settled while 2500 is still owed');
    }

    // ---------- 7. commission ----------

    public function test_07_commission_rules(): void
    {
        $e = $this->emp(); // default JOD (id 1)
        $this->commissionInvoice($e, 200);                                   // counted
        $this->commissionInvoice($e, 300, '2026-03-31 23:59:59');            // counted (last second)
        $this->commissionInvoice($e, 100, '2026-03-01 00:00:00');            // counted (first second)
        $this->commissionInvoice($e, 999, '2026-03-15 12:00:00', 'cancelled'); // excluded
        $this->commissionInvoice($e, 888, '2026-04-01 00:00:00');            // outside period
        $this->commissionInvoice($e, 777, '2026-02-28 23:59:59');            // outside period
        $this->commissionInvoice($e, 666, '2026-03-15 12:00:00', 'issued', 2); // USD invoice, employee is JOD -> excluded

        $i = $this->item($this->gen(), $e);
        $this->assertMoney(600, $i->commission_amount);
        $this->assertMoney(9600, $i->net_salary);
    }

    public function test_07b_commission_uses_employee_currency_and_null_currency_falls_back_to_default(): void
    {
        $usd = $this->emp(['currency_id' => 2]);
        $this->commissionInvoice($usd, 50, '2026-03-15 12:00:00', 'issued', 2); // counted
        $this->commissionInvoice($usd, 70, '2026-03-15 12:00:00', 'issued', 1); // JOD -> excluded

        $nullCur = $this->emp(['currency_id' => null]);
        $this->commissionInvoice($nullCur, 40, '2026-03-15 12:00:00', 'issued', 1); // default JOD counted
        $this->commissionInvoice($nullCur, 60, '2026-03-15 12:00:00', 'issued', 2); // excluded

        $run = $this->gen();
        $this->assertMoney(50, $this->item($run, $usd)->commission_amount);
        $this->assertSame(2, (int) $this->item($run, $usd)->currency_id);
        $this->assertMoney(40, $this->item($run, $nullCur)->commission_amount);
        $this->assertSame(1, (int) $this->item($run, $nullCur)->currency_id);
    }

    /** Documented design quirk: any non-cancelled invoice counts, even unpaid/draft ones. */
    public function test_07c_quirk_unpaid_and_draft_invoices_still_earn_commission(): void
    {
        $e = $this->emp();
        $inv = $this->commissionInvoice($e, 100, '2026-03-15 12:00:00', 'draft');
        $inv->update(['payment_status' => 'unpaid', 'paid_amount' => 0]);
        $this->assertMoney(100, $this->item($this->gen(), $e)->commission_amount);
    }

    // ---------- 8. combined ----------

    public function test_08_combined_case(): void
    {
        $e = $this->emp();
        $this->commissionInvoice($e, 500);
        $this->att($e, '2026-03-02', 0, 60);
        $this->att($e, '2026-03-03', 30, 0);
        $this->leave($e, $this->leaveType(false), '2026-03-10', '2026-03-10', 1);
        $this->advance($e, 3000, 3);

        $run = $this->gen();
        $i = $this->item($run, $e);
        $this->assertMoney(9000, $i->base_salary);
        $this->assertMoney(500, $i->commission_amount);
        $this->assertMoney(33.33, $i->overtime_amount);
        $this->assertMoney(300, $i->unpaid_leave_deduction);
        $this->assertMoney(1000, $i->advance_deduction);
        $this->assertMoney(16.67, $i->late_deduction);
        // 9000 + 500 + 33.33 - 300 - 1000 - 16.67 = 8216.66
        $this->assertMoney(8216.66, $i->net_salary);
        $this->assertMoney(8216.66, $run->total_net);
    }

    // ---------- 9. who is included ----------

    public function test_09_employment_status_and_super_admin_filter(): void
    {
        $active = $this->emp();
        $onLeave = $this->emp(['employment_status' => 'on_leave']);
        $terminated = $this->emp(['employment_status' => 'terminated']);
        $run = $this->gen();
        $ids = $run->items->pluck('employee_id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertContains($onLeave->id, $ids);
        $this->assertNotContains($terminated->id, $ids);
        $this->assertNotContains(1, $ids, 'super admin must be excluded');
        $this->assertCount(2, $ids);
        $this->assertMoney(18000, $run->total_net);
    }

    // ---------- 10. edge ----------

    public function test_10_zero_salary_edge(): void
    {
        $e = $this->emp(['base_salary' => 0]);
        $this->att($e, '2026-03-02', 30, 60);
        $this->commissionInvoice($e, 250);
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(0, $i->overtime_amount);
        $this->assertMoney(0, $i->late_deduction);
        $this->assertMoney(250, $i->net_salary);
    }

    public function test_10b_no_attendance_rows(): void
    {
        $e = $this->emp();
        $i = $this->item($this->gen(), $e);
        $this->assertMoney(0, $i->late_deduction);
        $this->assertMoney(0, $i->overtime_amount);
        $this->assertMoney(9000, $i->net_salary);
    }

    // ---------- HTTP end to end ----------

    public function test_11_http_generate_finalize_mark_paid(): void
    {
        $admin = Admin::find(1);
        $e = $this->emp();
        $this->advance($e, 3000, 3);

        $resp = $this->actingAs($admin, 'admin')->post(route('admin.payroll.generate'), ['period_month' => self::M, 'period_year' => self::Y]);
        $run = PayrollRun::firstOrFail();
        $resp->assertRedirect(route('admin.payroll.show', $run->id));
        $this->assertSame('draft', $run->status);
        $this->assertSame(1, (int) $run->generated_by);
        $this->assertMoney(8000, $run->total_net);

        // NOTE: GET pages 302 to a locale prefix in the test env (mcamara routes are registered without the locale prefix at boot),
        // so the views are rendered by calling the controller actions directly.
        $this->actingAs($admin, 'admin');
        $this->assertStringContainsString('Emp', app(\App\Http\Controllers\Admin\PayrollController::class)->show($run)->render());
        $this->assertNotEmpty(app(\App\Http\Controllers\Admin\PayrollController::class)->index()->render());


        $this->actingAs($admin, 'admin')->post(route('admin.payroll.finalize', $run->id))->assertSessionHasNoErrors();
        $this->assertSame('finalized', $run->fresh()->status);
        $this->assertMoney(1000, SalaryAdvance::first()->repaid_amount);

        // finalizing twice through the controller must NOT repay twice
        $this->actingAs($admin, 'admin')->post(route('admin.payroll.finalize', $run->id));
        $this->assertMoney(1000, SalaryAdvance::first()->repaid_amount);

        $item = $run->items()->firstOrFail();
        $this->actingAs($admin, 'admin')->post(route('admin.payroll.mark-paid', $item->id), ['payment_method' => 'cash'])
            ->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertSame('paid', $item->payment_status);
        $this->assertSame('cash', $item->payment_method);
        $this->assertNotNull($item->paid_at);

        // validation
        $this->actingAs($admin, 'admin')->post(route('admin.payroll.generate'), ['period_month' => 13, 'period_year' => 2026])
            ->assertSessionHasErrors('period_month');
    }
}
