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
    | MANAGER Routes (ensure.manager)
    |--------------------------------------------------------------------------
    */
    Route::prefix('manager')->name('manager.')->middleware(['ensure.manager', 'check.organization'])->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return view('manager.dashboard');
        })->name('dashboard');

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
        
        // Reports
        Route::get('/reports/revenue', function () {
            return view('manager.reports.revenue');
        })->name('reports.revenue');
        
        Route::get('/reports/occupancy', function () {
            return view('manager.reports.occupancy');
        })->name('reports.occupancy');
        
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
        
        // Legacy rooms routes (admin context)
        Route::get('/rooms', function () {
            return view('manager.rooms.index');
        })->name('rooms.index');
        
        Route::get('/rooms/create', function () {
            return view('manager.rooms.create');
        })->name('rooms.create');
        
        Route::post('/rooms', function () {
            return response()->json(['success' => true, 'message' => 'Phòng đã được tạo thành công!']);
        })->name('rooms.store');
        
        Route::get('/rooms/{id}/edit', function ($id) {
            return view('manager.rooms.edit', compact('id'));
        })->name('rooms.edit');
        
        Route::put('/rooms/{id}', function ($id) {
            return response()->json(['success' => true, 'message' => 'Phòng đã được cập nhật thành công!']);
        })->name('rooms.update');
        
        Route::delete('/rooms/{id}', function ($id) {
            return response()->json(['success' => true, 'message' => 'Phòng đã được xóa thành công!']);
        })->name('rooms.destroy');
        
        Route::get('/rooms/{id}', function ($id) {
            return view('manager.rooms.show', compact('id'));
        })->name('rooms.show');
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
    Route::get('/rooms', function () {
            return view('agent.rooms.index');
    })->name('rooms.index');
    
    Route::get('/rooms/create', function () {
            return view('agent.rooms.create');
    })->name('rooms.create');
    
    Route::post('/rooms', function () {
        return response()->json(['success' => true, 'message' => 'Phòng đã được tạo thành công!']);
    })->name('rooms.store');
    
    Route::get('/rooms/{id}/edit', function ($id) {
            return view('agent.rooms.edit', compact('id'));
    })->name('rooms.edit');
    
    Route::put('/rooms/{id}', function ($id) {
        return response()->json(['success' => true, 'message' => 'Phòng đã được cập nhật thành công!']);
    })->name('rooms.update');
    
    Route::delete('/rooms/{id}', function ($id) {
        return response()->json(['success' => true, 'message' => 'Phòng đã được xóa thành công!']);
    })->name('rooms.destroy');
    
    Route::get('/rooms/{id}', function ($id) {
            return view('agent.rooms.show', compact('id'));
    })->name('rooms.show');
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

        // Rooms listing
        Route::get('/rooms', function () {
            $type = request('type');
            return view('tenant.rooms.index', compact('type'));
        })->name('rooms.index');

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

    Route::get('/rooms', function () {
        return redirect()->route('tenant.rooms.index', request()->query());
    })->name('rooms.index');

    Route::get('/notifications', function () {
        return redirect()->route('tenant.notifications');
    })->name('notifications');
});
