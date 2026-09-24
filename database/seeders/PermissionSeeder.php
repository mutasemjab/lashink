<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // ── Roles & Employees ──────────────────────────────────────────
            'role-table',             'role-add',             'role-edit',             'role-delete',
            'employee-table',         'employee-add',         'employee-edit',         'employee-delete',

            // ── Clients ─────────────────────────────────────────────────────
            'client-table',           'client-add',           'client-edit',           'client-delete',

            // ── Services ────────────────────────────────────────────────────
            'service-category-table', 'service-category-add', 'service-category-edit', 'service-category-delete',
            'service-table',          'service-add',          'service-edit',          'service-delete',

            // ── Appointments / Calendar ────────────────────────────────────
            'appointment-table',      'appointment-add',      'appointment-edit',      'appointment-delete',

            // ── Inventory ───────────────────────────────────────────────────
            'supplier-table',         'supplier-add',         'supplier-edit',         'supplier-delete',
            'product-category-table', 'product-category-add', 'product-category-edit', 'product-category-delete',
            'product-table',          'product-add',          'product-edit',          'product-delete',
            'purchase-table',         'purchase-add',         'purchase-edit',         'purchase-delete',

            // ── Accounting ──────────────────────────────────────────────────
            'expense-category-table', 'expense-category-add', 'expense-category-edit', 'expense-category-delete',
            'expense-table',          'expense-add',          'expense-edit',          'expense-delete',
            'invoice-table',          'invoice-add',          'invoice-edit',          'invoice-delete',

            // ── HR: Payroll / Leaves / Advances ───────────────────────────
            'payroll-table',          'payroll-add',          'payroll-edit',
            'leave-table',            'leave-add',             'leave-edit',            'leave-delete',
            'advance-table',          'advance-add',           'advance-edit',          'advance-delete',
            'attendance-table',       'attendance-add',        'attendance-edit',       'attendance-delete',

            // ── Reports & Settings ─────────────────────────────────────────
            'report-view',
            'setting-edit',

            // ── Activity Log ───────────────────────────────────────────────
            'activity-log-table',     'activity-log-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }
    }
}
