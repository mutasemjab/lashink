<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\SalaryAdvance;

class DashboardController extends Controller
{
    public function index()
    {
        $today      = now()->startOfDay();
        $todayEnd   = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        $todayAppointments = Appointment::with(['client', 'employee'])
            ->whereBetween('start_at', [$today, $todayEnd])
            ->orderBy('start_at')
            ->get();

        $todayRevenue = (float) Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('issued_at', [$today, $todayEnd])
            ->sum('total');

        $monthRevenue = (float) Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('issued_at', [$monthStart, $monthEnd])
            ->sum('total');

        $monthExpenses = (float) Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');
        $monthProfit   = $monthRevenue - $monthExpenses;

        $lowStockCount = Product::whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0)->count();

        $pendingLeaves   = LeaveRequest::where('status', 'pending')->count();
        $pendingAdvances = SalaryAdvance::where('status', 'pending')->count();

        $topEmployee = InvoiceItem::selectRaw('employee_id, SUM(total) as revenue')
            ->whereNotNull('employee_id')
            ->whereHas('invoice', fn($q) => $q->where('status', '!=', 'cancelled')->whereBetween('issued_at', [$monthStart, $monthEnd]))
            ->groupBy('employee_id')
            ->with('employee')
            ->orderByDesc('revenue')
            ->first();

        return view('admin.dashboard', compact(
            'todayAppointments', 'todayRevenue', 'monthProfit', 'lowStockCount',
            'pendingLeaves', 'pendingAdvances', 'topEmployee'
        ));
    }
}
