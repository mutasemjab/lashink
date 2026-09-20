<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('product-table'))->only(['index']);
        $this->middleware($this->perm('product-add'))->only(['create', 'store']);
        $this->middleware($this->perm('product-edit'))->only(['edit', 'update', 'adjustStock']);
        $this->middleware($this->perm('product-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $products = Product::with(['category', 'supplier', 'currency'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->low_stock, fn($q) => $q->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.product.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        return view('admin.product.create', compact('categories', 'suppliers', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $filename = uploadImage('assets/uploads/products', $request->file('image'));
            $data['image'] = 'assets/uploads/products/' . $filename;
        }

        Product::create($data);

        return redirect()->route('admin.product.index')->with('success', __('messages.saved_successfully'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        return view('admin.product.edit', compact('product', 'categories', 'suppliers', 'currencies'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $filename = uploadImage('assets/uploads/products', $request->file('image'));
            $data['image'] = 'assets/uploads/products/' . $filename;
        }

        $product->update($data);

        return redirect()->route('admin.product.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'type'     => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'note'     => 'nullable|string|max:255',
        ]);

        $product->adjustStock($request->type, (float) $request->quantity, 'adjustment', $request->note);

        return back()->with('success', __('messages.updated_successfully'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id'       => 'nullable|exists:product_categories,id',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'currency_id'        => 'required|exists:currencies,id',
            'name'               => 'required|string|max:200',
            'unit'               => 'required|string|max:30',
            'quantity_in_stock'  => 'nullable|numeric|min:0',
            'min_stock_alert'    => 'nullable|numeric|min:0',
            'cost_price'         => 'required|numeric|min:0',
            'sale_price'         => 'nullable|numeric|min:0',
            'image'              => 'nullable|image|max:4096',
        ]);
        $data['is_sellable'] = $request->boolean('is_sellable');
        $data['is_active']   = $request->boolean('is_active', true);
        unset($data['image']);

        return $data;
    }
}
