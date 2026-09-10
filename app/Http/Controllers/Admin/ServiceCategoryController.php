<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('service-category-table'))->only(['index']);
        $this->middleware($this->perm('service-category-add'))->only(['store']);
        $this->middleware($this->perm('service-category-edit'))->only(['update']);
        $this->middleware($this->perm('service-category-delete'))->only(['destroy']);
    }

    public function index()
    {
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.service_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        ServiceCategory::create($request->only('name', 'icon', 'sort_order'));

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'icon'       => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $service_category->update($request->only('name', 'icon', 'sort_order'));

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(ServiceCategory $service_category)
    {
        $service_category->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }
}
