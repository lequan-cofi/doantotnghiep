<?php

use Illuminate\Support\Facades\Route;

// Include test routes (remove in production)
if (app()->environment('local')) {
    require __DIR__.'/test-soft-delete.php';
}
use App\Http\Controllers\Auth\EmailAuthController;
use App\Http\Controllers\Manager\PropertyController;
use App\Http\Controllers\Manager\LeaseController;
use App\Http\Controllers\Agent\InvoiceController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/search', [\App\Http\Controllers\HomeController::class, 'search'])->name('search');

// Public property routes - accessible without authentication
Route::get('/properties', [\App\Http\Controllers\PropertyController::class, 'index'])->name('property.index');
Route::get('/properties/{id}', [\App\Http\Controllers\PropertyController::class, 'show'])->name('property.show');


Route::get('/demo/preloader', function () {
    return view('demo.preloader');
})->name('demo.preloader');

Route::get('/demo/notifications', function () {
    return view('demo.notifications');
})->name('demo.notifications');

Route::get('/news', function () {
    return view('news.index');
})->name('news.index');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/detail/{id?}', function ($id = 1) {
    return view('detail', compact('id'));
})->name('detail');

// Property detail route
Route::get('/property/{id}', [\App\Http\Controllers\HomeController::class, 'propertyDetail'])->name('property.detail');

/*
|--------------------------------------------------------------------------
| Booking Routes (Public - No authentication required)
|--------------------------------------------------------------------------
*/
Route::prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/booking/{id?}/{unit_id?}', [\App\Http\Controllers\ViewingController::class, 'booking'])->name('booking');
    Route::post('/booking/{id?}', [\App\Http\Controllers\ViewingController::class, 'store'])->name('booking.store');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [EmailAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [EmailAuthController::class, 'login'])->name('login.store');
