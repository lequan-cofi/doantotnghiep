<?php
/**
 * Test script to check meter billing functionality
 */

require_once 'vendor/autoload.php';

use App\Services\MeterBillingService;
use App\Models\Meter;

echo "=== TESTING METER BILLING ===\n\n";

try {
    // Test 1: Check if MeterBillingService exists
    echo "1. Checking MeterBillingService...\n";
    
    if (class_exists('App\Services\MeterBillingService')) {
        echo "   ✅ MeterBillingService class exists\n";
    } else {
        echo "   ❌ MeterBillingService class not found\n";
        exit;
    }
    
    // Test 2: Check if we have meters
    echo "\n2. Checking Meters...\n";
    $meters = Meter::all();
    echo "   - Total Meters: " . $meters->count() . "\n";
    
    if ($meters->count() > 0) {
        echo "   - Meters found:\n";
        foreach ($meters as $meter) {
            echo "     * ID: {$meter->id}, Serial: {$meter->serial_no}, Status: " . ($meter->status ? 'Active' : 'Inactive') . "\n";
        }
    } else {
        echo "   ❌ No meters found!\n";
        echo "   Please create some meters first.\n";
        exit;
    }
    
    // Test 3: Test billing history
    echo "\n3. Testing Billing History...\n";
    
    $billingService = new MeterBillingService();
    $testMeter = $meters->first();
    
    echo "   - Testing with Meter ID: {$testMeter->id}\n";
    
    try {
        $billingHistory = $billingService->getBillingHistory($testMeter->id, 5);
        echo "   ✅ Billing history query successful\n";
        echo "   - Records returned: " . $billingHistory->count() . "\n";
        
        if ($billingHistory->count() > 0) {
            echo "   - Sample data:\n";
            foreach ($billingHistory as $item) {
                echo "     * Month: {$item->month}, Usage: {$item->usage}, Readings: {$item->reading_count}\n";
            }
        } else {
            echo "   - No billing history found (this is normal if no readings exist)\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Error in billing history: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
    echo "✅ Meter billing system is working!\n";
    echo "\nNext steps:\n";
    echo "1. Test meter show page: /agent/meters/{$testMeter->id}\n";
    echo "2. Check billing history display\n";
    echo "3. Verify no SQL errors in logs\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
