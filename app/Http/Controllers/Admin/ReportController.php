<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('report-view'));
    }

    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);

        $revenue  = (float) Invoice::where('status', '!=', 'cancelled')->whereBetween('issued_at', [$from, $to])->sum('total');
        $expenses = (float) Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $profit   = $revenue - $expenses;

        $topServices = InvoiceItem::selectRaw('description, SUM(total) as revenue, COUNT(*) as count')
            ->where('item_type', 'service')
            ->whereHas('invoice', fn($q) => $q->where('status', '!=', 'cancelled')->whereBetween('issued_at', [$from, $to]))
            ->groupBy('description')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $topEmployees = InvoiceItem::selectRaw('employee_id, SUM(total) as revenue, SUM(commission_amount) as commission')
            ->whereNotNull('employee_id')
            ->whereHas('invoice', fn($q) => $q->where('status', '!=', 'cancelled')->whereBetween('issued_at', [$from, $to]))
            ->groupBy('employee_id')
            ->with('employee')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $lowStock = Product::whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0)->get();

        $expenseByCategory = Expense::selectRaw('category_id, SUM(amount) as total')
            ->whereBetween('expense_date', [$from, $to])
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        return view('admin.report.index', compact(
            'from', 'to', 'revenue', 'expenses', 'profit',
            'topServices', 'topEmployees', 'lowStock', 'expenseByCategory'
        ));
    }

    public function exportRevenue(Request $request)
    {
        [$from, $to] = $this->period($request);

        $rows = Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('issued_at', [$from, $to])
            ->with('client')
            ->get()
            ->map(fn($i) => [
                $i->invoice_number,
                $i->client->name ?? '',
                $i->issued_at->format('Y-m-d'),
                (float) $i->total,
                $i->payment_status,
            ]);

        return Excel::download(
            new GenericExport($rows, [__('messages.invoice_number'), __('messages.clients'), __('messages.field_date'), __('messages.field_total'), __('messages.Status')]),
            'revenue-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportExpenses(Request $request)
    {
        [$from, $to] = $this->period($request);

        $rows = Expense::whereBetween('expense_date', [$from, $to])
            ->with('category')
            ->get()
            ->map(fn($e) => [
                $e->category->name ?? '',
                $e->expense_date->format('Y-m-d'),
                (float) $e->amount,
                $e->description,
            ]);

        return Excel::download(
            new GenericExport($rows, [__('messages.field_category'), __('messages.field_date'), __('messages.field_amount'), __('messages.field_description')]),
            'expenses-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function period(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfMonth();

        return [$from, $to];
    }
}
