<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = ['salon_name', 'salon_phone', 'salon_address', 'working_hours', 'currency_symbol', 'salon_logo'];

    public function __construct()
    {
        $this->middleware($this->perm('setting-edit'));
    }

    public function edit()
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn($k) => [$k => Setting::get($k)]);
        return view('admin.setting.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'salon_name'       => 'nullable|string|max:150',
            'salon_phone'      => 'nullable|string|max:30',
            'salon_address'    => 'nullable|string|max:500',
            'working_hours'    => 'nullable|string|max:500',
            'currency_symbol'  => 'nullable|string|max:20',
            'salon_logo'       => 'nullable|image|max:2048',
        ]);

        foreach (['salon_name', 'salon_phone', 'salon_address', 'working_hours', 'currency_symbol'] as $key) {
            Setting::set($key, $data[$key] ?? null);
        }

        if ($request->hasFile('salon_logo')) {
            $filename = uploadImage('assets/uploads/settings', $request->file('salon_logo'));
            Setting::set('salon_logo', 'assets/uploads/settings/' . $filename);
        }

        return back()->with('success', __('messages.updated_successfully'));
    }
}
