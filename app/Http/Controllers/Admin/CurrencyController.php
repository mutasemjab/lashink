<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('setting-edit'));
    }

    public function index()
    {
        $currencies = Currency::orderByDesc('is_default')->orderBy('code')->get();
        return view('admin.currency.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'   => 'required|string|max:10|unique:currencies,code',
            'symbol' => 'required|string|max:10',
        ]);

        Currency::create($data + ['is_active' => true]);

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:10|unique:currencies,code,' . $currency->id,
            'symbol'    => 'required|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $currency->update($data);
        Currency::forgetDefaultCache();

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function makeDefault(Currency $currency)
    {
        Currency::query()->update(['is_default' => false]);
        $currency->update(['is_default' => true, 'is_active' => true]);
        Currency::forgetDefaultCache();

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', __('messages.cannot_delete_default_currency'));
        }

        $currency->delete();

        return back()->with('success', __('messages.deleted_successfully'));
    }
}
