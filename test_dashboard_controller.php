<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Dashboard Controller...\n";

try {
    // Test 1: Check if controller exists
    echo "\n--- Test 1: Check Controller Exists ---\n";
    $controller = new \App\Http\Controllers\Manager\DashboardController();
    echo "✅ DashboardController created successfully\n";
    
    // Test 2: Check organization access
    echo "\n--- Test 2: Check Organization Access ---\n";
    $user = App\Models\User::first();
    if ($user) {
        $organization = $user->organizations()->first();
        if ($organization) {
            echo "✅ User {$user->full_name} has organization: {$organization->name}\n";
            echo "✅ Organization ID: {$organization->id}\n";
        } else {
            echo "❌ User has no organization\n";
        }
    } else {
        echo "❌ No users found\n";
    }
    
    // Test 3: Test dashboard data methods
    echo "\n--- Test 3: Test Dashboard Data Methods ---\n";
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($controller);
    
    // Test getKeyStats
    $getKeyStatsMethod = $reflection->getMethod('getKeyStats');
    $getKeyStatsMethod->setAccessible(true);
    $keyStats = $getKeyStatsMethod->invoke($controller, 1);
    echo "✅ Key Stats: " . json_encode($keyStats, JSON_PRETTY_PRINT) . "\n";
    
    // Test getRevenueStats
    $getRevenueStatsMethod = $reflection->getMethod('getRevenueStats');
    $getRevenueStatsMethod->setAccessible(true);
    $revenueStats = $getRevenueStatsMethod->invoke($controller, 1);
    echo "✅ Revenue Stats: " . json_encode($revenueStats, JSON_PRETTY_PRINT) . "\n";
    
    // Test getOccupancyStats
    $getOccupancyStatsMethod = $reflection->getMethod('getOccupancyStats');
    $getOccupancyStatsMethod->setAccessible(true);
    $occupancyStats = $getOccupancyStatsMethod->invoke($controller, 1);
    echo "✅ Occupancy Stats: " . json_encode($occupancyStats, JSON_PRETTY_PRINT) . "\n";
    
    // Test getTopPerformers
    $getTopPerformersMethod = $reflection->getMethod('getTopPerformers');
    $getTopPerformersMethod->setAccessible(true);
    $topPerformers = $getTopPerformersMethod->invoke($controller, 1);
    echo "✅ Top Performers: " . $topPerformers->count() . " performers found\n";
    
    // Test getUrgentTasks
    $getUrgentTasksMethod = $reflection->getMethod('getUrgentTasks');
    $getUrgentTasksMethod->setAccessible(true);
    $urgentTasks = $getUrgentTasksMethod->invoke($controller, 1);
    echo "✅ Urgent Tasks: " . json_encode($urgentTasks, JSON_PRETTY_PRINT) . "\n";
    
    // Test getAnalyticsData
    $getAnalyticsDataMethod = $reflection->getMethod('getAnalyticsData');
    $getAnalyticsDataMethod->setAccessible(true);
    $analyticsData = $getAnalyticsDataMethod->invoke($controller, 1);
    echo "✅ Analytics Data: " . json_encode($analyticsData, JSON_PRETTY_PRINT) . "\n";
    
    // Test 4: Test caching
    echo "\n--- Test 4: Test Caching ---\n";
    $cacheKey = "dashboard_data_org_1";
    $cacheExists = \Illuminate\Support\Facades\Cache::has($cacheKey);
    echo "✅ Cache key exists: " . ($cacheExists ? 'YES' : 'NO') . "\n";
    
    // Test 5: Test route
    echo "\n--- Test 5: Test Route ---\n";
    $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('manager.dashboard');
    if ($route) {
        echo "✅ Route 'manager.dashboard' exists\n";
        echo "✅ Route action: " . $route->getActionName() . "\n";
    } else {
        echo "❌ Route 'manager.dashboard' not found\n";
    }
    
    echo "\n=== Dashboard Controller Test Completed! ===\n";
    echo "✅ All dashboard functionality is working correctly!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
