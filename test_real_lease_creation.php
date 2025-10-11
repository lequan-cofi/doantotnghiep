<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lease;
use App\Models\CommissionEvent;
use App\Models\Unit;
use App\Models\User;
use App\Models\Organization;
use App\Http\Controllers\Agent\LeaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "Testing real lease creation with web form data...\n";

// Get test data
$unit = Unit::first();
$user = User::where('id', 5)->first(); // Agent user
$organization = Organization::find(1);

if (!$unit || !$user || !$organization) {
    echo "Missing test data\n";
    exit;
}

// Mock authentication
Auth::login($user);

echo "Unit ID: {$unit->id}\n";
echo "User ID: {$user->id}\n";
echo "Organization ID: {$organization->id}\n";

// Create a mock request with real form data
$request = new Request();
$request->merge([
    'unit_id' => $unit->id,
    'tenant_id' => 1,
    'start_date' => now()->addDays(1)->format('Y-m-d'),
    'end_date' => now()->addYear()->format('Y-m-d'),
    'rent_amount' => '10.000.000', // Formatted currency
    'deposit_amount' => '2.000.000', // Formatted currency
    'billing_day' => 1,
    'status' => 'active',
    'signed_at' => now()->format('Y-m-d'),
    'contract_no' => 'TEST-REAL-' . time(),
]);

echo "\nRequest data:\n";
echo "Status: {$request->status}\n";
echo "Rent amount: {$request->rent_amount}\n";
echo "Deposit amount: {$request->deposit_amount}\n";

// Test the store method
$controller = new LeaseController();

try {
    echo "\nCalling store method...\n";
    $response = $controller->store($request);
    echo "Store method executed successfully\n";
    
    // Get the latest lease
    $latestLease = Lease::latest()->first();
    echo "Latest lease ID: {$latestLease->id}\n";
    echo "Latest lease status: {$latestLease->status}\n";
    echo "Latest lease rent_amount: {$latestLease->rent_amount}\n";
    echo "Latest lease deposit_amount: {$latestLease->deposit_amount}\n";
    
    // Check commission events
    $events = CommissionEvent::where('lease_id', $latestLease->id)->get();
    echo "Commission events created: " . $events->count() . "\n";
    
    foreach ($events as $event) {
        echo "Event ID: {$event->id}, Policy: {$event->policy->title}, Amount: {$event->commission_total}, Status: {$event->status}\n";
    }
    
} catch (Exception $e) {
    echo "Error in store method: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
