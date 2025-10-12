<?php
/**
 * Test script to check units loading for properties
 */

require_once 'vendor/autoload.php';

use App\Models\Property;
use App\Models\Unit;

echo "=== TESTING UNITS LOADING ===\n\n";

try {
    // Test 1: Check if we have properties
    echo "1. Checking Properties...\n";
    $properties = Property::all();
    echo "   - Total Properties: " . $properties->count() . "\n";
    
    if ($properties->count() > 0) {
        echo "   - Properties found:\n";
        foreach ($properties as $property) {
            echo "     * ID: {$property->id}, Name: {$property->name}\n";
        }
    } else {
        echo "   ❌ No properties found!\n";
        echo "   Please create some properties first.\n";
        exit;
    }
    
    echo "\n2. Checking Units for each Property...\n";
    
    foreach ($properties as $property) {
        echo "   - Property: {$property->name} (ID: {$property->id})\n";
        
        $units = Unit::where('property_id', $property->id)->get();
        echo "     Units count: " . $units->count() . "\n";
        
        if ($units->count() > 0) {
            echo "     Units:\n";
            foreach ($units as $unit) {
                echo "       * ID: {$unit->id}, Code: {$unit->code}, Type: {$unit->unit_type}\n";
            }
        } else {
            echo "     ❌ No units found for this property!\n";
        }
        echo "\n";
    }
    
    echo "3. Testing AJAX Endpoint...\n";
    
    // Simulate the AJAX call
    $testPropertyId = $properties->first()->id;
    echo "   - Testing with Property ID: {$testPropertyId}\n";
    
    $units = Unit::where('property_id', $testPropertyId)
        ->select('id', 'code', 'unit_type')
        ->get();
    
    echo "   - Units returned: " . $units->count() . "\n";
    
    if ($units->count() > 0) {
        echo "   - Data structure:\n";
        foreach ($units as $unit) {
            echo "     * ID: {$unit->id}, Code: {$unit->code}, Type: {$unit->unit_type}\n";
        }
        
        // Test JSON response
        $jsonResponse = json_encode(['units' => $units]);
        echo "   - JSON Response: " . $jsonResponse . "\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
    if ($units->count() > 0) {
        echo "✅ Units loading should work correctly!\n";
        echo "\nTroubleshooting tips:\n";
        echo "1. Check browser console for JavaScript errors\n";
        echo "2. Check network tab for AJAX request/response\n";
        echo "3. Check Laravel logs: storage/logs/laravel.log\n";
        echo "4. Verify route is accessible: /agent/meters/get-units\n";
    } else {
        echo "❌ No units found for testing!\n";
        echo "\nTo fix this:\n";
        echo "1. Create some units for your properties\n";
        echo "2. Or check if units are soft-deleted\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
