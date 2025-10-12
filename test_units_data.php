<?php
/**
 * Test script to check units data for properties
 */

require_once 'vendor/autoload.php';

use App\Models\Property;
use App\Models\Unit;

echo "=== TESTING UNITS DATA ===\n\n";

try {
    // Test 1: Get all properties
    echo "1. Properties:\n";
    $properties = Property::all();
    echo "   - Total Properties: " . $properties->count() . "\n";
    
    if ($properties->count() > 0) {
        foreach ($properties as $property) {
            echo "     * ID: {$property->id}, Name: {$property->name}\n";
        }
    }
    
    echo "\n2. Units by Property:\n";
    
    foreach ($properties as $property) {
        echo "   - Property: {$property->name} (ID: {$property->id})\n";
        
        $units = Unit::where('property_id', $property->id)->get();
        echo "     Units count: " . $units->count() . "\n";
        
        if ($units->count() > 0) {
            foreach ($units as $unit) {
                echo "       * ID: {$unit->id}, Code: {$unit->code}, Type: {$unit->unit_type}, Status: {$unit->status}\n";
            }
        }
        echo "\n";
    }
    
    echo "3. Test API Response Format:\n";
    
    // Test with first property
    $testProperty = $properties->first();
    if ($testProperty) {
        echo "   - Testing with Property ID: {$testProperty->id}\n";
        
        $units = Unit::where('property_id', $testProperty->id)
            ->select('id', 'code', 'unit_type')
            ->get();
        
        echo "   - Units returned: " . $units->count() . "\n";
        
        if ($units->count() > 0) {
            echo "   - Sample data:\n";
            foreach ($units as $unit) {
                echo "     * ID: {$unit->id}, Code: {$unit->code}, Type: {$unit->unit_type}\n";
            }
        }
        
        // Test JSON response
        $jsonResponse = json_encode(['units' => $units]);
        echo "   - JSON Response: " . $jsonResponse . "\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
    if ($units->count() > 0) {
        echo "✅ Units data is available!\n";
        echo "\nNext steps:\n";
        echo "1. Test route: /agent/units-test?property_id={$testProperty->id}\n";
        echo "2. Test form: /agent/meters/create\n";
        echo "3. Check browser console for any errors\n";
    } else {
        echo "❌ No units found for testing!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
