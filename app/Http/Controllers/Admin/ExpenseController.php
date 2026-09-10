<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('expense-table'))->only(['index']);
        $this->middleware($this->perm('expense-add'))->only(['create', 'store']);
        $this->middleware($this->perm('expense-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('expense-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filtered = fn() => Expense::query()
            ->when($request->from, fn($q, $d) => $q->where('expense_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->where('expense_date', '<=', $d))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c));

        $expenses = $filtered()->with('category')->orderByDesc('expense_date')->paginate(20)->withQueryString();
        $total    = $filtered()->sum('amount');
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('admin.expense.index', compact('expenses', 'categories', 'total'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('admin.expense.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth('admin')->id();

        if ($request->hasFile('attachment')) {
            $filename = uploadImage('assets/uploads/expenses', $request->file('attachment'));
            $data['attachment'] = 'assets/uploads/expenses/' . $filename;
        }

        Expense::create($data);

        return redirect()->route('admin.expense.index')->with('success', __('messages.saved_successfully'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('admin.expense.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $filename = uploadImage('assets/uploads/expenses', $request->file('attachment'));
            $data['attachment'] = 'assets/uploads/expenses/' . $filename;
        }

        $expense->update($data);

        return redirect()->route('admin.expense.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id'    => 'required|exists:expense_categories,id',
            'amount'          => 'required|numeric|min:0.01',
            'expense_date'    => 'required|date',
            'description'     => 'nullable|string|max:500',
            'payment_method'  => 'required|in:cash,card,transfer',
            'attachment'      => 'nullable|file|max:4096',
        ]);
    }
}
