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
Route::get('/', function () {
    return view('home');
})->name('home');

// Public rooms route
Route::get('/rooms', function () {
    $type = request('type');
    
    // Sample data for demonstration
    $rooms = collect([
        (object)[
            'id' => 1,
            'title' => 'Phòng trọ chung chủ gần trường',
            'address' => '123 Đường ABC, Quận 1, TP.HCM',
            'type' => 'Nhà trọ chung chủ',
            'price' => 2500000,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'area' => 25,
            'image' => '/assets/images/room1.jpg'
        ],
        (object)[
            'id' => 2,
            'title' => 'Chung cư mini hiện đại',
            'address' => '456 Đường XYZ, Quận 2, TP.HCM',
            'type' => 'Chung cư mini',
            'price' => 3500000,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'area' => 35,
            'image' => '/assets/images/room2.jpg'
        ],
        (object)[
            'id' => 3,
            'title' => 'Căn hộ cao cấp view sông',
            'address' => '789 Đường DEF, Quận 7, TP.HCM',
            'type' => 'Căn hộ cao cấp',
            'price' => 8000000,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 60,
            'image' => '/assets/images/room3.jpg'
        ]
    ]);
    
    // Filter by type if specified
    if ($type) {
        $rooms = $rooms->filter(function($room) use ($type) {
            return strpos(strtolower($room->type), strtolower($type)) !== false;
        });
    }
    
    return view('tenant.rooms.index', compact('type', 'rooms'));
})->name('rooms.index');

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

Route::get('/test', function () {
    return view('test');
})->name('test');
Route::get('/notifications', function () {
    return view('notifications');
})->name('notifications');


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
            $record = \Illuminate\Support\Facades\DB::table('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->where('user_roles.user_id', $userId)
                ->orderBy('roles.id')
                ->select('roles.key_code')
                ->first();
            $roleKey = $record->key_code ?? null;
        }

        $routeByRole = [
            'admin' => 'admin.dashboard',
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

    });

    /*
    |--------------------------------------------------------------------------
    | AGENT Routes (ensure.agent)
    |--------------------------------------------------------------------------
    */
    Route::prefix('agent')->name('agent.')->middleware('ensure.agent')->group(function () {
        Route::get('/dashboard', function () {
            return view('agent.dashboard');
        })->name('dashboard');

        // Profile
        Route::get('/profile', function () {
            return view('agent.profile');
        })->name('profile');

        // Rooms management
    // Agent rooms routes - REMOVED FOR NOW
    // Will be re-implemented later when needed


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
        Route::get('/profile', function () {
            return view('tenant.profile');
        })->name('profile');
        
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

    Route::get('/notifications', function () {
        return redirect()->route('tenant.notifications');
    })->name('notifications');
});
