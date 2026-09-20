<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SalaryAdvanceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SupplierController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Permission\Models\Permission;

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {

    Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {

        // ── Dashboard ─────────────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

        // ── Admin profile ─────────────────────────────────────────────
        Route::get('/admin/edit/{id}',    [LoginController::class, 'editlogin'])->name('admin.login.edit');
        Route::post('/admin/update/{id}', [LoginController::class, 'updatelogin'])->name('admin.login.update');

        // ── Roles & Employees ─────────────────────────────────────────
        Route::resource('employee', EmployeeController::class, ['as' => 'admin'])->except(['show']);
        Route::get('role',               [RoleController::class, 'index'])->name('admin.role.index');
        Route::get('role/create',        [RoleController::class, 'create'])->name('admin.role.create');
        Route::get('role/{id}/edit',     [RoleController::class, 'edit'])->name('admin.role.edit');
        Route::patch('role/{id}',        [RoleController::class, 'update'])->name('admin.role.update');
        Route::post('role',              [RoleController::class, 'store'])->name('admin.role.store');
        Route::post('admin/role/delete',  [RoleController::class, 'delete'])->name('admin.role.delete');
        Route::delete('role/{id}',        [RoleController::class, 'destroy'])->name('admin.role.destroy');

        Route::get('/permissions/{guard_name}', function ($guard_name) {
            return response()->json(Permission::where('guard_name', $guard_name)->get());
        });

        // ── Catalog & Clients ───────────────────────────────────────
        Route::resource('service-category', ServiceCategoryController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('service', ServiceController::class, ['as' => 'admin'])->except(['show']);
        Route::resource('client', ClientController::class, ['as' => 'admin'])->except(['show']);
        Route::get('client/search', [ClientController::class, 'search'])->name('admin.client.search');

        // ── Appointments & Calendar ─────────────────────────────────
        Route::get('appointment/daily-report', [ReportController::class, 'daily'])->name('admin.report.daily');
        Route::get('appointment', [AppointmentController::class, 'index'])->name('admin.appointment.index');
        Route::get('appointment/events', [AppointmentController::class, 'events'])->name('admin.appointment.events');
        Route::get('appointment/clients/search', [AppointmentController::class, 'searchClients'])->name('admin.appointment.clients.search');
        Route::get('appointment/staff/search', [AppointmentController::class, 'searchStaff'])->name('admin.appointment.staff.search');
        Route::post('appointment', [AppointmentController::class, 'store'])->name('admin.appointment.store');
        Route::put('appointment/{appointment}', [AppointmentController::class, 'update'])->name('admin.appointment.update');
        Route::patch('appointment/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('admin.appointment.reschedule');
        Route::post('appointment/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('admin.appointment.status');
        Route::delete('appointment/{appointment}', [AppointmentController::class, 'destroy'])->name('admin.appointment.destroy');

        // ── Inventory ─────────────────────────────────────────────
        Route::resource('supplier', SupplierController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('product-category', ProductCategoryController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('product', ProductController::class, ['as' => 'admin'])->except(['show']);
        Route::post('product/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('admin.product.adjust-stock');
        Route::resource('purchase', PurchaseController::class, ['as' => 'admin'])->only(['index', 'create', 'store', 'show', 'destroy']);

        // ── Accounting ────────────────────────────────────────────
        Route::resource('expense-category', ExpenseCategoryController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('expense', ExpenseController::class, ['as' => 'admin'])->except(['show']);
        Route::get('invoice/appointments/search', [InvoiceController::class, 'searchAppointments'])->name('admin.invoice.appointments.search');
        Route::get('invoice/appointments/{appointment}', [InvoiceController::class, 'appointmentData'])->name('admin.invoice.appointments.data');
        Route::resource('invoice', InvoiceController::class, ['as' => 'admin'])->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::get('invoice/{invoice}/details', [InvoiceController::class, 'details'])->name('admin.invoice.details');
        Route::get('invoice/{invoice}/print', [InvoiceController::class, 'print'])->name('admin.invoice.print');
        Route::post('invoice/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('admin.invoice.payment');
        Route::post('invoice/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('admin.invoice.cancel');

        // ── HR: Leaves, Advances, Payroll ───────────────────────────
        Route::resource('attendance', AttendanceController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('leave-type', LeaveTypeController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('leave-request', LeaveRequestController::class, ['as' => 'admin'])->only(['index', 'create', 'store', 'destroy']);
        Route::post('leave-request/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('admin.leave-request.approve');
        Route::post('leave-request/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('admin.leave-request.reject');

        Route::resource('salary-advance', SalaryAdvanceController::class, ['as' => 'admin'])->only(['index', 'create', 'store', 'destroy']);
        Route::post('salary-advance/{salary_advance}/approve', [SalaryAdvanceController::class, 'approve'])->name('admin.salary-advance.approve');
        Route::post('salary-advance/{salary_advance}/reject', [SalaryAdvanceController::class, 'reject'])->name('admin.salary-advance.reject');

        Route::get('payroll', [PayrollController::class, 'index'])->name('admin.payroll.index');
        Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('admin.payroll.generate');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('admin.payroll.show');
        Route::post('payroll/{payroll}/finalize', [PayrollController::class, 'finalize'])->name('admin.payroll.finalize');
        Route::post('payroll-item/{item}/pay', [PayrollController::class, 'markPaid'])->name('admin.payroll.mark-paid');

        // ── Reports & Settings ───────────────────────────────────────
        Route::get('report', [ReportController::class, 'index'])->name('admin.report.index');
        Route::get('report/export-revenue', [ReportController::class, 'exportRevenue'])->name('admin.report.export-revenue');
        Route::get('report/export-expenses', [ReportController::class, 'exportExpenses'])->name('admin.report.export-expenses');
        Route::get('settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
        Route::post('settings', [SettingController::class, 'update'])->name('admin.settings.update');
        Route::resource('currency', CurrencyController::class, ['as' => 'admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::post('currency/{currency}/make-default', [CurrencyController::class, 'makeDefault'])->name('admin.currency.make-default');

    });
});

Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => 'guest:admin'], function () {
    Route::get('login',  [LoginController::class, 'show_login_view'])->name('admin.showlogin');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login');
});
