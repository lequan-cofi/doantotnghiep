<?php

use Illuminate\Support\Facades\Route;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

// Test soft delete with deleted_by
Route::get('/test-soft-delete', function () {
    // Login as user 1 for testing
    Auth::loginUsingId(1);
    
    // Create a test property
    $property = Property::create([
        'name' => 'Test Property for Soft Delete',
        'status' => 1,
        'total_rooms' => 10,
    ]);
    
    echo "Created property ID: {$property->id}<br>";
    echo "Created by user ID: " . Auth::id() . "<br><br>";
    
    // Soft delete it
    $property->delete();
    
    // Refresh to get deleted_at and deleted_by
    $property = Property::withTrashed()->find($property->id);
    
    echo "After soft delete:<br>";
    echo "deleted_at: {$property->deleted_at}<br>";
    echo "deleted_by: {$property->deleted_by}<br><br>";
    
    // Restore it
    $property->restore();
    $property->refresh();
    
    echo "After restore:<br>";
    echo "deleted_at: " . ($property->deleted_at ?? 'null') . "<br>";
    echo "deleted_by: " . ($property->deleted_by ?? 'null') . "<br><br>";
    
    // Force delete to clean up
    $property->forceDelete();
    
    echo "Test completed! Property was force deleted to clean up.";
});

