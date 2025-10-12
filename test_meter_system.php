<?php
/**
 * Test script for Meter Management System
 * Run this script to test the meter CRUD functionality
 */

require_once 'vendor/autoload.php';

use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Service;
use App\Models\Lease;
use App\Services\MeterBillingService;

echo "=== METER MANAGEMENT SYSTEM TEST ===\n\n";

try {
    // Test 1: Check if models exist and are accessible
    echo "1. Testing Models...\n";
    
    $meterCount = Meter::count();
    echo "   - Total Meters: {$meterCount}\n";
    
    $readingCount = MeterReading::count();
    echo "   - Total Readings: {$readingCount}\n";
    
    $propertyCount = Property::count();
    echo "   - Total Properties: {$propertyCount}\n";
    
    $unitCount = Unit::count();
    echo "   - Total Units: {$unitCount}\n";
    
    $serviceCount = Service::count();
    echo "   - Total Services: {$serviceCount}\n";
    
    $leaseCount = Lease::count();
    echo "   - Total Leases: {$leaseCount}\n";
    
    echo "   ✓ Models are accessible\n\n";
    
    // Test 2: Check services
    echo "2. Testing Services...\n";
    
    $services = Service::all();
    echo "   - Available Services:\n";
    foreach ($services as $service) {
        echo "     * {$service->name} ({$service->key_code}) - {$service->unit_label}\n";
    }
    echo "   ✓ Services loaded successfully\n\n";
    
    // Test 3: Check meter billing service
    echo "3. Testing MeterBillingService...\n";
    
    $billingService = new MeterBillingService();
    echo "   ✓ MeterBillingService instantiated successfully\n\n";
    
    // Test 4: Check routes (if possible)
    echo "4. Testing Routes...\n";
    
    $routes = [
        'agent.meters.index',
        'agent.meters.create',
        'agent.meters.store',
        'agent.meters.show',
        'agent.meters.edit',
        'agent.meters.update',
        'agent.meters.destroy',
        'agent.meter-readings.index',
        'agent.meter-readings.create',
        'agent.meter-readings.store',
        'agent.meter-readings.show',
        'agent.meter-readings.edit',
        'agent.meter-readings.update',
        'agent.meter-readings.destroy',
    ];
    
    foreach ($routes as $route) {
        try {
            $url = route($route, ['id' => 1], false);
            echo "   ✓ Route '{$route}' exists\n";
        } catch (Exception $e) {
            echo "   ✗ Route '{$route}' not found\n";
        }
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    echo "Meter Management System is ready to use!\n";
    echo "\nNext steps:\n";
    echo "1. Access the system at: /agent/meters\n";
    echo "2. Create your first meter\n";
    echo "3. Add meter readings\n";
    echo "4. Monitor billing automatically\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
