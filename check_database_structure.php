<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Analyzing Database Structure for SaaS Super Admin...\n";

try {
    // Get all tables
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $tableNames = [];
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        $tableNames[] = $tableName;
    }
    
    echo "\n=== DATABASE TABLES ===\n";
    foreach ($tableNames as $table) {
        echo "✅ $table\n";
    }
    
    // Analyze key SaaS tables
    echo "\n=== SAAS CORE TABLES ANALYSIS ===\n";
    
    // Organizations (Tenants)
    if (in_array('organizations', $tableNames)) {
        $orgCount = \Illuminate\Support\Facades\DB::table('organizations')->count();
        echo "✅ Organizations (Tenants): $orgCount\n";
        
        $orgs = \Illuminate\Support\Facades\DB::table('organizations')->select('id', 'name', 'status', 'created_at')->get();
        foreach ($orgs as $org) {
            echo "  - ID {$org->id}: {$org->name} (Status: {$org->status})\n";
        }
    }
    
    // Users
    if (in_array('users', $tableNames)) {
        $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
        echo "✅ Users: $userCount\n";
        
        // Count users by organization
        $usersByOrg = \Illuminate\Support\Facades\DB::table('users')
            ->join('organization_users', 'users.id', '=', 'organization_users.user_id')
            ->join('organizations', 'organizations.id', '=', 'organization_users.organization_id')
            ->select('organizations.name', \Illuminate\Support\Facades\DB::raw('count(*) as user_count'))
            ->groupBy('organizations.id', 'organizations.name')
            ->get();
            
        foreach ($usersByOrg as $userGroup) {
            echo "  - {$userGroup->name}: {$userGroup->user_count} users\n";
        }
    }
    
    // Properties (Business Data)
    if (in_array('properties', $tableNames)) {
        $propertyCount = \Illuminate\Support\Facades\DB::table('properties')->count();
        echo "✅ Properties: $propertyCount\n";
        
        $propertiesByOrg = \Illuminate\Support\Facades\DB::table('properties')
            ->join('organizations', 'organizations.id', '=', 'properties.organization_id')
            ->select('organizations.name', \Illuminate\Support\Facades\DB::raw('count(*) as property_count'))
            ->groupBy('organizations.id', 'organizations.name')
            ->get();
            
        foreach ($propertiesByOrg as $propGroup) {
            echo "  - {$propGroup->name}: {$propGroup->property_count} properties\n";
        }
    }
    
    // Leases (Revenue Data)
    if (in_array('leases', $tableNames)) {
        $leaseCount = \Illuminate\Support\Facades\DB::table('leases')->count();
        $activeLeases = \Illuminate\Support\Facades\DB::table('leases')->where('status', 'active')->count();
        echo "✅ Leases: $leaseCount total, $activeLeases active\n";
    }
    
    // Invoices (Billing Data)
    if (in_array('invoices', $tableNames)) {
        $invoiceCount = \Illuminate\Support\Facades\DB::table('invoices')->count();
        $paidInvoices = \Illuminate\Support\Facades\DB::table('invoices')->where('status', 'paid')->count();
        echo "✅ Invoices: $invoiceCount total, $paidInvoices paid\n";
    }
    
    // Payments (Revenue)
    if (in_array('payments', $tableNames)) {
        $paymentCount = \Illuminate\Support\Facades\DB::table('payments')->count();
        $totalRevenue = \Illuminate\Support\Facades\DB::table('payments')->where('status', 'completed')->sum('amount');
        echo "✅ Payments: $paymentCount total, " . number_format($totalRevenue) . " VND revenue\n";
    }
    
    // System Tables
    echo "\n=== SYSTEM TABLES ===\n";
    $systemTables = ['migrations', 'failed_jobs', 'password_resets', 'cache', 'sessions'];
    foreach ($systemTables as $table) {
        if (in_array($table, $tableNames)) {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            echo "✅ $table: $count records\n";
        }
    }
    
    // Analyze SaaS Metrics
    echo "\n=== SAAS METRICS ===\n";
    
    // Total Organizations
    $totalOrgs = \Illuminate\Support\Facades\DB::table('organizations')->count();
    echo "📊 Total Organizations: $totalOrgs\n";
    
    // Active Organizations (with recent activity)
    $activeOrgs = \Illuminate\Support\Facades\DB::table('organizations')
        ->whereExists(function($query) {
            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('users')
                ->join('organization_users', 'users.id', '=', 'organization_users.user_id')
                ->whereColumn('organization_users.organization_id', 'organizations.id')
                ->where('users.last_login_at', '>=', now()->subDays(30));
        })
        ->count();
    echo "📊 Active Organizations (30 days): $activeOrgs\n";
    
    // Total Users
    $totalUsers = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "📊 Total Users: $totalUsers\n";
    
    // Active Users (recent login)
    $activeUsers = \Illuminate\Support\Facades\DB::table('users')
        ->where('last_login_at', '>=', now()->subDays(30))
        ->count();
    echo "📊 Active Users (30 days): $activeUsers\n";
    
    // Total Revenue
    $totalRevenue = \Illuminate\Support\Facades\DB::table('payments')
        ->where('status', 'completed')
        ->sum('amount');
    echo "📊 Total Revenue: " . number_format($totalRevenue) . " VND\n";
    
    // Monthly Recurring Revenue (MRR) - estimated from active leases
    $mrr = \Illuminate\Support\Facades\DB::table('leases')
        ->where('status', 'active')
        ->sum('rent_amount');
    echo "📊 Monthly Recurring Revenue (MRR): " . number_format($mrr) . " VND\n";
    
    echo "\n=== SUPER ADMIN DASHBOARD REQUIREMENTS ===\n";
    echo "✅ Organizations Management\n";
    echo "✅ Users Management\n";
    echo "✅ Revenue Analytics\n";
    echo "✅ System Health\n";
    echo "✅ Billing & Subscriptions\n";
    echo "✅ Support & Tickets\n";
    echo "✅ System Settings\n";
    echo "✅ Audit Logs\n";
    
    echo "\n=== ANALYSIS COMPLETED ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
