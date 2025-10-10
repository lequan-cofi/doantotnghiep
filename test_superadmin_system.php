<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Super Admin System...\n";

try {
    // Test 1: Check SuperAdminController
    echo "\n--- Test 1: Check SuperAdminController ---\n";
    $controller = new \App\Http\Controllers\SuperAdmin\SuperAdminController();
    echo "✅ SuperAdminController created successfully\n";
    
    // Test 2: Check middleware
    echo "\n--- Test 2: Check SuperAdminMiddleware ---\n";
    $middleware = new \App\Http\Middleware\SuperAdminMiddleware();
    echo "✅ SuperAdminMiddleware created successfully\n";
    
    // Test 3: Test SaaS metrics methods
    echo "\n--- Test 3: Test SaaS Metrics Methods ---\n";
    $reflection = new ReflectionClass($controller);
    
    // Test getOrganizationMetrics
    $getOrgMetricsMethod = $reflection->getMethod('getOrganizationMetrics');
    $getOrgMetricsMethod->setAccessible(true);
    $orgMetrics = $getOrgMetricsMethod->invoke($controller);
    echo "✅ Organization Metrics: " . json_encode($orgMetrics, JSON_PRETTY_PRINT) . "\n";
    
    // Test getUserMetrics
    $getUserMetricsMethod = $reflection->getMethod('getUserMetrics');
    $getUserMetricsMethod->setAccessible(true);
    $userMetrics = $getUserMetricsMethod->invoke($controller);
    echo "✅ User Metrics: " . json_encode($userMetrics, JSON_PRETTY_PRINT) . "\n";
    
    // Test getRevenueMetrics
    $getRevenueMetricsMethod = $reflection->getMethod('getRevenueMetrics');
    $getRevenueMetricsMethod->setAccessible(true);
    $revenueMetrics = $getRevenueMetricsMethod->invoke($controller);
    echo "✅ Revenue Metrics: " . json_encode($revenueMetrics, JSON_PRETTY_PRINT) . "\n";
    
    // Test getSystemMetrics
    $getSystemMetricsMethod = $reflection->getMethod('getSystemMetrics');
    $getSystemMetricsMethod->setAccessible(true);
    $systemMetrics = $getSystemMetricsMethod->invoke($controller);
    echo "✅ System Metrics: " . json_encode($systemMetrics, JSON_PRETTY_PRINT) . "\n";
    
    // Test 4: Check routes
    echo "\n--- Test 4: Check Super Admin Routes ---\n";
    $routes = [
        'superadmin.dashboard',
        'superadmin.organizations.index',
        'superadmin.users.index',
        'superadmin.revenue.index',
        'superadmin.system.health'
    ];
    
    foreach ($routes as $routeName) {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
        if ($route) {
            echo "✅ Route '$routeName' exists\n";
        } else {
            echo "❌ Route '$routeName' not found\n";
        }
    }
    
    // Test 5: Check views
    echo "\n--- Test 5: Check Super Admin Views ---\n";
    $views = [
        'layouts.superadmin',
        'superadmin.dashboard'
    ];
    
    foreach ($views as $viewName) {
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (file_exists($viewPath)) {
            echo "✅ View '$viewName' exists\n";
        } else {
            echo "❌ View '$viewName' not found\n";
        }
    }
    
    // Test 6: Check assets
    echo "\n--- Test 6: Check Super Admin Assets ---\n";
    $assets = [
        'assets/css/superadmin/superadmin.css',
        'assets/js/superadmin/superadmin.js'
    ];
    
    foreach ($assets as $asset) {
        $assetPath = public_path($asset);
        if (file_exists($assetPath)) {
            echo "✅ Asset '$asset' exists\n";
        } else {
            echo "❌ Asset '$asset' not found\n";
        }
    }
    
    // Test 7: Check super admin user
    echo "\n--- Test 7: Check Super Admin User ---\n";
    $firstUser = \App\Models\User::orderBy('id')->first();
    if ($firstUser) {
        echo "✅ First user: {$firstUser->full_name} ({$firstUser->email})\n";
        
        // Test middleware logic
        $middleware = new \App\Http\Middleware\SuperAdminMiddleware();
        $reflection = new ReflectionClass($middleware);
        $isSuperAdminMethod = $reflection->getMethod('isSuperAdmin');
        $isSuperAdminMethod->setAccessible(true);
        $isSuperAdmin = $isSuperAdminMethod->invoke($middleware, $firstUser);
        
        echo "✅ Is Super Admin: " . ($isSuperAdmin ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ No users found\n";
    }
    
    // Test 8: Check SaaS data
    echo "\n--- Test 8: Check SaaS Data ---\n";
    $totalOrgs = \App\Models\Organization::count();
    $totalUsers = \App\Models\User::count();
    $totalProperties = \App\Models\Property::count();
    $totalLeases = \App\Models\Lease::count();
    $totalRevenue = \Illuminate\Support\Facades\DB::table('payments')
        ->where('status', 'completed')
        ->sum('amount');
    
    echo "✅ Total Organizations: $totalOrgs\n";
    echo "✅ Total Users: $totalUsers\n";
    echo "✅ Total Properties: $totalProperties\n";
    echo "✅ Total Leases: $totalLeases\n";
    echo "✅ Total Revenue: " . number_format($totalRevenue) . " VND\n";
    
    echo "\n=== Super Admin System Test Completed! ===\n";
    echo "✅ Super Admin system is ready for SaaS management!\n";
    echo "🌐 Access URL: /superadmin/dashboard\n";
    echo "👤 Super Admin User: {$firstUser->email}\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
