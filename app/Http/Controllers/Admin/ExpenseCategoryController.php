<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('expense-category-table'))->only(['index']);
        $this->middleware($this->perm('expense-category-add'))->only(['store']);
        $this->middleware($this->perm('expense-category-edit'))->only(['update']);
        $this->middleware($this->perm('expense-category-delete'))->only(['destroy']);
    }

    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->get();
        return view('admin.expense_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        ExpenseCategory::create($request->validate(['name' => 'required|string|max:150']));
        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $expense_category->update($request->validate(['name' => 'required|string|max:150']));
        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        $expense_category->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
