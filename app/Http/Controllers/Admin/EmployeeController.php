<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('employee-table'))->only(['index']);
        $this->middleware($this->perm('employee-add'))->only(['create', 'store']);
        $this->middleware($this->perm('employee-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('employee-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $employees = Admin::where('is_super', false)
            ->with('roles')
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q2) => $q2
                    ->where('name', 'like', "%$s%")
                    ->orWhere('username', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%")
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.employee.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        return view('admin.employee.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:200',
            'username'            => 'required|string|max:100|unique:admins,username',
            'email'               => 'nullable|email|max:200|unique:admins,email',
            'phone'               => 'nullable|string|max:30',
            'national_id'         => 'nullable|string|max:30',
            'hire_date'           => 'nullable|date',
            'base_salary'         => 'nullable|numeric|min:0',
            'commission_percent'  => 'nullable|numeric|min:0|max:100',
            'employment_status'   => 'required|in:active,on_leave,terminated',
            'address'             => 'nullable|string|max:500',
            'notes'               => 'nullable|string|max:2000',
            'photo'               => 'nullable|image|max:4096',
            'password'            => 'required|string|min:8|confirmed',
            'roles'               => 'nullable|array',
            'roles.*'             => 'integer|exists:roles,id',
        ]);

        $data = $request->only([
            'name', 'username', 'email', 'phone', 'national_id', 'hire_date',
            'base_salary', 'commission_percent', 'employment_status', 'address', 'notes',
        ]);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filename = uploadImage('assets/uploads/employees', $request->file('photo'));
            $data['photo'] = 'assets/uploads/employees/' . $filename;
        }

        $employee = Admin::create($data);

        if ($request->filled('roles')) {
            $employee->syncRoles(
                Role::whereIn('id', $request->roles)->where('guard_name', 'admin')->get()
            );
        }

        return redirect()->route('admin.employee.index')
            ->with('success', __('messages.employee_created'));
    }

    public function edit(int $id)
    {
        $employee     = Admin::where('is_super', false)->findOrFail($id);
        $roles        = Role::where('guard_name', 'admin')->orderBy('name')->get();
        $assignedRoles = $employee->roles->pluck('id')->toArray();

        return view('admin.employee.edit', compact('employee', 'roles', 'assignedRoles'));
    }

    public function update(Request $request, int $id)
    {
        $employee = Admin::where('is_super', false)->findOrFail($id);

        $request->validate([
            'name'                => 'required|string|max:200',
            'username'            => 'required|string|max:100|unique:admins,username,' . $id,
            'email'               => 'nullable|email|max:200|unique:admins,email,' . $id,
            'phone'               => 'nullable|string|max:30',
            'national_id'         => 'nullable|string|max:30',
            'hire_date'           => 'nullable|date',
            'base_salary'         => 'nullable|numeric|min:0',
            'commission_percent'  => 'nullable|numeric|min:0|max:100',
            'employment_status'   => 'required|in:active,on_leave,terminated',
            'address'             => 'nullable|string|max:500',
            'notes'               => 'nullable|string|max:2000',
            'photo'               => 'nullable|image|max:4096',
            'password'            => 'nullable|string|min:8|confirmed',
            'roles'               => 'nullable|array',
            'roles.*'             => 'integer|exists:roles,id',
        ]);

        $data = $request->only([
            'name', 'username', 'email', 'phone', 'national_id', 'hire_date',
            'base_salary', 'commission_percent', 'employment_status', 'address', 'notes',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $filename = uploadImage('assets/uploads/employees', $request->file('photo'));
            $data['photo'] = 'assets/uploads/employees/' . $filename;
        }

        $employee->update($data);
        $employee->syncRoles(
            Role::whereIn('id', $request->input('roles', []))->where('guard_name', 'admin')->get()
        );

        return redirect()->route('admin.employee.index')
            ->with('success', __('messages.employee_updated'));
    }

    public function destroy(int $id)
    {
        $employee = Admin::where('is_super', false)->findOrFail($id);
        $employee->syncRoles([]);
        $employee->delete();

        return back()->with('success', __('messages.employee_deleted'));
    }
}
