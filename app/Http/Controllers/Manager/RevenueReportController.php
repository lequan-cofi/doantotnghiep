<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Lease;
use App\Models\CommissionEvent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueReportController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organizations()->first()?->id;

        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly

        // Convert to Carbon instances
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get revenue data
        $revenueData = $this->getRevenueData($organizationId, $start, $end, $period);
        
        // Get property statistics
        $propertyStats = $this->getPropertyStatistics($organizationId, $start, $end);
        
        // Get commission statistics
        $commissionStats = $this->getCommissionStatistics($organizationId, $start, $end);
        
        // Get top performers
        $topPerformers = $this->getTopPerformers($organizationId, $start, $end);
        
        // Get occupancy rates
        $occupancyRates = $this->getOccupancyRates($organizationId, $start, $end);
        
        // Get revenue trends
        $revenueTrends = $this->getRevenueTrends($organizationId, $start, $end, $period);

        return view('manager.revenue-reports.index', compact(
            'revenueData',
            'propertyStats',
            'commissionStats',
            'topPerformers',
            'occupancyRates',
            'revenueTrends',
            'startDate',
            'endDate',
            'period'
        ));
    }

    public function detail(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organizations()->first()?->id;

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $type = $request->get('type', 'all'); // all, rental, sale, commission

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get detailed revenue data
        $detailedData = $this->getDetailedRevenueData($organizationId, $start, $end, $type);

        return view('manager.revenue-reports.detail', compact(
            'detailedData',
            'startDate',
            'endDate',
            'type'
        ));
    }

    private function getRevenueData($organizationId, $start, $end, $period)
    {
        // Total revenue from leases
        $totalRevenue = Lease::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->sum(DB::raw('rent_amount + deposit_amount'));

        // Revenue by type (all leases are rental type in this system)
        $revenueByType = collect([
            'rental' => $totalRevenue,
            'sale' => 0 // No sale in current system
        ]);

        // Revenue by period
        $revenueByPeriod = $this->getRevenueByPeriod($organizationId, $start, $end, $period);

        // Commission revenue from commission events
        $commissionRevenue = CommissionEvent::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_total');

        // Payment revenue (actual received from invoices)
        $paymentRevenue = Payment::whereHas('invoice', function($query) use ($organizationId, $start, $end) {
                $query->where('organization_id', $organizationId)
                      ->whereBetween('created_at', [$start, $end]);
            })
            ->where('status', 'completed')
            ->sum('amount');

        // Invoice revenue (total invoiced)
        $invoiceRevenue = Invoice::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'revenue_by_type' => $revenueByType,
            'revenue_by_period' => $revenueByPeriod,
            'commission_revenue' => $commissionRevenue,
            'payment_revenue' => $paymentRevenue,
            'invoice_revenue' => $invoiceRevenue,
            'pending_revenue' => $invoiceRevenue - $paymentRevenue,
        ];
    }

    private function getRevenueByPeriod($organizationId, $start, $end, $period)
    {
        $query = Lease::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end]);

        switch ($period) {
            case 'daily':
                return $query->select(
                    DB::raw('DATE(created_at) as period'),
                    DB::raw('SUM(rent_amount + deposit_amount) as total')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            case 'weekly':
                return $query->select(
                    DB::raw('YEARWEEK(created_at) as period'),
                    DB::raw('SUM(rent_amount + deposit_amount) as total')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            case 'monthly':
                return $query->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                    DB::raw('SUM(rent_amount + deposit_amount) as total')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            case 'yearly':
                return $query->select(
                    DB::raw('YEAR(created_at) as period'),
                    DB::raw('SUM(rent_amount + deposit_amount) as total')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            default:
                return collect();
        }
    }

    private function getPropertyStatistics($organizationId, $start, $end)
    {
        $totalProperties = Property::where('organization_id', $organizationId)->count();
        
        $activeProperties = Property::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->count();

        $rentedProperties = Property::where('organization_id', $organizationId)
            ->whereHas('units.leases', function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end])
                      ->where('status', 'active');
            })
            ->count();

        $newProperties = Property::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'total_properties' => $totalProperties,
            'active_properties' => $activeProperties,
            'rented_properties' => $rentedProperties,
            'new_properties' => $newProperties,
            'occupancy_rate' => $totalProperties > 0 ? round(($rentedProperties / $totalProperties) * 100, 2) : 0,
        ];
    }

    private function getCommissionStatistics($organizationId, $start, $end)
    {
        $totalCommission = CommissionEvent::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_total');

        $paidCommission = CommissionEvent::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('commission_total');

        $pendingCommission = CommissionEvent::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'pending')
            ->sum('commission_total');

        $commissionByType = CommissionEvent::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->select('trigger_event', DB::raw('SUM(commission_total) as total'))
            ->groupBy('trigger_event')
            ->get()
            ->pluck('total', 'trigger_event');

        return [
            'total_commission' => $totalCommission,
            'paid_commission' => $paidCommission,
            'pending_commission' => $pendingCommission,
            'commission_by_type' => $commissionByType,
        ];
    }

    private function getTopPerformers($organizationId, $start, $end)
    {
        return User::whereHas('organizations', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereHas('leasesAsAgent', function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->withCount(['leasesAsAgent as leases_count' => function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }])
            ->withSum(['leasesAsAgent as total_revenue' => function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }], DB::raw('rent_amount + deposit_amount'))
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();
    }

    private function getOccupancyRates($organizationId, $start, $end)
    {
        $properties = Property::where('organization_id', $organizationId)
            ->with(['units.leases' => function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end])
                      ->where('status', 'active');
            }])
            ->get();

        $occupancyData = [];
        foreach ($properties as $property) {
            $totalUnits = $property->units->count();
            $occupiedUnits = $property->units->sum(function($unit) {
                return $unit->leases->count();
            });
            
            $occupancyData[] = [
                'property_name' => $property->name,
                'property_type' => $property->type,
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'occupancy_rate' => $totalUnits > 0 ? 
                    round(($occupiedUnits / $totalUnits) * 100, 2) : 0,
            ];
        }

        return collect($occupancyData)->sortByDesc('occupancy_rate');
    }

    private function getRevenueTrends($organizationId, $start, $end, $period)
    {
        // Get revenue trends for the last 12 periods
        $trends = [];
        $current = $start->copy();

        for ($i = 0; $i < 12; $i++) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy();

            switch ($period) {
                case 'daily':
                    $periodEnd->addDay();
                    $label = $periodStart->format('d/m');
                    break;
                case 'weekly':
                    $periodEnd->addWeek();
                    $label = 'Tuần ' . $periodStart->weekOfYear;
                    break;
                case 'monthly':
                    $periodEnd->addMonth();
                    $label = $periodStart->format('m/Y');
                    break;
                case 'yearly':
                    $periodEnd->addYear();
                    $label = $periodStart->format('Y');
                    break;
            }

            $revenue = Lease::where('organization_id', $organizationId)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum(DB::raw('rent_amount + deposit_amount'));

            $trends[] = [
                'period' => $label,
                'revenue' => $revenue,
                'date' => $periodStart->format('Y-m-d'),
            ];

            $current = $periodEnd;
        }

        return collect($trends);
    }

    private function getDetailedRevenueData($organizationId, $start, $end, $type)
    {
        $query = Lease::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->with(['unit.property', 'agent', 'invoices.payments']);

        // All leases are rental type in this system
        if ($type === 'sale') {
            return collect(); // No sale leases
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
