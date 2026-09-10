<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('product-category-table'))->only(['index']);
        $this->middleware($this->perm('product-category-add'))->only(['store']);
        $this->middleware($this->perm('product-category-edit'))->only(['update']);
        $this->middleware($this->perm('product-category-delete'))->only(['destroy']);
    }

    public function index()
    {
        $categories = ProductCategory::withCount('products')->orderBy('name')->get();
        return view('admin.product_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        ProductCategory::create($request->validate(['name' => 'required|string|max:150']));
        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, ProductCategory $product_category)
    {
        $product_category->update($request->validate(['name' => 'required|string|max:150']));
        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(ProductCategory $product_category)
    {
        $product_category->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
