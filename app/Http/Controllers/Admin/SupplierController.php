<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('supplier-table'))->only(['index']);
        $this->middleware($this->perm('supplier-add'))->only(['store']);
        $this->middleware($this->perm('supplier-edit'))->only(['update']);
        $this->middleware($this->perm('supplier-delete'))->only(['destroy']);
    }

    public function index()
    {
        $suppliers = Supplier::withCount('products')->orderBy('name')->get();
        return view('admin.supplier.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:200',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:2000',
        ]);
        Supplier::create($data);

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:200',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:2000',
        ]);
        $supplier->update($data);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
