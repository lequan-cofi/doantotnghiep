<?php

use Illuminate\Support\Facades\Route;

// Include test routes (remove in production)
if (app()->environment('local')) {
    require __DIR__.'/test-soft-delete.php';
}
use App\Http\Controllers\Auth\EmailAuthController;
use App\Http\Controllers\Manager\PropertyController;
use App\Http\Controllers\Manager\LeaseController;

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

// Debug routes (only in debug mode)
if (config('app.debug')) {
    Route::get('/debug-active-properties', [\App\Http\Controllers\HomeController::class, 'debugActiveProperties'])->name('debug.active.properties');
    Route::get('/test-database', [\App\Http\Controllers\HomeController::class, 'testDatabase'])->name('test.database');
    Route::get('/test-home-data', [\App\Http\Controllers\HomeController::class, 'testHomeData'])->name('test.home.data');
    Route::get('/test-organization-bypass', [\App\Http\Controllers\HomeController::class, 'testOrganizationBypass'])->name('test.organization.bypass');
    Route::get('/test-soft-delete', [\App\Http\Controllers\HomeController::class, 'testSoftDelete'])->name('test.soft.delete');
    Route::get('/test-property-controller', [\App\Http\Controllers\PropertyController::class, 'index'])->name('test.property.controller');
    Route::get('/test-property-data', [\App\Http\Controllers\PropertyController::class, 'test'])->name('test.property.data');
    Route::get('/test-categories-calculation', [\App\Http\Controllers\HomeController::class, 'testCategoriesCalculation'])->name('test.categories.calculation');
    Route::get('/test-property-prices', function() {
        try {
            // Test step by step
            $totalProperties = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->count();
            $activeProperties = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->active()->count();
            $propertiesWithUnits = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->active()->whereHas('units')->count();
            $propertiesWithAvailableUnits = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->active()->whereHas('units', function($query) {
                $query->where('status', 'available');
            })->count();
            
            // Check unit statuses
            $unitStatuses = \App\Models\Unit::selectRaw('status, count(*) as count')->groupBy('status')->get();
            
            return response()->json([
                'success' => true,
                'debug_info' => [
                    'total_properties' => $totalProperties,
                    'active_properties' => $activeProperties,
                    'properties_with_units' => $propertiesWithUnits,
                    'properties_with_available_units' => $propertiesWithAvailableUnits,
                    'unit_statuses' => $unitStatuses->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    });
    
    Route::get('/test-property-8', function() {
        try {
            $propertyId = 8;
            
            // Test step by step
            $propertyExists = \App\Models\Property::withoutGlobalScope('organization')->find($propertyId);
            $propertyNotDeleted = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->find($propertyId);
            $propertyActive = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->active()->find($propertyId);
            $propertyWithUnits = \App\Models\Property::withoutGlobalScope('organization')
                ->whereNull('deleted_at')
                ->active()
                ->whereHas('units')
                ->find($propertyId);
            $propertyWithAvailableUnits = \App\Models\Property::withoutGlobalScope('organization')
                ->whereNull('deleted_at')
                ->active()
                ->whereHas('units', function($query) {
                    $query->where('status', 'available');
                })
                ->find($propertyId);
            
            return response()->json([
                'success' => true,
                'property_id' => $propertyId,
                'debug_info' => [
                    'property_exists' => $propertyExists ? true : false,
                    'property_not_deleted' => $propertyNotDeleted ? true : false,
                    'property_active' => $propertyActive ? true : false,
                    'property_with_units' => $propertyWithUnits ? true : false,
                    'property_with_available_units' => $propertyWithAvailableUnits ? true : false,
                    'property_data' => $propertyExists ? [
                        'id' => $propertyExists->id,
                        'name' => $propertyExists->name,
                        'status' => $propertyExists->status,
                        'deleted_at' => $propertyExists->deleted_at,
                        'organization_id' => $propertyExists->organization_id
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    });
    
    Route::get('/test-property-validation', function() {
        try {
            // Test validation rules
            $propertyTypes = \App\Models\PropertyType::where('status', 1)->count();
            $users = \App\Models\User::count();
            $provinces = \DB::table('geo_provinces')->count();
            $districts = \DB::table('geo_districts')->count();
            $wards = \DB::table('geo_wards')->count();
            $provinces2025 = \DB::table('geo_provinces_2025')->count();
            $wards2025 = \DB::table('geo_wards_2025')->count();
            
            return response()->json([
                'success' => true,
                'validation_data' => [
                    'property_types_count' => $propertyTypes,
                    'users_count' => $users,
                    'geo_provinces_count' => $provinces,
                    'geo_districts_count' => $districts,
                    'geo_wards_count' => $wards,
                    'geo_provinces_2025_count' => $provinces2025,
                    'geo_wards_2025_count' => $wards2025
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    });
    
    Route::get('/test-property-simple', function() {
        try {
            $properties = \App\Models\Property::withoutGlobalScope('organization')
                ->whereNull('deleted_at')
                ->active()
                ->whereHas('units', function($query) {
                    $query->where('status', 'available'); // Only properties with available units
                })
                ->with(['location2025', 'propertyType', 'units' => function($query) {
                    $query->where('status', 'available'); // Only load available units
                }])
                ->limit(3)
                ->get();
            
            $result = [];
            foreach($properties as $property) {
                $minPrice = $property->units->min('base_rent');
                $maxPrice = $property->units->max('base_rent');
                
                $result[] = [
                    'id' => $property->id,
                    'name' => $property->name,
                    'available_units' => $property->units->count(), // Only available units loaded
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'price_display' => $minPrice && $maxPrice ? 
                        ($minPrice == $maxPrice ? 
                            number_format($minPrice, 0, ',', '.') . ' VNĐ/tháng' :
                            number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.') . ' VNĐ/tháng'
                        ) : 'Liên hệ'
                ];
            }
            
            return response()->json([
                'success' => true,
                'properties' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    });
    
    // Simple test route
    Route::get('/simple-test', function() {
        try {
            $properties = \App\Models\Property::withoutGlobalScope('organization')->whereNull('deleted_at')->count();
            $units = \App\Models\Unit::count();
            $propertyTypes = \App\Models\PropertyType::whereNull('deleted_at')->count();
            
            // Check unit statuses
            $unitStatuses = \App\Models\Unit::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get();
            
            $statusHtml = '';
            foreach($unitStatuses as $status) {
                $statusHtml .= "<p>Status '{$status->status}': {$status->count} units</p>";
            }
            
            return "<h1>Database Test (Public Access)</h1>
                    <p>Properties (all organizations): {$properties}</p>
                    <p>Units: {$units}</p>
                    <p>Property Types: {$propertyTypes}</p>
                    <h3>Unit Statuses:</h3>
                    {$statusHtml}
                    <p>Status: OK - Organization scope bypassed</p>";
        } catch (\Exception $e) {
            return "<h1>Database Error</h1><p>Error: " . $e->getMessage() . "</p>";
        }
    });
}

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

Route::get('/test', function () {
    return view('test');
})->name('test');


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
        
        // Test route
        Route::get('/test-units', function() {
            return response()->json(['message' => 'Test route works']);
        });
        
        // Very simple test route
        Route::get('/units-test', function() {
            $propertyId = request('property_id');
            
            if (!$propertyId) {
                return response()->json([
                    'message' => 'No property ID provided',
                    'property_id' => $propertyId,
                    'units' => []
                ]);
            }
            
            try {
                $units = \App\Models\Unit::where('property_id', $propertyId)
                    ->select('id', 'code', 'unit_type')
                    ->get();
                
                return response()->json([
                    'message' => 'Units loaded successfully',
                    'property_id' => $propertyId,
                    'units' => $units
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error loading units',
                    'property_id' => $propertyId,
                    'error' => $e->getMessage(),
                    'units' => []
                ]);
            }
        });
        
        // Test controller method
        Route::get('/test-controller', [\App\Http\Controllers\Agent\MeterController::class, 'getUnits']);
        
        // Simple test route
        Route::get('/simple-test', function() {
            try {
                $propertyId = request('property_id');
                
                if (!$propertyId) {
                    return response()->json(['units' => []]);
                }

                $units = \App\Models\Unit::where('property_id', $propertyId)
                    ->select('id', 'code', 'unit_type')
                    ->get();

                return response()->json(['units' => $units]);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'units' => []
                ], 500);
            }
        });
        
        // Meter readings management (CRUD)
        Route::resource('meter-readings', \App\Http\Controllers\Agent\MeterReadingController::class);
        Route::get('/meter-readings/get-last-reading', [\App\Http\Controllers\Agent\MeterReadingController::class, 'getLastReading'])->name('meter-readings.get-last-reading');
        
        // Debug route for testing units loading
        Route::get('/debug/units/{propertyId}', function($propertyId) {
            try {
                $units = \App\Models\Unit::where('property_id', $propertyId)
                    ->select('id', 'code', 'unit_type')
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'property_id' => $propertyId,
                    'units_count' => $units->count(),
                    'units' => $units
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'property_id' => $propertyId
                ]);
            }
        });
        
        // Debug page
        Route::get('/debug-units', function() {
            return view('debug-units');
        });
        
        // Test page
        Route::get('/test-units-page', function() {
            return view('test-units');
        });
        
        // Data test route
        Route::get('/data-test', function() {
            try {
                $properties = \App\Models\Property::all();
                $units = \App\Models\Unit::all();
                
                $result = [
                    'properties_count' => $properties->count(),
                    'units_count' => $units->count(),
                    'properties' => $properties->map(function($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name
                        ];
                    }),
                    'units_by_property' => []
                ];
                
                foreach ($properties as $property) {
                    $propertyUnits = \App\Models\Unit::where('property_id', $property->id)->get();
                    $result['units_by_property'][$property->id] = [
                        'property_name' => $property->name,
                        'units_count' => $propertyUnits->count(),
                        'units' => $propertyUnits->map(function($u) {
                            return [
                                'id' => $u->id,
                                'code' => $u->code,
                                'unit_type' => $u->unit_type,
                                'status' => $u->status
                            ];
                        })
                    ];
                }
                
                return response()->json($result);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
        
        // Viewings management
        Route::get('/viewings', [\App\Http\Controllers\Agent\ViewingController::class, 'index'])->name('viewings.index');
        Route::get('/viewings/today', [\App\Http\Controllers\Agent\ViewingController::class, 'today'])->name('viewings.today');
        Route::get('/viewings/calendar', [\App\Http\Controllers\Agent\ViewingController::class, 'calendar'])->name('viewings.calendar');
        Route::get('/viewings/statistics', [\App\Http\Controllers\Agent\ViewingController::class, 'statistics'])->name('viewings.statistics');
        Route::get('/viewings/{id}', [\App\Http\Controllers\Agent\ViewingController::class, 'show'])->name('viewings.show');
        Route::post('/viewings/{id}/confirm', [\App\Http\Controllers\Agent\ViewingController::class, 'confirm'])->name('viewings.confirm');
        Route::post('/viewings/{id}/cancel', [\App\Http\Controllers\Agent\ViewingController::class, 'cancel'])->name('viewings.cancel');

        // Meters management
        Route::get('/meters', [\App\Http\Controllers\Agent\MeterController::class, 'index'])->name('meters.index');
        Route::get('/meters/create', [\App\Http\Controllers\Agent\MeterController::class, 'create'])->name('meters.create');
        Route::post('/meters', [\App\Http\Controllers\Agent\MeterController::class, 'store'])->name('meters.store');
        Route::get('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'show'])->name('meters.show');
        Route::get('/meters/{id}/edit', [\App\Http\Controllers\Agent\MeterController::class, 'edit'])->name('meters.edit');
        Route::put('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'update'])->name('meters.update');
        Route::delete('/meters/{id}', [\App\Http\Controllers\Agent\MeterController::class, 'destroy'])->name('meters.destroy');

        // Salary management
        Route::get('/salary-contracts', [\App\Http\Controllers\Agent\SalaryContractController::class, 'index'])->name('salary-contracts.index');
        Route::get('/salary-contracts/create', [\App\Http\Controllers\Agent\SalaryContractController::class, 'create'])->name('salary-contracts.create');
        Route::post('/salary-contracts', [\App\Http\Controllers\Agent\SalaryContractController::class, 'store'])->name('salary-contracts.store');
        Route::get('/salary-contracts/{id}', [\App\Http\Controllers\Agent\SalaryContractController::class, 'show'])->name('salary-contracts.show');
        Route::get('/salary-contracts/{id}/edit', [\App\Http\Controllers\Agent\SalaryContractController::class, 'edit'])->name('salary-contracts.edit');
        Route::put('/salary-contracts/{id}', [\App\Http\Controllers\Agent\SalaryContractController::class, 'update'])->name('salary-contracts.update');
        Route::delete('/salary-contracts/{id}', [\App\Http\Controllers\Agent\SalaryContractController::class, 'destroy'])->name('salary-contracts.destroy');

        Route::get('/payroll-cycles', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'index'])->name('payroll-cycles.index');
        Route::get('/payroll-cycles/create', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'create'])->name('payroll-cycles.create');
        Route::post('/payroll-cycles', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'store'])->name('payroll-cycles.store');
        Route::get('/payroll-cycles/{id}', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'show'])->name('payroll-cycles.show');
        Route::get('/payroll-cycles/{id}/edit', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'edit'])->name('payroll-cycles.edit');
        Route::put('/payroll-cycles/{id}', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'update'])->name('payroll-cycles.update');
        Route::delete('/payroll-cycles/{id}', [\App\Http\Controllers\Agent\PayrollCycleController::class, 'destroy'])->name('payroll-cycles.destroy');

        Route::get('/payroll-payslips', [\App\Http\Controllers\Agent\PayrollPayslipController::class, 'index'])->name('payroll-payslips.index');
        Route::get('/payroll-payslips/{id}', [\App\Http\Controllers\Agent\PayrollPayslipController::class, 'show'])->name('payroll-payslips.show');
        Route::get('/payroll-payslips/{id}/edit', [\App\Http\Controllers\Agent\PayrollPayslipController::class, 'edit'])->name('payroll-payslips.edit');
        Route::put('/payroll-payslips/{id}', [\App\Http\Controllers\Agent\PayrollPayslipController::class, 'update'])->name('payroll-payslips.update');

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

        // Reports
        Route::get('/revenue-reports', [\App\Http\Controllers\Agent\RevenueReportController::class, 'index'])->name('revenue-reports.index');
        Route::get('/revenue-reports/{id}', [\App\Http\Controllers\Agent\RevenueReportController::class, 'show'])->name('revenue-reports.show');
        Route::get('/reports/payments', [\App\Http\Controllers\Agent\PaymentReportController::class, 'index'])->name('reports.payments');

        // Settings
        Route::get('/settings/general', [\App\Http\Controllers\Agent\SettingsController::class, 'general'])->name('settings.general');
        Route::put('/settings/general', [\App\Http\Controllers\Agent\SettingsController::class, 'updateGeneral'])->name('settings.update-general');
        
    // API endpoints for leases
    Route::prefix('api/leases')->group(function () {
        Route::get('/units/{propertyId}', [\App\Http\Controllers\Agent\LeaseController::class, 'getUnits']);
        Route::get('/next-contract-number', [\App\Http\Controllers\Agent\LeaseController::class, 'getNextContractNumber']);
        Route::get('/search-users', [\App\Http\Controllers\Agent\LeaseController::class, 'searchUsers']);
        Route::get('/test-users', [\App\Http\Controllers\Agent\LeaseController::class, 'testUsers']);
        Route::get('/debug-organizations', [\App\Http\Controllers\Agent\LeaseController::class, 'debugOrganizations']);
        Route::get('/test-search/{query?}', [\App\Http\Controllers\Agent\LeaseController::class, 'testSearch']);
        Route::get('/simple-test', [\App\Http\Controllers\Agent\LeaseController::class, 'simpleTest']);
        Route::get('/debug-org-3', [\App\Http\Controllers\Agent\LeaseController::class, 'debugOrg3']);
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
        
        // Booking
        Route::get('/booking/{id?}', function ($id = 1) {
            return view('tenant.booking', compact('id'));
        })->name('booking');

        Route::post('/booking/{id?}', function ($id = 1) {
            return response()->json(['success' => true, 'message' => 'Đặt lịch thành công!']);
        })->name('booking.store');

        // Appointments
        Route::get('/appointments', function () {
            return view('tenant.appointments');
        })->name('appointments');

        // Deposit
        Route::get('/deposit/{id?}', function ($id = 1) {
            return view('tenant.deposit', compact('id'));
        })->name('deposit');

        Route::post('/deposit/{id?}', function ($id = 1) {
            return response()->json(['success' => true, 'message' => 'Thanh toán thành công!', 'transaction_id' => 'DP' . date('Ymd') . rand(10, 99)]);
        })->name('deposit.store');

        // Contracts
        Route::get('/contracts', function () {
            return view('tenant.contracts');
        })->name('contracts');

        // Invoices
        Route::get('/invoices', function () {
            return view('tenant.invoices');
        })->name('invoices');

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

    Route::get('/booking/{id?}', function ($id = 1) {
        return redirect()->route('tenant.booking', $id);
    });

    Route::get('/appointments', function () {
        return redirect()->route('tenant.appointments');
    });

    Route::get('/contracts', function () {
        return redirect()->route('tenant.contracts');
    });

    Route::get('/invoices', function () {
        return redirect()->route('tenant.invoices');
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
| Authenticated Viewing Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('viewings')->name('viewings.')->group(function () {
    Route::get('/my-viewings', [\App\Http\Controllers\ViewingController::class, 'myViewings'])->name('my-viewings');
    Route::get('/appointments', [\App\Http\Controllers\ViewingController::class, 'appointments'])->name('appointments');
    Route::get('/{id}', [\App\Http\Controllers\ViewingController::class, 'show'])->name('show');
    Route::post('/{id}/cancel', [\App\Http\Controllers\ViewingController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/booking/{property_id?}/{unit_id?}', [\App\Http\Controllers\ViewingController::class, 'booking'])->name('booking');
});





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
