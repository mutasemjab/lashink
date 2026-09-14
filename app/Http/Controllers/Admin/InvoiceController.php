<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService)
    {
        $this->middleware($this->perm('invoice-table'))->only(['index', 'show']);
        $this->middleware($this->perm('invoice-add'))->only(['create', 'store']);
        $this->middleware($this->perm('invoice-edit'))->only(['addPayment', 'cancel']);
        $this->middleware($this->perm('invoice-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $from = $request->has('from') ? $request->from : now()->toDateString();
        $to   = $request->has('to') ? $request->to : now()->toDateString();

        $invoices = Invoice::with('client')
            ->when($request->search, fn($q, $s) => $q->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%$s%")))
            ->when($request->payment_status, fn($q, $s) => $q->where('payment_status', $s))
            ->when($from, fn($q, $d) => $q->whereDate('issued_at', '>=', $d))
            ->when($to, fn($q, $d) => $q->whereDate('issued_at', '<=', $d))
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoice.index', compact('invoices', 'from', 'to'));
    }

    public function create(Request $request)
    {
        $clients   = Client::orderBy('name')->get();
        $services  = Service::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_sellable', true)->where('is_active', true)->orderBy('name')->get();
        $employees = Admin::where('is_super', false)->where('employment_status', 'active')->orderBy('name')->get();

        $appointment = null;
        if ($request->appointment_id) {
            $appointment = Appointment::with('services.service')->find($request->appointment_id);
        }

        return view('admin.invoice.create', compact('clients', 'services', 'products', 'employees', 'appointment'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'         => 'required|exists:clients,id',
            'appointment_id'    => 'nullable|exists:appointments,id',
            'employee_id'       => 'nullable|exists:admins,id',
            'discount_amount'   => 'nullable|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:2000',
            'item_type'         => 'required|array|min:1',
            'item_type.*'       => 'required|in:service,product',
            'item_id'           => 'required|array|min:1',
            'item_id.*'         => 'required|integer',
            'item_qty'          => 'required|array|min:1',
            'item_qty.*'        => 'required|numeric|min:0.01',
            'item_employee'     => 'nullable|array',
        ]);

        $lines = [];
        foreach ($data['item_type'] as $i => $type) {
            $lines[] = [
                'type'        => $type,
                'id'          => $data['item_id'][$i],
                'quantity'    => $data['item_qty'][$i],
                'employee_id' => $data['item_employee'][$i] ?? $data['employee_id'] ?? null,
            ];
        }

        $invoice = $this->invoiceService->create($data, $lines);

        if ($request->boolean('mark_paid')) {
            $this->invoiceService->recordPayment($invoice, (float) $invoice->total, $request->input('payment_method', 'cash'));
        }

        return redirect()->route('admin.invoice.show', $invoice->id)->with('success', __('messages.saved_successfully'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'payments', 'client', 'employee');
        return view('admin.invoice.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('items', 'client');
        return view('admin.invoice.print', compact('invoice'));
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->remainingAmount(),
            'method' => 'required|in:cash,card,transfer,other',
            'note'   => 'nullable|string|max:255',
        ]);

        $this->invoiceService->recordPayment($invoice, (float) $request->amount, $request->method, $request->note);

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function cancel(Invoice $invoice)
    {
        $invoice->update(['status' => 'cancelled']);
        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
