@php
    $u = auth('admin')->user();
@endphp

<aside class="sidebar" id="sidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-stars"></i></div>
        <span class="brand-text">{{ __('messages.edu_platform') }}</span>
    </div>

    <nav class="sidebar-nav">

        {{-- ── Main ─────────────────────────────────────────── --}}
        <div class="nav-label">{{ __('messages.main') }}</div>
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <span>{{ __('messages.dashboard') }}</span>
                </a>
            </li>
        </ul>

        {{-- ── Appointments ─────────────────────────────────── --}}
        @if($u?->can('appointment-table'))
        <div class="nav-label">{{ __('messages.nav_appointments') }}</div>
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.appointment.index') }}"
                   class="nav-link {{ request()->routeIs('admin.appointment.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar2-week"></i>
                    <span>{{ __('messages.nav_appointments') }}</span>
                </a>
            </li>
            @can('report-view')
            <li class="nav-item">
                <a href="{{ route('admin.report.daily') }}"
                   class="nav-link {{ request()->routeIs('admin.report.daily') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-journal-text"></i>
                    <span>{{ __('messages.daily_accounts') }}</span>
                </a>
            </li>
            @endcan
        </ul>
        @endif

        {{-- ── Catalog & Clients ────────────────────────────── --}}
        @if($u?->canAny(['client-table','service-category-table','service-table']))
        <div class="nav-label">{{ __('messages.nav_clients') }}</div>
        <ul>
            @if($u?->can('client-table'))
            <li class="nav-item">
                <a href="{{ route('admin.client.index') }}"
                   class="nav-link {{ request()->routeIs('admin.client.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-hearts"></i>
                    <span>{{ __('messages.clients') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('service-category-table'))
            <li class="nav-item">
                <a href="{{ route('admin.service-category.index') }}"
                   class="nav-link {{ request()->routeIs('admin.service-category.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-tags"></i>
                    <span>{{ __('messages.service_categories') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('service-table'))
            <li class="nav-item">
                <a href="{{ route('admin.service.index') }}"
                   class="nav-link {{ request()->routeIs('admin.service.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-scissors"></i>
                    <span>{{ __('messages.services') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        {{-- ── Inventory ────────────────────────────────────── --}}
        @if($u?->canAny(['supplier-table','product-category-table','product-table','purchase-table']))
        <div class="nav-label">{{ __('messages.nav_inventory') }}</div>
        <ul>
            @if($u?->can('product-table'))
            <li class="nav-item">
                <a href="{{ route('admin.product.index') }}" class="nav-link {{ request()->routeIs('admin.product.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-box-seam"></i><span>{{ __('messages.products') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('product-category-table'))
            <li class="nav-item">
                <a href="{{ route('admin.product-category.index') }}" class="nav-link {{ request()->routeIs('admin.product-category.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-tags"></i><span>{{ __('messages.product_categories') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('purchase-table'))
            <li class="nav-item">
                <a href="{{ route('admin.purchase.index') }}" class="nav-link {{ request()->routeIs('admin.purchase.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-receipt-cutoff"></i><span>{{ __('messages.purchases') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('supplier-table'))
            <li class="nav-item">
                <a href="{{ route('admin.supplier.index') }}" class="nav-link {{ request()->routeIs('admin.supplier.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-truck"></i><span>{{ __('messages.suppliers') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        {{-- ── Accounting ───────────────────────────────────── --}}
        @if($u?->canAny(['invoice-table','expense-table','expense-category-table']))
        <div class="nav-label">{{ __('messages.nav_accounting') }}</div>
        <ul>
            @if($u?->can('invoice-table'))
            <li class="nav-item">
                <a href="{{ route('admin.invoice.index') }}" class="nav-link {{ request()->routeIs('admin.invoice.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-receipt"></i><span>{{ __('messages.invoices') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('expense-table'))
            <li class="nav-item">
                <a href="{{ route('admin.expense.index') }}" class="nav-link {{ request()->routeIs('admin.expense.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-cash-stack"></i><span>{{ __('messages.expenses') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('expense-category-table'))
            <li class="nav-item">
                <a href="{{ route('admin.expense-category.index') }}" class="nav-link {{ request()->routeIs('admin.expense-category.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-tags"></i><span>{{ __('messages.expense_categories') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        {{-- ── HR ───────────────────────────────────────────── --}}
        @if($u?->canAny(['payroll-table','leave-table','advance-table','attendance-table']))
        <div class="nav-label">{{ __('messages.nav_hr') }}</div>
        <ul>
            @if($u?->can('attendance-table'))
            <li class="nav-item">
                <a href="{{ route('admin.attendance.index') }}" class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-clock-history"></i><span>{{ __('messages.nav_attendance') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('payroll-table'))
            <li class="nav-item">
                <a href="{{ route('admin.payroll.index') }}" class="nav-link {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-cash-coin"></i><span>{{ __('messages.payroll') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('leave-table'))
            <li class="nav-item">
                <a href="{{ route('admin.leave-request.index') }}" class="nav-link {{ request()->routeIs('admin.leave-request.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar2-x"></i><span>{{ __('messages.leave_requests') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.leave-type.index') }}" class="nav-link {{ request()->routeIs('admin.leave-type.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-list-check"></i><span>{{ __('messages.leave_types') }}</span>
                </a>
            </li>
            @endif
            @if($u?->can('advance-table'))
            <li class="nav-item">
                <a href="{{ route('admin.salary-advance.index') }}" class="nav-link {{ request()->routeIs('admin.salary-advance.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-wallet2"></i><span>{{ __('messages.salary_advances') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        {{-- ── Reports ──────────────────────────────────────── --}}
        @if($u?->can('report-view'))
        <div class="nav-label">{{ __('messages.nav_reports') }}</div>
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.report.index') }}" class="nav-link {{ request()->routeIs('admin.report.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-graph-up"></i><span>{{ __('messages.nav_reports') }}</span>
                </a>
            </li>
        </ul>
        @endif

              {{-- ── System ────────────────────────────────────────── --}}
        @if($u?->canAny(['role-table','employee-table','activity-log-table','contact-message-table','setting-edit']))
        <div class="nav-label">{{ __('messages.system') }}</div>
        <ul>
            @if($u?->can('role-table'))
            <li class="nav-item">
                <a href="{{ route('admin.role.index') }}"
                   class="nav-link {{ request()->routeIs('admin.role.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-shield-check"></i>
                    <span>{{ __('messages.roles_permissions') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('employee-table'))
            <li class="nav-item">
                <a href="{{ route('admin.employee.index') }}"
                   class="nav-link {{ request()->routeIs('admin.employee.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-people"></i>
                    <span>{{ __('messages.employees') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('setting-edit'))
            <li class="nav-item">
                <a href="{{ route('admin.settings.edit') }}"
                   class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-shop"></i>
                    <span>{{ __('messages.salon_settings') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.currency.index') }}"
                   class="nav-link {{ request()->routeIs('admin.currency.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-currency-exchange"></i>
                    <span>{{ __('messages.currencies') }}</span>
                </a>
            </li>
            @endif

        </ul>
        @endif

    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer">
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.login.edit', auth('admin')->id()) }}" class="nav-link">
                    <i class="nav-icon bi bi-person-circle"></i>
                    <span>{{ __('messages.my_profile') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link"
                   onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                    <i class="nav-icon bi bi-box-arrow-right"></i>
                    <span>{{ __('messages.sign_out') }}</span>
                </a>
            </li>
        </ul>
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="{{ __('messages.collapse_sidebar') }}">
            <i class="bi bi-arrow-bar-left"></i>
        </button>
    </div>

</aside>