Route::get('/register', [EmailAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [EmailAuthController::class, 'register'])->name('register.store');
Route::post('/logout', [EmailAuthController::class, 'logout'])->name('logout');
Route::get('/logout', [EmailAuthController::class, 'logout'])->name('logout.get');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Default dashboard resolver (redirects to role-specific dashboard)
    Route::get('/dashboard', function () {
        $roleKey = session('auth_role_key');
        if (! $roleKey && \Illuminate\Support\Facades\Auth::check()) {
            $userId = \Illuminate\Support\Facades\Auth::id();
            $record = \Illuminate\Support\Facades\DB::table('organization_users')
                ->join('roles', 'roles.id', '=', 'organization_users.role_id')
                ->where('organization_users.user_id', $userId)
                ->where('organization_users.status', 'active')
                ->orderBy('roles.id')
                ->select('roles.key_code')
                ->first();
            $roleKey = $record->key_code ?? null;
        }

        $routeByRole = [
            'admin' => 'superadmin.dashboard',
            'manager' => 'manager.dashboard',
            'agent' => 'agent.dashboard',
            'landlord' => 'landlord.dashboard',
            'tenant' => 'tenant.dashboard',
        ];

        $target = $routeByRole[$roleKey] ?? 'home';
        return redirect()->route($target);
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN Routes (ensure.admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('ensure.admin')->group(function () {
        // Dashboard
       
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN Routes (superadmin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'ensure.admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'index'])->name('dashboard');
        Route::post('/clear-cache', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'clearCache'])->name('clear-cache');
        
        // Organizations Management
        Route::get('/organizations', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/create', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('/organizations', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('/organizations/{organization}', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'show'])->name('organizations.show');
        Route::get('/organizations/{organization}/edit', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::put('/organizations/{organization}', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::post('/organizations/{organization}/toggle-status', [\App\Http\Controllers\SuperAdmin\OrganizationController::class, 'toggleStatus'])->name('organizations.toggle-status');
        
        // Users Management
        Route::get('/users', [\App\Http\Controllers\SuperAdmin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\SuperAdmin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\SuperAdmin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\SuperAdmin\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\SuperAdmin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\SuperAdmin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\SuperAdmin\UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\SuperAdmin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Revenue Analytics
        // Route::get('/revenue', [\App\Http\Controllers\SuperAdmin\RevenueController::class, 'index'])->name('revenue.index');
        
        // System Management
        // Route::get('/system/health', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'health'])->name('system.health');
        
        // Support
        // Route::get('/support/tickets', [\App\Http\Controllers\SuperAdmin\SupportController::class, 'tickets'])->name('support.tickets');
    });

    /*
    |--------------------------------------------------------------------------
    | MANAGER Routes (ensure.manager)
    |--------------------------------------------------------------------------
    */
    Route::prefix('manager')->name('manager.')->middleware(['ensure.manager', 'check.organization'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/clear-cache', [\App\Http\Controllers\Manager\DashboardController::class, 'clearCache'])->name('dashboard.clear-cache');

        // Properties CRUD
        Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::get('/properties/{id}', [PropertyController::class, 'show'])->name('properties.show');
        Route::get('/properties/{id}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('/properties/{id}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])->name('properties.destroy');

        // Property Types CRUD
        Route::resource('property-types', \App\Http\Controllers\Manager\PropertyTypeController::class);
        Route::get('/api/property-types/options', [\App\Http\Controllers\Manager\PropertyTypeController::class, 'getOptions']);
        
        // Users CRUD
        Route::get('/users', [\App\Http\Controllers\Manager\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Manager\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Manager\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [\App\Http\Controllers\Manager\UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\Manager\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [\App\Http\Controllers\Manager\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [\App\Http\Controllers\Manager\UserController::class, 'destroy'])->name('users.destroy');
        
        // Leases CRUD
        Route::resource('leases', LeaseController::class);
        
        
        // Staff (CTV/Nhân viên) management
        // Staff Management
        Route::resource('staff', \App\Http\Controllers\Manager\StaffController::class);
        Route::get('/staff/{id}/salary-contracts', [\App\Http\Controllers\Manager\StaffController::class, 'getSalaryContracts'])->name('staff.salary-contracts');
        Route::get('/staff/{id}/commission-events', [\App\Http\Controllers\Manager\StaffController::class, 'getCommissionEvents'])->name('staff.commission-events');
        Route::post('/staff/{id}/assign-properties', [\App\Http\Controllers\Manager\StaffController::class, 'assignProperties'])->name('staff.assign-properties');
        
        // Leases
        Route::resource('leases', \App\Http\Controllers\Manager\LeaseController::class);
        
        // Invoices
        Route::resource('invoices', \App\Http\Controllers\Manager\InvoiceController::class);
        
        // Ticket management
        Route::resource('tickets', \App\Http\Controllers\Manager\TicketController::class);
        Route::post('tickets/{ticket}/logs', [\App\Http\Controllers\Manager\TicketController::class, 'addLog'])->name('tickets.addLog');
        
        // Commission Policies
        Route::resource('commission-policies', \App\Http\Controllers\Manager\CommissionPolicyController::class);
        
        // Commission Events
        Route::resource('commission-events', \App\Http\Controllers\Manager\CommissionEventController::class);
        Route::post('commission-events/{commissionEvent}/approve', [\App\Http\Controllers\Manager\CommissionEventController::class, 'approve'])->name('commission-events.approve');
        Route::post('commission-events/{commissionEvent}/mark-as-paid', [\App\Http\Controllers\Manager\CommissionEventController::class, 'markAsPaid'])->name('commission-events.mark-as-paid');
        
        // Payroll Cycles
        Route::resource('payroll-cycles', \App\Http\Controllers\Manager\PayrollCycleController::class);
        Route::post('payroll-cycles/{payrollCycle}/lock', [\App\Http\Controllers\Manager\PayrollCycleController::class, 'lock'])->name('payroll-cycles.lock');
        Route::post('payroll-cycles/{payrollCycle}/generate-payslips', [\App\Http\Controllers\Manager\PayrollCycleController::class, 'generatePayslips'])->name('payroll-cycles.generate-payslips');
        
        // Payroll Payslips
        Route::resource('payroll-payslips', \App\Http\Controllers\Manager\PayrollPayslipController::class);
        Route::post('payroll-payslips/{payrollPayslip}/mark-as-paid', [\App\Http\Controllers\Manager\PayrollPayslipController::class, 'markAsPaid'])->name('payroll-payslips.mark-as-paid');
        Route::post('payroll-payslips/{payrollPayslip}/recalculate', [\App\Http\Controllers\Manager\PayrollPayslipController::class, 'recalculate'])->name('payroll-payslips.recalculate');
        
        // Salary Advances
        Route::resource('salary-advances', \App\Http\Controllers\Manager\SalaryAdvanceController::class);
        Route::post('salary-advances/{salaryAdvance}/approve', [\App\Http\Controllers\Manager\SalaryAdvanceController::class, 'approve'])->name('salary-advances.approve');
        Route::post('salary-advances/{salaryAdvance}/reject', [\App\Http\Controllers\Manager\SalaryAdvanceController::class, 'reject'])->name('salary-advances.reject');
        Route::post('salary-advances/{salaryAdvance}/repayment', [\App\Http\Controllers\Manager\SalaryAdvanceController::class, 'addRepayment'])->name('salary-advances.repayment');
        
    // Salary Contracts
    Route::resource('salary-contracts', \App\Http\Controllers\Manager\SalaryContractController::class);
    Route::post('salary-contracts/{salaryContract}/terminate', [\App\Http\Controllers\Manager\SalaryContractController::class, 'terminate'])->name('salary-contracts.terminate');
    Route::post('salary-contracts/{salaryContract}/activate', [\App\Http\Controllers\Manager\SalaryContractController::class, 'activate'])->name('salary-contracts.activate');

    // Revenue Reports
    Route::get('revenue-reports', [\App\Http\Controllers\Manager\RevenueReportController::class, 'index'])->name('revenue-reports.index');
    Route::get('revenue-reports/detail', [\App\Http\Controllers\Manager\RevenueReportController::class, 'detail'])->name('revenue-reports.detail');
        
        // Reports - using new revenue reports system
        
        Route::get('/reports/payments', function () {
            return view('manager.reports.payments');
        })->name('reports.payments');
        
        // Profile
        Route::get('/profile', function () {
            return view('manager.profile');
        })->name('profile');
        
        // Settings
        Route::get('/settings/general', function () {
            return view('manager.settings.general');
        })->name('settings.general');
        
        // Payment Cycle Settings
        Route::prefix('payment-cycle-settings')->name('payment-cycle-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'index'])->name('index');
            Route::put('/organization', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'updateOrganization'])->name('organization.update');
            Route::put('/property/{propertyId}', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'updateProperty'])->name('property.update');
            Route::put('/lease/{leaseId}', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'updateLease'])->name('lease.update');
            Route::get('/property/{propertyId}/leases', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'getPropertyLeases'])->name('property.leases');
            Route::post('/apply-to-properties', [\App\Http\Controllers\Manager\PaymentCycleSettingController::class, 'applyToProperties'])->name('apply-to-properties');
        });
        
        // Legacy rooms routes (admin context) - REMOVED FOR NOW
        // Will be re-implemented later when needed
        // API endpoints for geo data (cascading dropdowns)
        Route::prefix('api/geo')->group(function () {
            Route::get('/districts/{provinceCode}', [PropertyController::class, 'getDistricts']);
            Route::get('/wards/{districtCode}', [PropertyController::class, 'getWards']);
            Route::get('/wards-2025/{provinceCode}', [PropertyController::class, 'getWards2025']);
        });

        // API endpoints for properties
        Route::prefix('api/properties')->group(function () {
            Route::get('/{propertyId}/units', [LeaseController::class, 'getUnits']);
            Route::get('/{propertyId}/payment-cycle', [LeaseController::class, 'getPropertyPaymentCycle']);
        });

        // API endpoints for leases
        Route::prefix('api/leases')->group(function () {
            Route::get('/next-contract-number', [LeaseController::class, 'getNextContractNumber']);
        });

        // API endpoints for invoices
        Route::prefix('api/invoices')->group(function () {
            Route::get('/leases/{leaseId}/details', [\App\Http\Controllers\Manager\InvoiceController::class, 'getLeaseDetails']);
        });

        Route::prefix('api/tickets')->group(function () {
            Route::get('/properties/{propertyId}/units', [\App\Http\Controllers\Manager\TicketController::class, 'getUnits']);
            Route::get('/units/{unitId}/leases', [\App\Http\Controllers\Manager\TicketController::class, 'getLeases']);
        });
        Route::prefix('api/revenue-reports')->group(function () {
            Route::get('/detailed', [\App\Http\Controllers\Manager\RevenueReportController::class, 'getDetailedData']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | AGENT Routes (ensure.agent)
    |--------------------------------------------------------------------------
    */
    Route::prefix('agent')->name('agent.')->middleware('ensure.agent')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Agent\DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', function () {
            return view('agent.dashboard');
        })->name('profile');

        // Properties management (read-only for agents)
        Route::get('/properties', [\App\Http\Controllers\Agent\PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/{id}', [\App\Http\Controllers\Agent\PropertyController::class, 'show'])->name('properties.show');

        // Property Types management (read-only for agents)
        Route::get('/property-types', [\App\Http\Controllers\Agent\PropertyTypeController::class, 'index'])->name('property-types.index');

        // Units management (CRUD)
        Route::resource('units', \App\Http\Controllers\Agent\UnitController::class);


        // Rented properties
        Route::get('/rented', [\App\Http\Controllers\Agent\RentedController::class, 'index'])->name('rented.index');
        Route::get('/rented/{id}', [\App\Http\Controllers\Agent\RentedController::class, 'show'])->name('rented.show');
        
        // AJAX routes for rented properties
        Route::get('/rented/lease/{leaseId}/details', [\App\Http\Controllers\Agent\RentedController::class, 'getLeaseDetails'])->name('rented.lease.details');
        Route::get('/rented/tenant/{tenantId}/profile', [\App\Http\Controllers\Agent\RentedController::class, 'getTenantProfile'])->name('rented.tenant.profile');

        // Leases management (CRUD)
        Route::resource('leases', \App\Http\Controllers\Agent\LeaseController::class);
        
        // Lease residents management
        Route::post('/leases/{leaseId}/residents', [\App\Http\Controllers\Agent\LeaseController::class, 'addResident'])->name('leases.residents.add');
        Route::put('/leases/{leaseId}/residents/{residentId}', [\App\Http\Controllers\Agent\LeaseController::class, 'updateResident'])->name('leases.residents.update');
        Route::delete('/leases/{leaseId}/residents/{residentId}', [\App\Http\Controllers\Agent\LeaseController::class, 'deleteResident'])->name('leases.residents.delete');
        
        // Leads management (CRUD)
        Route::resource('leads', \App\Http\Controllers\Agent\LeadController::class);
        Route::put('/leads/{id}/status', [\App\Http\Controllers\Agent\LeadController::class, 'updateStatus'])->name('leads.update-status');
        Route::get('/leads-statistics', [\App\Http\Controllers\Agent\LeadController::class, 'statistics'])->name('leads.statistics');
        
        // Convert lead to lease
        Route::get('/leads/{leadId}/create-lease', [\App\Http\Controllers\Agent\LeaseController::class, 'createFromLead'])->name('leads.create-lease');
        Route::post('/leads/{leadId}/create-lease', [\App\Http\Controllers\Agent\LeaseController::class, 'storeFromLead'])->name('leads.store-lease');
        
        // Link tenant to lease when lead creates account
        Route::post('/leases/{leaseId}/link-tenant', [\App\Http\Controllers\Agent\LeaseController::class, 'linkTenantToLease'])->name('leases.link-tenant');
        Route::get('/api/leases/needing-tenant-link', [\App\Http\Controllers\Agent\LeaseController::class, 'getLeasesNeedingTenantLink'])->name('api.leases.needing-tenant-link');
        
        // Meters management (CRUD)
        Route::resource('meters', \App\Http\Controllers\Agent\MeterController::class);
        Route::get('/meters/get-units', [\App\Http\Controllers\Agent\MeterController::class, 'getUnits'])->name('meters.get-units');
        
        // Meter readings management (CRUD)
        Route::resource('meter-readings', \App\Http\Controllers\Agent\MeterReadingController::class);
        Route::get('/meter-readings/get-last-reading', [\App\Http\Controllers\Agent\MeterReadingController::class, 'getLastReading'])->name('meter-readings.get-last-reading');
        
        // Custom viewing routes (must be before resource routes to avoid conflicts)
        Route::get('/viewings/today', [\App\Http\Controllers\Agent\ViewingController::class, 'today'])->name('viewings.today');
        Route::get('/viewings/calendar', [\App\Http\Controllers\Agent\ViewingController::class, 'calendar'])->name('viewings.calendar');
        Route::get('/viewings/statistics', [\App\Http\Controllers\Agent\ViewingController::class, 'statistics'])->name('viewings.statistics');
        
        // AJAX routes for viewings
        Route::get('/viewings/get-units', [\App\Http\Controllers\Agent\ViewingController::class, 'getUnits'])->name('viewings.get-units');
        
        // Viewings management (CRUD)
        Route::resource('viewings', \App\Http\Controllers\Agent\ViewingController::class);
        
        // Action routes for viewings (after resource routes)
        Route::post('/viewings/{id}/confirm', [\App\Http\Controllers\Agent\ViewingController::class, 'confirm'])->name('viewings.confirm');
        Route::post('/viewings/{id}/cancel', [\App\Http\Controllers\Agent\ViewingController::class, 'cancel'])->name('viewings.cancel');
        Route::post('/viewings/{id}/mark-done', [\App\Http\Controllers\Agent\ViewingController::class, 'markDone'])->name('viewings.mark-done');

        // Meters management
        Route::get('/meters', [\App\Http\Controllers\Agent\MeterController::class, 'index'])->name('meters.index');
        Route::get('/meters/create', [\App\Http\Controllers\Agent\MeterController::class, 'create'])->name('meters.create');
        Route::post('/meters', [\App\Http\Controllers\Agent\MeterController::class, 'store'])->name('meters.store');
        Route::get('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'show'])->name('meters.show');
        Route::get('/meters/{id}/edit', [\App\Http\Controllers\Agent\MeterController::class, 'edit'])->name('meters.edit');
        Route::put('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'update'])->name('meters.update');
        Route::delete('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'destroy'])->name('meters.destroy');

        // Salary management
        // Salary Contracts (read-only for agents)
        Route::get('/salary-contracts', [\App\Http\Controllers\Agent\SalaryContractController::class, 'index'])->name('salary-contracts.index');
        Route::get('/salary-contracts/{id}', [\App\Http\Controllers\Agent\SalaryContractController::class, 'show'])->name('salary-contracts.show');

        // Payroll Cycles (read-only for agents)
        Route::get('/payroll-cycles', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'index'])->name('payroll-cycles.index');
        Route::get('/payroll-cycles/{id}', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'show'])->name('payroll-cycles.show');

        // Payslips (read-only for agents)
        Route::get('/payslips', [\App\Http\Controllers\Agent\PayslipController::class, 'index'])->name('payslips.index');
        Route::get('/payslips/{id}', [\App\Http\Controllers\Agent\PayslipController::class, 'show'])->name('payslips.show');

        Route::get('/salary-advances', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'index'])->name('salary-advances.index');
        Route::get('/salary-advances/create', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'create'])->name('salary-advances.create');
        Route::post('/salary-advances', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'store'])->name('salary-advances.store');
        Route::get('/salary-advances/{id}', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'show'])->name('salary-advances.show');
        Route::get('/salary-advances/{id}/edit', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'edit'])->name('salary-advances.edit');
        Route::put('/salary-advances/{id}', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'update'])->name('salary-advances.update');
        Route::delete('/salary-advances/{id}', [\App\Http\Controllers\Agent\SalaryAdvanceController::class, 'destroy'])->name('salary-advances.destroy');

        // Commission management
        Route::get('/commission-policies', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'index'])->name('commission-policies.index');
        Route::get('/commission-policies/create', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'create'])->name('commission-policies.create');
        Route::post('/commission-policies', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'store'])->name('commission-policies.store');
        Route::get('/commission-policies/{id}', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'show'])->name('commission-policies.show');
        Route::get('/commission-policies/{id}/edit', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'edit'])->name('commission-policies.edit');
        Route::put('/commission-policies/{id}', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'update'])->name('commission-policies.update');
        Route::delete('/commission-policies/{id}', [\App\Http\Controllers\Agent\CommissionPolicyController::class, 'destroy'])->name('commission-policies.destroy');

        Route::get('/commission-events', [\App\Http\Controllers\Agent\CommissionEventController::class, 'index'])->name('commission-events.index');
        Route::get('/commission-events/create', [\App\Http\Controllers\Agent\CommissionEventController::class, 'create'])->name('commission-events.create');
        Route::post('/commission-events', [\App\Http\Controllers\Agent\CommissionEventController::class, 'store'])->name('commission-events.store');
        Route::get('/commission-events/{id}', [\App\Http\Controllers\Agent\CommissionEventController::class, 'show'])->name('commission-events.show');
        Route::get('/commission-events/{id}/edit', [\App\Http\Controllers\Agent\CommissionEventController::class, 'edit'])->name('commission-events.edit');
        Route::put('/commission-events/{id}', [\App\Http\Controllers\Agent\CommissionEventController::class, 'update'])->name('commission-events.update');
        Route::delete('/commission-events/{id}', [\App\Http\Controllers\Agent\CommissionEventController::class, 'destroy'])->name('commission-events.destroy');
        // Invoice sync routes removed - commission events no longer create invoices

        // Reports
        Route::get('/revenue-reports', [\App\Http\Controllers\Agent\RevenueReportController::class, 'index'])->name('revenue-reports.index');
        Route::get('/revenue-reports/{id}', [\App\Http\Controllers\Agent\RevenueReportController::class, 'show'])->name('revenue-reports.show');
        Route::get('/reports/payments', [\App\Http\Controllers\Agent\PaymentReportController::class, 'index'])->name('reports.payments');

        // Tenants management (CRUD)
        Route::resource('tenants', \App\Http\Controllers\Agent\TenantController::class);
        Route::post('/tenants/add-resident/{leaseId}', [\App\Http\Controllers\Agent\TenantController::class, 'addResident'])->name('tenants.add-resident');

        // Booking Deposits management (CRUD)
        // Specific routes must come BEFORE resource routes to avoid conflicts
        Route::get('/booking-deposits/get-units', [\App\Http\Controllers\Agent\BookingDepositController::class, 'getUnits'])->name('booking-deposits.get-units');
        Route::get('/booking-deposits/statistics', [\App\Http\Controllers\Agent\BookingDepositController::class, 'statistics'])->name('booking-deposits.statistics');
        
        // Alternative route structure to avoid conflicts
        Route::get('/api/booking-deposits/units', [\App\Http\Controllers\Agent\BookingDepositController::class, 'getUnits'])->name('api.booking-deposits.units');
        
        
        
        Route::post('/booking-deposits/{id}/mark-as-paid', [\App\Http\Controllers\Agent\BookingDepositController::class, 'markAsPaid'])->name('booking-deposits.mark-as-paid');
        Route::post('/booking-deposits/{id}/refund', [\App\Http\Controllers\Agent\BookingDepositController::class, 'refund'])->name('booking-deposits.refund');
        Route::post('/booking-deposits/{id}/cancel', [\App\Http\Controllers\Agent\BookingDepositController::class, 'cancel'])->name('booking-deposits.cancel');
        Route::resource('booking-deposits', \App\Http\Controllers\Agent\BookingDepositController::class);

        // Settings
        Route::get('/settings/general', [\App\Http\Controllers\Agent\SettingsController::class, 'general'])->name('settings.general');
        Route::put('/settings/general', [\App\Http\Controllers\Agent\SettingsController::class, 'updateGeneral'])->name('settings.update-general');
        
        // API endpoints for leases
        Route::prefix('api/leases')->group(function () {
            Route::get('/units/{propertyId}', [\App\Http\Controllers\Agent\LeaseController::class, 'getUnits']);
            Route::get('/next-contract-number', [\App\Http\Controllers\Agent\LeaseController::class, 'getNextContractNumber']);
            Route::get('/search-users', [\App\Http\Controllers\Agent\LeaseController::class, 'searchUsers']);
            Route::get('/deposits/{unitId}', [\App\Http\Controllers\Agent\LeaseController::class, 'getUnitDeposits']);
            Route::get('/properties/{propertyId}/payment-cycle', [\App\Http\Controllers\Agent\LeaseController::class, 'getPropertyPaymentCycle']);
        });

        // Invoices management (CRUD)
        Route::resource('invoices', \App\Http\Controllers\Agent\InvoiceController::class);
        Route::post('/invoices/{id}/issue', [\App\Http\Controllers\Agent\InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('/invoices/{id}/cancel', [\App\Http\Controllers\Agent\InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('/invoices/lease-info/{leaseId}', [\App\Http\Controllers\Agent\InvoiceController::class, 'getLeaseInfo'])->name('invoices.lease-info');

        // Tickets management (CRU - no Delete)
        Route::get('/tickets', [\App\Http\Controllers\Agent\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [\App\Http\Controllers\Agent\TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [\App\Http\Controllers\Agent\TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{id}', [\App\Http\Controllers\Agent\TicketController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{id}/edit', [\App\Http\Controllers\Agent\TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{id}', [\App\Http\Controllers\Agent\TicketController::class, 'update'])->name('tickets.update');
        Route::post('/tickets/{id}/logs', [\App\Http\Controllers\Agent\TicketController::class, 'addLog'])->name('tickets.addLog');

        // API endpoints for tickets
        Route::prefix('api/tickets')->group(function () {
            Route::get('/properties/{propertyId}/units', [\App\Http\Controllers\Agent\TicketController::class, 'getUnits']);
            Route::get('/units/{unitId}/leases', [\App\Http\Controllers\Agent\TicketController::class, 'getLeases']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | LANDLORD Routes (ensure.landlord)
    |--------------------------------------------------------------------------
    */
    Route::prefix('landlord')->name('landlord.')->middleware('ensure.landlord')->group(function () {
            // Dashboard
        
    });

    /*
    |--------------------------------------------------------------------------
    | TENANT Routes (ensure.tenant)
    |--------------------------------------------------------------------------
    */

    Route::prefix('tenant')->name('tenant.')->middleware('ensure.tenant')->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return view('tenant.dashboard');
        })->name('dashboard');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'index'])->name('profile');
        Route::get('/profile/edit', [\App\Http\Controllers\Tenant\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'update'])->name('profile.update');
        // Appointments - moved from public viewings to tenant-specific
        Route::get('/appointments', [\App\Http\Controllers\ViewingController::class, 'appointments'])->name('appointments');
        Route::get('/appointments/{id}', [\App\Http\Controllers\ViewingController::class, 'show'])->name('appointments.show');
        Route::get('/appointments/{id}/edit', [\App\Http\Controllers\ViewingController::class, 'edit'])->name('appointments.edit');
        Route::get('/appointments/{id}/edit-data', [\App\Http\Controllers\ViewingController::class, 'getForEdit'])->name('appointments.edit-data');
        Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\ViewingController::class, 'cancel'])->name('appointments.cancel');
        Route::put('/appointments/{id}/update', [\App\Http\Controllers\ViewingController::class, 'update'])->name('appointments.update');
        Route::put('/appointments/{id}/status', [\App\Http\Controllers\ViewingController::class, 'updateStatus'])->name('appointments.update-status');

        // Deposit
        Route::get('/deposit/{id?}', function ($id = 1) {
            return view('tenant.deposit', compact('id'));
        })->name('deposit');

        Route::post('/deposit/{id?}', function ($id = 1) {
            return response()->json(['success' => true, 'message' => 'Thanh toán thành công!', 'transaction_id' => 'DP' . date('Ymd') . rand(10, 99)]);
        })->name('deposit.store');

        // Contracts
        Route::get('/contracts', [\App\Http\Controllers\Tenant\ContractController::class, 'index'])->name('contracts.index');
        Route::get('/contracts/{id}', [\App\Http\Controllers\Tenant\ContractController::class, 'show'])->name('contracts.show');

        // Invoices
        Route::get('/invoices', [\App\Http\Controllers\Tenant\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [\App\Http\Controllers\Tenant\InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{id}/pay', [\App\Http\Controllers\Tenant\InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::get('/invoices/{id}/download', [\App\Http\Controllers\Tenant\InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/invoices/export', [\App\Http\Controllers\Tenant\InvoiceController::class, 'export'])->name('invoices.export');

        // Maintenance
        Route::get('/maintenance', function () {
            return view('tenant.maintenance');
        })->name('maintenance');

        Route::post('/maintenance', function () {
            return response()->json(['success' => true, 'message' => 'Yêu cầu sửa chữa đã được tạo thành công!', 'request_id' => 'YC' . rand(100, 999)]);
        })->name('maintenance.store');

        // Reviews
        Route::get('/reviews', function () {
            return view('tenant.reviews');
        })->name('reviews');

        Route::post('/reviews', function () {
            return response()->json(['success' => true, 'message' => 'Đánh giá đã được đăng thành công!', 'review_id' => 'RV' . rand(100, 999)]);
        })->name('reviews.store');

        // Notifications
        Route::get('/notifications', function () {
            return view('tenant.notifications');
        })->name('notifications');

        // Rooms listing - REMOVED FOR NOW
        // Will be re-implemented later when needed

        // News
        Route::get('/news', function () {
            return view('tenant.news.index');
        })->name('news.index');

        // Contact
        Route::get('/contact', function () {
            return view('tenant.contact');
        })->name('contact');

        Route::post('/contact', function () {
            return response()->json(['success' => true, 'message' => 'Tin nhắn đã được gửi thành công!']);
        })->name('contact.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Legacy compatibility routes (backward compatibility with old /profile)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', function () {
        return redirect()->route('tenant.profile');
    });


    

    Route::get('/contracts', function () {
        return redirect()->route('tenant.contracts.index');
    });

    Route::get('/invoices', function () {
        return redirect()->route('tenant.invoices.index');
    });

    Route::get('/maintenance', function () {
        return redirect()->route('tenant.maintenance');
    });

    Route::get('/reviews', function () {
        return redirect()->route('tenant.reviews');
    });

    Route::get('/notifications', function () {
        return redirect()->route('tenant.notifications');
    });

    // Main rooms route - MOVED TO PUBLIC ROUTES
});

/*
|--------------------------------------------------------------------------
| Viewing Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('viewings')->name('viewings.')->group(function () {
    Route::post('/store', [\App\Http\Controllers\ViewingController::class, 'store'])->name('store');
    Route::get('/available-slots', [\App\Http\Controllers\ViewingController::class, 'getAvailableSlots'])->name('available-slots');
});

/*
|--------------------------------------------------------------------------
| Authenticated Viewing Routes (Legacy - kept for backward compatibility)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('viewings')->name('viewings.')->group(function () {
    Route::get('/my-viewings', [\App\Http\Controllers\ViewingController::class, 'myViewings'])->name('my-viewings');
    // Note: appointments routes moved to tenant.appointments for better organization
    Route::get('/{id}', [\App\Http\Controllers\ViewingController::class, 'show'])->name('show');
    Route::post('/{id}/cancel', [\App\Http\Controllers\ViewingController::class, 'cancel'])->name('cancel');
    Route::put('/{id}/update', [\App\Http\Controllers\ViewingController::class, 'update'])->name('update');
});

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/
// Redirect old booking routes to tenant booking routes
Route::get('/booking/{id?}', function ($id = 1) {
    return redirect()->route('tenant.booking', $id);
})->name('booking.redirect');





/*
|--------------------------------------------------------------------------
| Image Upload API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api/images')->name('api.images.')->middleware('auth')->group(function () {
    Route::post('/upload', [\App\Http\Controllers\Api\ImageController::class, 'upload'])->name('upload');
    Route::post('/upload-multiple', [\App\Http\Controllers\Api\ImageController::class, 'uploadMultiple'])->name('upload-multiple');
    Route::delete('/delete', [\App\Http\Controllers\Api\ImageController::class, 'delete'])->name('delete');
    Route::get('/url', [\App\Http\Controllers\Api\ImageController::class, 'getUrl'])->name('url');
    Route::get('/stats', [\App\Http\Controllers\Api\ImageController::class, 'stats'])->name('stats');
    Route::post('/validate', [\App\Http\Controllers\Api\ImageController::class, 'validate'])->name('validate');
});
