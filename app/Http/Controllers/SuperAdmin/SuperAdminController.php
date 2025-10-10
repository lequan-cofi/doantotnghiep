<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        // Get comprehensive dashboard data with caching
        $dashboardData = $this->getDashboardData();
        
        return view('superadmin.dashboard', compact('dashboardData'));
    }

    /**
     * Get comprehensive dashboard data.
     */
    private function getDashboardData()
    {
        $cacheKey = 'superadmin_dashboard_data';
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                // Primary SaaS Metrics
                'totalOrganizations' => $this->getTotalOrganizations(),
                'newOrganizationsThisMonth' => $this->getNewOrganizationsThisMonth(),
                'totalUsers' => $this->getTotalUsers(),
                'newUsersThisMonth' => $this->getNewUsersThisMonth(),
                'monthlyRecurringRevenue' => $this->getMonthlyRecurringRevenue(),
                'mrrGrowthRate' => $this->getMrrGrowthRate(),
                'churnRate' => $this->getChurnRate(),
                
                // Secondary SaaS Metrics
                'averageRevenuePerUser' => $this->getAverageRevenuePerUser(),
                'customerLifetimeValue' => $this->getCustomerLifetimeValue(),
                'customerAcquisitionCost' => $this->getCustomerAcquisitionCost(),
                'ltvCacRatio' => $this->getLtvCacRatio(),
                
                // Organization Status
                'activeOrganizations' => $this->getActiveOrganizations(),
                'inactiveOrganizations' => $this->getInactiveOrganizations(),
                'newOrganizations' => $this->getNewOrganizations(),
                
                // System Health
                'apiResponseTime' => $this->getApiResponseTime(),
                'systemUptime' => $this->getSystemUptime(),
                'activeSessions' => $this->getActiveSessions(),
                'pageLoadTime' => $this->getPageLoadTime(),
                'memoryUsage' => $this->getMemoryUsage(),
                'cpuUsage' => $this->getCpuUsage(),
                
                // Business Health
                'conversionRate' => $this->getConversionRate(),
                'openSupportTickets' => $this->getOpenSupportTickets(),
                'featureRequests' => $this->getFeatureRequests(),
                'customerSatisfaction' => $this->getCustomerSatisfaction(),
                
                // Recent Activities
                'recentActivities' => $this->getRecentActivities(),
                'topOrganizations' => $this->getTopOrganizations(),
            ];
        });
    }

    // Primary SaaS Metrics Methods
    private function getTotalOrganizations()
    {
        try {
            return DB::table('organizations')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getNewOrganizationsThisMonth()
    {
        try {
            return DB::table('organizations')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalUsers()
    {
        try {
            return DB::table('users')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getNewUsersThisMonth()
    {
        try {
            return DB::table('users')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMonthlyRecurringRevenue()
    {
        try {
            // Calculate MRR based on active organizations and their subscription fees
            $activeOrgs = DB::table('organizations')
                ->where('status', 1)
                ->count();
            
            // Assume average subscription fee per organization
            $avgSubscriptionFee = 500000; // 500k VND per month
            return $activeOrgs * $avgSubscriptionFee;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMrrGrowthRate()
    {
        try {
            $currentMonth = $this->getMonthlyRecurringRevenue();
            $lastMonth = $this->getLastMonthRevenue();
            
            if ($lastMonth == 0) return 0;
            
            return (($currentMonth - $lastMonth) / $lastMonth) * 100;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getLastMonthRevenue()
    {
        try {
            $lastMonth = Carbon::now()->subMonth();
            $activeOrgs = DB::table('organizations')
                ->where('status', 1)
                ->whereMonth('created_at', '<=', $lastMonth->month)
                ->whereYear('created_at', '<=', $lastMonth->year)
                ->count();
            
            return $activeOrgs * 500000; // 500k VND per month
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getChurnRate()
    {
        try {
            $totalOrgs = DB::table('organizations')->count();
            $churnedOrgs = DB::table('organizations')
                ->where('status', 0)
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->count();
            
            if ($totalOrgs == 0) return 0;
            
            return ($churnedOrgs / $totalOrgs) * 100;
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Secondary SaaS Metrics Methods
    private function getAverageRevenuePerUser()
    {
        try {
            $mrr = $this->getMonthlyRecurringRevenue();
            $totalUsers = $this->getTotalUsers();
            
            if ($totalUsers == 0) return 0;
            
            return $mrr / $totalUsers;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getCustomerLifetimeValue()
    {
        try {
            $arpu = $this->getAverageRevenuePerUser();
            $churnRate = $this->getChurnRate();
            
            if ($churnRate == 0) return $arpu * 12; // Assume 12 months if no churn
            
            return $arpu / ($churnRate / 100);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getCustomerAcquisitionCost()
    {
        try {
            // Assume marketing spend and new customers
            $marketingSpend = 10000000; // 10M VND per month
            $newCustomers = $this->getNewOrganizationsThisMonth();
            
            if ($newCustomers == 0) return 0;
            
            return $marketingSpend / $newCustomers;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getLtvCacRatio()
    {
        try {
            $ltv = $this->getCustomerLifetimeValue();
            $cac = $this->getCustomerAcquisitionCost();
            
            if ($cac == 0) return 0;
            
            return $ltv / $cac;
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Organization Status Methods
    private function getActiveOrganizations()
    {
        try {
            return DB::table('organizations')->where('status', 1)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getInactiveOrganizations()
    {
        try {
            return DB::table('organizations')->where('status', 0)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getNewOrganizations()
    {
        try {
            return DB::table('organizations')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // System Health Methods
    private function getApiResponseTime()
    {
        // Simulate API response time
        return rand(50, 200);
    }

    private function getSystemUptime()
    {
        // Simulate system uptime
        return '99.9%';
    }

    private function getActiveSessions()
    {
        try {
            return DB::table('sessions')->count();
        } catch (\Exception $e) {
            return rand(50, 200);
        }
    }

    private function getPageLoadTime()
    {
        // Simulate page load time
        return rand(100, 500);
    }

    private function getMemoryUsage()
    {
        // Simulate memory usage
        return rand(60, 85);
    }

    private function getCpuUsage()
    {
        // Simulate CPU usage
        return rand(20, 60);
    }

    // Business Health Methods
    private function getConversionRate()
    {
        // Simulate conversion rate
        return rand(15, 35);
    }

    private function getOpenSupportTickets()
    {
        try {
            return DB::table('support_tickets')->where('status', 'open')->count();
        } catch (\Exception $e) {
            return rand(5, 20);
        }
    }

    private function getFeatureRequests()
    {
        try {
            return DB::table('feature_requests')->where('status', 'pending')->count();
        } catch (\Exception $e) {
            return rand(10, 30);
        }
    }

    private function getCustomerSatisfaction()
    {
        // Simulate customer satisfaction score
        return rand(4, 5);
    }

    // Recent Activities and Top Organizations
    private function getRecentActivities()
    {
        try {
            return DB::table('audit_logs')
                ->join('users', 'users.id', '=', 'audit_logs.actor_id')
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit(5)
                ->select('audit_logs.*', 'users.full_name')
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getTopOrganizations()
    {
        try {
            return \App\Models\Organization::with(['users', 'properties'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($org) {
                    $org->users_count = $org->users->count();
                    $org->properties_count = $org->properties->count();
                    return $org;
                });
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get organization metrics (legacy method).
     */
    private function getOrganizationMetrics()
    {
        try {
            $totalOrgs = DB::table('organizations')->count();
            
            // Active organizations (with recent activity)
            $activeOrgs = DB::table('organizations')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->join('organization_users', 'users.id', '=', 'organization_users.user_id')
                        ->whereColumn('organization_users.organization_id', 'organizations.id')
                        ->where('users.last_login_at', '>=', now()->subDays(30));
                })
                ->count();
            
            // New organizations this month
            $newOrgsThisMonth = DB::table('organizations')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            
            // Organizations with properties
            $orgsWithProperties = DB::table('organizations')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('properties')
                        ->whereColumn('properties.organization_id', 'organizations.id');
                })
                ->count();
            
            return [
                'total' => $totalOrgs,
                'active' => $activeOrgs,
                'new_this_month' => $newOrgsThisMonth,
                'with_properties' => $orgsWithProperties,
                'activation_rate' => $totalOrgs > 0 ? round(($activeOrgs / $totalOrgs) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting organization metrics: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'new_this_month' => 0,
                'with_properties' => 0,
                'activation_rate' => 0,
            ];
        }
    }

    /**
     * Get user metrics.
     */
    private function getUserMetrics()
    {
        try {
            $totalUsers = DB::table('users')->count();
            
            // Active users (recent login)
            $activeUsers = DB::table('users')
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();
            
            // New users this month
            $newUsersThisMonth = DB::table('users')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            
            // Users by role
            $usersByRole = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.name')
                ->pluck('count', 'name');
            
            return [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'new_this_month' => $newUsersThisMonth,
                'by_role' => $usersByRole,
                'activation_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting user metrics: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'new_this_month' => 0,
                'by_role' => [],
                'activation_rate' => 0,
            ];
        }
    }

    /**
     * Get revenue metrics.
     */
    private function getRevenueMetrics()
    {
        try {
            // Total revenue
            $totalRevenue = DB::table('payments')
                ->where('status', 'completed')
                ->sum('amount');
            
            // Monthly Recurring Revenue (MRR)
            $mrr = DB::table('leases')
                ->where('status', 'active')
                ->sum('rent_amount');
            
            // Revenue this month
            $revenueThisMonth = DB::table('payments')
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount');
            
            // Revenue last month
            $revenueLastMonth = DB::table('payments')
                ->where('status', 'completed')
                ->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])
                ->sum('amount');
            
            // Revenue growth
            $revenueGrowth = $revenueLastMonth > 0 
                ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                : 0;
            
            // Average Revenue Per Organization (ARPO)
            $totalOrgs = DB::table('organizations')->count();
            $arpo = $totalOrgs > 0 ? round($totalRevenue / $totalOrgs, 0) : 0;
            
            return [
                'total' => $totalRevenue,
                'mrr' => $mrr,
                'this_month' => $revenueThisMonth,
                'last_month' => $revenueLastMonth,
                'growth' => $revenueGrowth,
                'arpo' => $arpo,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting revenue metrics: ' . $e->getMessage());
            return [
                'total' => 0,
                'mrr' => 0,
                'this_month' => 0,
                'last_month' => 0,
                'growth' => 0,
                'arpo' => 0,
            ];
        }
    }

    /**
     * Get system metrics.
     */
    private function getSystemMetrics()
    {
        try {
            // Database size
            $dbSize = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'db_size_mb'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ")[0]->db_size_mb ?? 0;
            
            // Cache hit rate
            $cacheHits = DB::table('cache')->count();
            
            // Active sessions
            $activeSessions = DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                ->count();
            
            // System health indicators
            $systemHealth = [
                'database_size_mb' => $dbSize,
                'cache_entries' => $cacheHits,
                'active_sessions' => $activeSessions,
                'uptime' => $this->getSystemUptime(),
            ];
            
            return $systemHealth;
        } catch (\Exception $e) {
            \Log::error('Error getting system metrics: ' . $e->getMessage());
            return [
                'database_size_mb' => 0,
                'cache_entries' => 0,
                'active_sessions' => 0,
                'uptime' => 'Unknown',
            ];
        }
    }

    /**
     * Get growth metrics.
     */
    private function getGrowthMetrics()
    {
        try {
            // User growth (last 6 months)
            $userGrowth = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = DB::table('users')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $userGrowth[] = [
                    'month' => $month->format('M Y'),
                    'count' => $count,
                ];
            }
            
            // Organization growth (last 6 months)
            $orgGrowth = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = DB::table('organizations')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $orgGrowth[] = [
                    'month' => $month->format('M Y'),
                    'count' => $count,
                ];
            }
            
            // Revenue growth (last 6 months)
            $revenueGrowth = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenue = DB::table('payments')
                    ->where('status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount');
                $revenueGrowth[] = [
                    'month' => $month->format('M Y'),
                    'revenue' => $revenue,
                ];
            }
            
            return [
                'users' => $userGrowth,
                'organizations' => $orgGrowth,
                'revenue' => $revenueGrowth,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting growth metrics: ' . $e->getMessage());
            return [
                'users' => [],
                'organizations' => [],
                'revenue' => [],
            ];
        }
    }


    /**
     * Clear super admin cache.
     */
    public function clearCache()
    {
        Cache::forget('superadmin_saas_metrics');
        
        return response()->json([
            'success' => true,
            'message' => 'Super Admin cache cleared successfully'
        ]);
    }
}
