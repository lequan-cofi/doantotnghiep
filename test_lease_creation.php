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

echo "Testing lease creation and commission events...\n";

// Get test data
$unit = Unit::first();
$user = User::where('id', 5)->first(); // Agent user
$organization = Organization::find(1);

if (!$unit || !$user || !$organization) {
    echo "Missing test data\n";
    exit;
}

echo "Unit ID: {$unit->id}\n";
echo "User ID: {$user->id}\n";
echo "Organization ID: {$organization->id}\n";

// Check commission policies
$policies = \App\Models\CommissionPolicy::where('organization_id', $organization->id)
    ->where('trigger_event', 'lease_signed')
    ->where('active', true)
    ->get();

echo "Active lease_signed policies: " . $policies->count() . "\n";
foreach ($policies as $policy) {
    echo "Policy: {$policy->title}, Percent: {$policy->percent_value}%\n";
}

// Create a test lease
echo "\nCreating test lease...\n";
$lease = Lease::create([
    'organization_id' => $organization->id,
    'unit_id' => $unit->id,
    'tenant_id' => 1,
    'agent_id' => $user->id,
    'start_date' => now()->addDays(1),
    'end_date' => now()->addYear(),
    'rent_amount' => 7000000,
    'deposit_amount' => 1400000,
    'billing_day' => 1,
    'status' => 'active',
    'contract_no' => 'TEST-AUTO-' . time(),
    'signed_at' => now(),
]);

echo "Lease created with ID: {$lease->id}\n";

// Test the createCommissionEvents method
$controller = new LeaseController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('createCommissionEvents');
$method->setAccessible(true);

echo "\nCalling createCommissionEvents method...\n";
try {
    $method->invoke($controller, $lease, $organization);
    echo "Commission events creation method executed successfully\n";
} catch (Exception $e) {
    echo "Error in commission events creation: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Check commission events
$events = CommissionEvent::where('lease_id', $lease->id)->get();
echo "\nCommission events created: " . $events->count() . "\n";

foreach ($events as $event) {
    echo "Event ID: {$event->id}, Policy: {$event->policy->title}, Amount: {$event->commission_total}, Status: {$event->status}\n";
}
