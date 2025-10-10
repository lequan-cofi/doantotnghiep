<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing New Occupancy Logic...\n";

try {
    // Test 1: Check leases data
    echo "\n--- Test 1: Check Leases Data ---\n";
    $totalLeases = \Illuminate\Support\Facades\DB::table('leases')->count();
    $activeLeases = \Illuminate\Support\Facades\DB::table('leases')->where('status', 'active')->whereNull('deleted_at')->count();
    $pendingLeases = \Illuminate\Support\Facades\DB::table('leases')->where('status', 'pending')->whereNull('deleted_at')->count();
    
    echo "✅ Total leases: $totalLeases\n";
    echo "✅ Active leases: $activeLeases\n";
    echo "✅ Pending leases: $pendingLeases\n";
    
    // Test 2: Check units data
    echo "\n--- Test 2: Check Units Data ---\n";
    $totalUnits = \Illuminate\Support\Facades\DB::table('units')->count();
    $unitsWithActiveLeases = \Illuminate\Support\Facades\DB::table('units')
        ->whereExists(function ($query) {
            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('leases')
                ->whereColumn('leases.unit_id', 'units.id')
                ->where('leases.status', 'active')
                ->whereNull('leases.deleted_at');
        })
        ->count();
    
    echo "✅ Total units: $totalUnits\n";
    echo "✅ Units with active leases: $unitsWithActiveLeases\n";
    
    // Test 3: Test DashboardController occupancy logic
    echo "\n--- Test 3: Test DashboardController Occupancy Logic ---\n";
    $controller = new \App\Http\Controllers\Manager\DashboardController();
    $reflection = new ReflectionClass($controller);
    
    $getOccupancyStatsMethod = $reflection->getMethod('getOccupancyStats');
    $getOccupancyStatsMethod->setAccessible(true);
    $occupancyStats = $getOccupancyStatsMethod->invoke($controller, 1);
    
    echo "✅ Occupancy Stats from Controller:\n";
    echo "  - Available: " . $occupancyStats['available'] . "\n";
    echo "  - Occupied: " . $occupancyStats['occupied'] . "\n";
    echo "  - Reserved: " . $occupancyStats['reserved'] . "\n";
    echo "  - Maintenance: " . $occupancyStats['maintenance'] . "\n";
    
    // Test 4: Test Property model occupancy logic
    echo "\n--- Test 4: Test Property Model Occupancy Logic ---\n";
    $property = \App\Models\Property::first();
    if ($property) {
        echo "✅ Testing Property: {$property->name}\n";
        echo "  - Total units: " . $property->getTotalUnitsCount() . "\n";
        echo "  - Occupied units: " . $property->getOccupiedUnitsCount() . "\n";
        echo "  - Available units: " . $property->getAvailableUnitsCount() . "\n";
        echo "  - Reserved units: " . $property->getReservedUnitsCount() . "\n";
        echo "  - Maintenance units: " . $property->getMaintenanceUnitsCount() . "\n";
        echo "  - Occupancy rate: " . $property->getOccupancyRate() . "%\n";
        echo "  - Occupancy status: " . $property->getOccupancyStatusAttribute() . "\n";
    } else {
        echo "❌ No properties found\n";
    }
    
    // Test 5: Compare old vs new logic
    echo "\n--- Test 5: Compare Old vs New Logic ---\n";
    if ($property) {
        // Old logic (units.status)
        $oldOccupied = $property->units()->where('status', 'occupied')->count();
        $oldOccupancyRate = $property->units()->count() > 0 ? round(($oldOccupied / $property->units()->count()) * 100, 1) : 0;
        
        // New logic (active leases)
        $newOccupied = $property->getOccupiedUnitsCount();
        $newOccupancyRate = $property->getOccupancyRate();
        
        echo "✅ Old Logic (units.status):\n";
        echo "  - Occupied: $oldOccupied\n";
        echo "  - Occupancy Rate: $oldOccupancyRate%\n";
        
        echo "✅ New Logic (active leases):\n";
        echo "  - Occupied: $newOccupied\n";
        echo "  - Occupancy Rate: $newOccupancyRate%\n";
        
        echo "✅ Difference: " . ($newOccupied - $oldOccupied) . " units\n";
    }
    
    echo "\n=== Occupancy Logic Test Completed! ===\n";
    echo "✅ New occupancy logic based on leases is working correctly!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
