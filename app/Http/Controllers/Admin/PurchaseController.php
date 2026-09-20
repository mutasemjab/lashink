<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('purchase-table'))->only(['index', 'show']);
        $this->middleware($this->perm('purchase-add'))->only(['create', 'store']);
        $this->middleware($this->perm('purchase-delete'))->only(['destroy']);
    }

    public function index()
    {
        $purchases = Purchase::with(['supplier', 'currency'])->latest('purchase_date')->paginate(15);
        return view('admin.purchase.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        return view('admin.purchase.create', compact('suppliers', 'products', 'currencies'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items.product', 'supplier', 'currency');
        return view('admin.purchase.show', compact('purchase'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'currency_id'     => 'required|exists:currencies,id',
            'purchase_date'   => 'required|date',
            'notes'           => 'nullable|string|max:2000',
            'product_id'      => 'required|array|min:1',
            'product_id.*'    => 'required|exists:products,id',
            'quantity'        => 'required|array|min:1',
            'quantity.*'      => 'required|numeric|min:0.01',
            'unit_cost'       => 'required|array|min:1',
            'unit_cost.*'     => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->quantity as $i => $qty) {
                $total += $qty * $request->unit_cost[$i];
            }

            $purchase = Purchase::create([
                'supplier_id'   => $request->supplier_id,
                'currency_id'   => $request->currency_id,
                'purchase_date' => $request->purchase_date,
                'total'         => $total,
                'notes'         => $request->notes,
                'created_by'    => auth('admin')->id(),
            ]);

            foreach ($request->product_id as $i => $productId) {
                $qty      = (float) $request->quantity[$i];
                $unitCost = (float) $request->unit_cost[$i];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $productId,
                    'quantity'    => $qty,
                    'unit_cost'   => $unitCost,
                    'total'       => $qty * $unitCost,
                ]);

                $product = Product::find($productId);
                $product?->adjustStock('in', $qty, 'purchase', __('messages.purchase_stock_note'), $purchase);
            }

            $category = ExpenseCategory::firstOrCreate(['name' => 'مشتريات مخزون']);
            Expense::create([
                'category_id'    => $category->id,
                'currency_id'    => $request->currency_id,
                'amount'         => $total,
                'expense_date'   => $request->purchase_date,
                'description'    => __('messages.purchase_stock_note') . ' #' . $purchase->id,
                'payment_method' => 'cash',
                'reference_type' => Purchase::class,
                'reference_id'   => $purchase->id,
                'created_by'     => auth('admin')->id(),
            ]);
        });

        return redirect()->route('admin.purchase.index')->with('success', __('messages.saved_successfully'));
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
