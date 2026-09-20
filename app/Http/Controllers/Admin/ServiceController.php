<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('service-table'))->only(['index']);
        $this->middleware($this->perm('service-add'))->only(['create', 'store']);
        $this->middleware($this->perm('service-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('service-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $services = Service::with(['category', 'currency'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = ServiceCategory::orderBy('name')->get();

        return view('admin.service.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::orderBy('name')->get();
        $employees  = Admin::where('is_super', false)->where('employment_status', 'active')->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        return view('admin.service.create', compact('categories', 'employees', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $service = Service::create($data);
        $service->qualifiedEmployees()->sync($request->input('employees', []));

        return redirect()->route('admin.service.index')->with('success', __('messages.saved_successfully'));
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::orderBy('name')->get();
        $employees  = Admin::where('is_super', false)->where('employment_status', 'active')->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();
        $assigned   = $service->qualifiedEmployees->pluck('id')->toArray();
        return view('admin.service.edit', compact('service', 'categories', 'employees', 'currencies', 'assigned'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request);
        $service->update($data);
        $service->qualifiedEmployees()->sync($request->input('employees', []));

        return redirect()->route('admin.service.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id'        => 'nullable|exists:service_categories,id',
            'currency_id'         => 'required|exists:currencies,id',
            'name'                => 'required|string|max:200',
            'duration_minutes'    => 'required|integer|min:1',
            'price'               => 'required|numeric|min:0',
            'commission_type'    => 'required|in:percent,fixed',
            'commission_value'   => 'nullable|numeric|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
