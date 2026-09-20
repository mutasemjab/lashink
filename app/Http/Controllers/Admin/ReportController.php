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

        $revenueByCurrency = Invoice::where('status', '!=', 'cancelled')->whereBetween('issued_at', [$from, $to])
            ->with('currency')->get()
            ->groupBy(fn($i) => $i->currency->code ?? __('Currency'))
            ->map(fn($g) => $g->sum('total'));

        $expensesByCurrency = Expense::whereBetween('expense_date', [$from, $to])
            ->with('currency')->get()
            ->groupBy(fn($e) => $e->currency->code ?? __('Currency'))
            ->map(fn($g) => $g->sum('amount'));

        $profitByCurrency = $revenueByCurrency->keys()->merge($expensesByCurrency->keys())->unique()
            ->mapWithKeys(fn($code) => [$code => ($revenueByCurrency[$code] ?? 0) - ($expensesByCurrency[$code] ?? 0)]);

        $currencies = \App\Models\Currency::all()->keyBy('id');

        $topServices = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->selectRaw('invoice_items.description, invoices.currency_id, SUM(invoice_items.total) as revenue, COUNT(*) as count')
            ->where('invoice_items.item_type', 'service')
            ->where('invoices.status', '!=', 'cancelled')
            ->whereBetween('invoices.issued_at', [$from, $to])
            ->groupBy('invoice_items.description', 'invoices.currency_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->each(fn($row) => $row->currency = $currencies[$row->currency_id] ?? null);

        $topEmployees = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->selectRaw('invoice_items.employee_id, invoices.currency_id, SUM(invoice_items.total) as revenue, SUM(invoice_items.commission_amount) as commission')
            ->whereNotNull('invoice_items.employee_id')
            ->where('invoices.status', '!=', 'cancelled')
            ->whereBetween('invoices.issued_at', [$from, $to])
            ->groupBy('invoice_items.employee_id', 'invoices.currency_id')
            ->with('employee')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->each(fn($row) => $row->currency = $currencies[$row->currency_id] ?? null);

        $lowStock = Product::whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0)->get();

        $expenseByCategory = Expense::whereBetween('expense_date', [$from, $to])
            ->with(['category', 'currency'])
            ->get()
            ->groupBy('category_id')
            ->map(fn($g) => (object) [
                'category' => $g->first()->category,
                'total'    => $g->sum('amount'),
                'currency_breakdown' => $g->groupBy(fn($e) => $e->currency->code ?? __('Currency'))->map(fn($g2) => $g2->sum('amount')),
            ])
            ->sortByDesc('total');

        return view('admin.report.index', compact(
            'from', 'to', 'revenueByCurrency', 'expensesByCurrency', 'profitByCurrency',
            'topServices', 'topEmployees', 'lowStock', 'expenseByCategory'
        ));
    }

    public function exportRevenue(Request $request)
    {
        [$from, $to] = $this->period($request);

        $rows = Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('issued_at', [$from, $to])
            ->with(['client', 'currency'])
            ->get()
            ->map(fn($i) => [
                $i->invoice_number,
                $i->client->name ?? '',
                $i->issued_at->format('Y-m-d'),
                (float) $i->total,
                $i->currency->code ?? '',
                $i->payment_status,
            ]);

        return Excel::download(
            new GenericExport($rows, [__('messages.invoice_number'), __('messages.clients'), __('messages.field_date'), __('messages.field_total'), __('messages.field_code'), __('messages.Status')]),
            'revenue-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportExpenses(Request $request)
    {
        [$from, $to] = $this->period($request);

        $rows = Expense::whereBetween('expense_date', [$from, $to])
            ->with(['category', 'currency'])
            ->get()
            ->map(fn($e) => [
                $e->category->name ?? '',
                $e->expense_date->format('Y-m-d'),
                (float) $e->amount,
                $e->currency->code ?? '',
                $e->description,
            ]);

        return Excel::download(
            new GenericExport($rows, [__('messages.field_category'), __('messages.field_date'), __('messages.field_amount'), __('messages.field_code'), __('messages.field_description')]),
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
