<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the manager dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Get organization ID
        $organizationId = $user->organizations()->first()?->id;
        
        if (!$organizationId) {
            return redirect()->route('manager.organizations.create')
                ->with('error', 'Bạn cần tham gia một tổ chức trước khi sử dụng dashboard.');
        }

        // Get dashboard data
        $dashboardData = $this->getDashboardData($organizationId);

        return view('manager.dashboard', compact('dashboardData'));
    }

    /**
     * Get all dashboard data with caching.
     */
    private function getDashboardData($organizationId)
    {
        // Cache key with organization ID
        $cacheKey = "dashboard_data_org_{$organizationId}";
        
        // Cache for 5 minutes
        return Cache::remember($cacheKey, 300, function () use ($organizationId) {
            return [
                'stats' => $this->getKeyStats($organizationId),
                'revenue' => $this->getRevenueStats($organizationId),
                'occupancy' => $this->getOccupancyStats($organizationId),
                'topPerformers' => $this->getTopPerformers($organizationId),
                'urgentTasks' => $this->getUrgentTasks($organizationId),
                'recentActivities' => $this->getRecentActivities($organizationId),
                'analytics' => $this->getAnalyticsData($organizationId),
            ];
        });
    }

    /**
     * Get key performance statistics.
     */
    private function getKeyStats($organizationId)
    {
        try {
            // Properties count
            $propertiesCount = DB::table('properties')
                ->where('organization_id', $organizationId)
                ->count();

            // Occupancy rate - based on active leases, not units.status
            $totalUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->count();

            // Count units that have active leases
            $occupiedUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('leases')
                        ->whereColumn('leases.unit_id', 'units.id')
                        ->where('leases.status', 'active')
                        ->whereNull('leases.deleted_at');
                })
                ->count();

            $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

            // Upcoming viewings
            $upcomingViewings = DB::table('viewings')
                ->join('properties', 'properties.id', '=', 'viewings.property_id')
                ->where('properties.organization_id', $organizationId)
                ->where('viewings.schedule_at', '>=', now())
                ->where('viewings.status', 'confirmed')
                ->count();

            // Conversion rate
            $totalLeads = DB::table('leads')
                ->where('organization_id', $organizationId)
                ->count();

            $convertedLeads = DB::table('leads')
                ->where('organization_id', $organizationId)
                ->where('status', 'converted')
                ->count();

            $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

            return [
                'properties_count' => $propertiesCount,
                'occupancy_rate' => $occupancyRate,
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'upcoming_viewings' => $upcomingViewings,
                'conversion_rate' => $conversionRate,
                'total_leads' => $totalLeads,
                'converted_leads' => $convertedLeads,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting key stats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Get revenue and commission statistics.
     */
    private function getRevenueStats($organizationId)
    {
        try {
            // Monthly revenue from invoices
            $monthlyRevenue = DB::table('invoices')
                ->where('organization_id', $organizationId)
                ->where('status', 'paid')
                ->whereYear('issue_date', now()->year)
                ->whereMonth('issue_date', now()->month)
                ->sum('total_amount');

            // Previous month revenue for comparison
            $previousMonthRevenue = DB::table('invoices')
                ->where('organization_id', $organizationId)
                ->where('status', 'paid')
                ->whereYear('issue_date', now()->subMonth()->year)
                ->whereMonth('issue_date', now()->subMonth()->month)
                ->sum('total_amount');

            $revenueGrowth = $previousMonthRevenue > 0 
                ? round((($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
                : 0;

            // Monthly commission
            $monthlyCommission = DB::table('commission_events')
                ->where('organization_id', $organizationId)
                ->whereYear('occurred_at', now()->year)
                ->whereMonth('occurred_at', now()->month)
                ->sum('commission_total');

            // Previous month commission
            $previousMonthCommission = DB::table('commission_events')
                ->where('organization_id', $organizationId)
                ->whereYear('occurred_at', now()->subMonth()->year)
                ->whereMonth('occurred_at', now()->subMonth()->month)
                ->sum('commission_total');

            $commissionGrowth = $previousMonthCommission > 0 
                ? round((($monthlyCommission - $previousMonthCommission) / $previousMonthCommission) * 100, 1)
                : 0;

            // Pending invoices and tickets
            $pendingInvoices = DB::table('invoices')
                ->where('organization_id', $organizationId)
                ->whereIn('status', ['issued', 'overdue'])
                ->count();

            $openTickets = DB::table('tickets')
                ->where('organization_id', $organizationId)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            return [
                'monthly_revenue' => $monthlyRevenue,
                'revenue_growth' => $revenueGrowth,
                'monthly_commission' => $monthlyCommission,
                'commission_growth' => $commissionGrowth,
                'pending_invoices' => $pendingInvoices,
                'open_tickets' => $openTickets,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting revenue stats: ' . $e->getMessage());
            return [
                'monthly_revenue' => 0,
                'revenue_growth' => 0,
                'monthly_commission' => 0,
                'commission_growth' => 0,
                'pending_invoices' => 0,
                'open_tickets' => 0,
            ];
        }
    }

    /**
     * Get occupancy statistics based on leases.
     */
    private function getOccupancyStats($organizationId)
    {
        try {
            // Get total units
            $totalUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->count();

            // Count units with active leases (occupied)
            $occupiedUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('leases')
                        ->whereColumn('leases.unit_id', 'units.id')
                        ->where('leases.status', 'active')
                        ->whereNull('leases.deleted_at');
                })
                ->count();

            // Count units with pending leases (reserved)
            $reservedUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('leases')
                        ->whereColumn('leases.unit_id', 'units.id')
                        ->where('leases.status', 'pending')
                        ->whereNull('leases.deleted_at');
                })
                ->count();

            // Count units with maintenance status
            $maintenanceUnits = DB::table('units')
                ->join('properties', 'properties.id', '=', 'units.property_id')
                ->where('properties.organization_id', $organizationId)
                ->where('units.status', 'maintenance')
                ->count();

            // Available units = total - occupied - reserved - maintenance
            $availableUnits = $totalUnits - $occupiedUnits - $reservedUnits - $maintenanceUnits;

            return [
                'available' => max(0, $availableUnits),
                'occupied' => $occupiedUnits,
                'reserved' => $reservedUnits,
                'maintenance' => $maintenanceUnits,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting occupancy stats: ' . $e->getMessage());
            return [
                'available' => 0,
                'occupied' => 0,
                'reserved' => 0,
                'maintenance' => 0,
            ];
        }
    }

    /**
     * Get top performing agents.
     */
    private function getTopPerformers($organizationId)
    {
        try {
            return DB::table('commission_events')
                ->join('users', 'users.id', '=', 'commission_events.user_id')
                ->where('commission_events.organization_id', $organizationId)
                ->select(
                    'users.id',
                    'users.full_name',
                    DB::raw('SUM(commission_events.commission_total) as total_commission'),
                    DB::raw('COUNT(*) as deals')
                )
                ->whereYear('commission_events.created_at', now()->year)
                ->whereMonth('commission_events.created_at', now()->month)
                ->groupBy('users.id', 'users.full_name')
                ->orderByDesc('total_commission')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting top performers: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get urgent tasks that need immediate attention.
     */
    private function getUrgentTasks($organizationId)
    {
        try {
            $overdueInvoices = DB::table('invoices')
                ->where('organization_id', $organizationId)
                ->where('status', 'overdue')
                ->count();

            $expiringLeases = DB::table('leases')
                ->where('organization_id', $organizationId)
                ->where('end_date', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count();

            $pendingViewings = DB::table('viewings')
                ->join('properties', 'properties.id', '=', 'viewings.property_id')
                ->where('properties.organization_id', $organizationId)
                ->where('viewings.status', 'requested')
                ->count();

            return [
                'overdue_invoices' => $overdueInvoices,
                'expiring_leases' => $expiringLeases,
                'pending_viewings' => $pendingViewings,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting urgent tasks: ' . $e->getMessage());
            return [
                'overdue_invoices' => 0,
                'expiring_leases' => 0,
                'pending_viewings' => 0,
            ];
        }
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities($organizationId)
    {
        try {
            return DB::table('audit_logs')
                ->join('users', 'users.id', '=', 'audit_logs.actor_id')
                ->where('audit_logs.organization_id', $organizationId)
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit(5)
                ->select('audit_logs.*', 'users.full_name')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting recent activities: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get analytics data for the last 30 days.
     */
    private function getAnalyticsData($organizationId)
    {
        try {
            $thirtyDaysAgo = now()->subDays(30);

            $newLeads = DB::table('leads')
                ->where('organization_id', $organizationId)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();

            $totalViewings = DB::table('viewings')
                ->join('properties', 'properties.id', '=', 'viewings.property_id')
                ->where('properties.organization_id', $organizationId)
                ->where('viewings.created_at', '>=', $thirtyDaysAgo)
                ->count();

            $newLeases = DB::table('leases')
                ->where('organization_id', $organizationId)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();

            $newDeposits = DB::table('booking_deposits')
                ->join('properties', 'properties.id', '=', 'booking_deposits.property_id')
                ->where('properties.organization_id', $organizationId)
                ->where('booking_deposits.created_at', '>=', $thirtyDaysAgo)
                ->where('booking_deposits.payment_status', 'paid')
                ->count();

            return [
                'new_leads' => $newLeads,
                'total_viewings' => $totalViewings,
                'new_leases' => $newLeases,
                'new_deposits' => $newDeposits,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting analytics data: ' . $e->getMessage());
            return [
                'new_leads' => 0,
                'total_viewings' => 0,
                'new_leases' => 0,
                'new_deposits' => 0,
            ];
        }
    }

    /**
     * Get default stats when there's an error.
     */
    private function getDefaultStats()
    {
        return [
            'properties_count' => 0,
            'occupancy_rate' => 0,
            'total_units' => 0,
            'occupied_units' => 0,
            'upcoming_viewings' => 0,
            'conversion_rate' => 0,
            'total_leads' => 0,
            'converted_leads' => 0,
        ];
    }

    /**
     * Clear dashboard cache.
     */
    public function clearCache()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $organizationId = $user->organizations()->first()?->id;
        
        if ($organizationId) {
            $cacheKey = "dashboard_data_org_{$organizationId}";
            Cache::forget($cacheKey);
        }

        return response()->json(['success' => true, 'message' => 'Dashboard cache cleared successfully']);
    }
}
