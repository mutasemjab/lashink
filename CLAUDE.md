# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this app is

A Laravel 9 admin panel for salon/spa business management ("lashink") — appointments, clients,
employees, services, inventory (products/suppliers/purchases/stock), accounting (invoices/expenses),
and HR (leave requests, salary advances, payroll). There is currently no public-facing site or API:
`routes/web.php` and `routes/api.php` are empty stubs, and all real functionality lives behind
`routes/admin.php` under the `admin` auth guard.

**Known leftover state from a prior project:** the codebase was repurposed from an earlier
"Baheth" school/course app (Flutter app + Apple IAP course purchases). Remnants that no longer
resolve are still present and will break if touched without cleanup first:
- `config/auth.php` defines `user`, `teacher`, `student` guards/providers pointing at
  `App\Models\User`, `Teacher`, `Student` — none of these models exist in `app/Models`.
- `app/Providers/AppServiceProvider.php` has `use` statements for `App\Contracts\AppleTransactionVerifier`
  and `App\Services\Apple\AppleSignedTransactionVerifier` — neither namespace exists.
- `tests/Feature/AppleInAppPurchaseTest.php` and `tests/Unit/Apple*.php` reference
  `App\Models\Course`, `App\Models\Student`, `App\Models\Teacher`, and `App\Services\Apple\*` —
  none exist, so these tests will fail to run as-is.
- `app/Helpers/General.php`'s `sett()`/`sett_raw()` helpers call `App\Models\SiteSetting`, which
  doesn't exist; the actual settings model is `App\Models\Setting` (simple `get`/`set` key-value
  store cached forever, see `app/Models/Setting.php`). Use `Setting::get()`/`Setting::set()`, not
  the `sett()` helpers, until/unless that's fixed.
- The README still documents the old Apple IAP / StoreKit flow, `config/apple_iap.php`, and
  `app/Exceptions/ApplePurchaseException.php`, which remain in the tree but are unused by any
  current controller or route.

Only the `admin` guard (`App\Models\Admin`, table `admins`) is live and wired to real routes.

## Commands

- Install PHP deps: `composer install`
- Install JS deps: `npm install`
- Dev server: `php artisan serve`
- Frontend asset dev/watch: `npm run dev` (Vite)
- Frontend build: `npm run build`
- Run migrations: `php artisan migrate`
- Run all tests: `php artisan test` or `vendor/bin/phpunit`
- Run a single test file: `vendor/bin/phpunit tests/Unit/ExampleTest.php`
- Run a single test method: `vendor/bin/phpunit --filter testMethodName`
- Code style (Pint): `vendor/bin/pint`

Note: the Apple-related tests above will error on missing classes; don't treat their failure as a
regression from unrelated changes unless you're specifically working on that area.

## Architecture

**Routing & auth guard split.** `App\Providers\RouteServiceProvider::boot()` wires three route
files under three different middleware groups: `web.php` (web middleware, currently empty),
`api.php` (api middleware, currently empty), and `admin.php` (custom `admin` middleware group).
Inside `admin.php`, everything is additionally wrapped in `auth:admin` plus
`mcamara/laravel-localization` locale middleware (`localeSessionRedirect`,
`localizationRedirect`, `localeViewPath`), so admin URLs are locale-prefixed
(e.g. `/en/admin/...`, `/ar/admin/...`). Login/logout for the admin guard sits outside that group
in a `guest:admin` block at the bottom of `admin.php`.

**Controllers → Services → Models for money-touching flows.** Most CRUD (clients, employees,
services, products, etc.) is plain controller + Eloquent model. The two flows with real business
logic — invoicing and payroll — are pulled into `app/Services`:
- `InvoiceService::create()` builds an invoice with line items in a DB transaction, computing
  employee commissions and deducting stock for both direct product sales and service "recipe"
  product consumption (see `AppointmentService`/`Service` recipe relations).
- `PayrollService` generates payroll runs/items from attendance, leave, and salary advance data.

Follow this pattern for new money- or stock-affecting features: keep the transactional,
multi-model logic in a service class rather than the controller.

**Inventory/stock.** `Product`, `StockMovement`, `Purchase`/`PurchaseItem`, `Supplier`, and
`ProductCategory` model a standard inventory flow: purchases increase stock, invoices/appointments
decrease it via `StockMovement` records. `ProductController::adjustStock` is a manual
correction endpoint.

**HR/payroll chain.** `Employee` → `LeaveType`/`LeaveRequest`/`LeaveBalance` (leave accrual and
approval), `SalaryAdvance` (approved advances deducted from payroll), and
`PayrollRun`/`PayrollItem` (generated batches, finalized then paid item-by-item via
`PayrollController::markPaid`). Leave requests and salary advances both follow a
create → approve/reject flow (see the `approve`/`reject` actions in `LeaveRequestController` and
`SalaryAdvanceController`).

**Roles & permissions.** Uses `spatie/laravel-permission` scoped to the `admin` guard.
`RoleController` manages roles; permissions are fetched per guard via the
`GET /admin/permissions/{guard_name}` JSON endpoint (used by the role edit UI).

**Localization.** `mcamara/laravel-localization` drives locale-prefixed routes/views
(`resources/lang/en`, `resources/lang/ar`); `spatie/laravel-translatable` is available for
translatable model attributes. Admin views live under `resources/views/admin/{feature}`, with
shared chrome in `resources/views/admin/layouts` and `resources/views/admin/includes`.

**Exports.** `maatwebsite/excel` powers report exports (`ReportController::exportRevenue`,
`exportExpenses`) via classes in `app/Exports`.

**Frontend build.** Plain Vite + Laravel Vite plugin (no framework) — entry points are
`resources/css/app.css` and `resources/js/app.js`, no component framework configured.
