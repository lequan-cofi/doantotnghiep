<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * Display a listing of the tenant's contracts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all leases for the authenticated tenant
        $query = Lease::with([
            'unit.property.location',
            'unit.property.location2025',
            'unit.property.propertyType',
            'invoices' => function($q) {
                $q->latest('issue_date');
            },
            'leaseServices.service',
            'agent'
        ])
        ->where('tenant_id', $user->id)
        ->whereNull('deleted_at');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('unit.property', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'expiring') {
                $query->where('status', 'active')
                      ->where('end_date', '<=', Carbon::now()->addDays(30))
                      ->where('end_date', '>', Carbon::now());
            } elseif ($status === 'expired') {
                $query->where(function($q) {
                    $q->where('status', 'expired')
                      ->orWhere('end_date', '<', Carbon::now());
                });
            }
        }

        $contracts = $query->latest('start_date')->paginate(10);

        // Calculate statistics
        $stats = $this->calculateContractStats($user->id);

        return view('tenant.contract.index', compact('contracts', 'stats'));
    }

    /**
     * Display the specified contract
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Get contract with all related data
        $contract = Lease::with([
            'unit.property.location',
            'unit.property.location2025',
            'unit.property.propertyType',
            'unit.meters.service',
            'unit.meters.readings' => function($q) {
                $q->latest('reading_date');
            },
            'invoices.items',
            'leaseServices.service',
            'residents',
            'agent',
            'tenant'
        ])
        ->where('id', $id)
        ->where('tenant_id', $user->id)
        ->whereNull('deleted_at')
        ->firstOrFail();

        // Get meter readings summary (last 5 readings)
        $meterReadingsSummary = $this->getMeterReadingsSummary($contract->unit_id, 5);

        // Get all meter readings for the contract period
        $meterReadingsHistory = $this->getMeterReadingsHistory($contract->unit_id, $contract->start_date);

        // Get invoices with pagination
        $invoices = Invoice::where('lease_id', $contract->id)
            ->latest('issue_date')
            ->paginate(10);

        // Calculate remaining days
        $remainingDays = Carbon::now()->diffInDays($contract->end_date, false);
        $isExpired = $contract->end_date < Carbon::now();
        $isExpiring = !$isExpired && $remainingDays <= 30;

        return view('tenant.contract.show', compact(
            'contract',
            'meterReadingsSummary',
            'meterReadingsHistory',
            'invoices',
            'remainingDays',
            'isExpired',
            'isExpiring'
        ));
    }

    /**
     * Calculate contract statistics
     */
    private function calculateContractStats($tenantId)
    {
        $now = Carbon::now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        $active = Lease::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('end_date', '>', $now)
            ->whereNull('deleted_at')
            ->count();

        $expiring = Lease::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('end_date', '<=', $thirtyDaysFromNow)
            ->where('end_date', '>', $now)
            ->whereNull('deleted_at')
            ->count();

        $expired = Lease::where('tenant_id', $tenantId)
            ->where(function($q) use ($now) {
                $q->where('status', 'expired')
                  ->orWhere('end_date', '<', $now);
            })
            ->whereNull('deleted_at')
            ->count();

        $total = Lease::where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->count();

        return [
            'active' => $active,
            'expiring' => $expiring,
            'expired' => $expired,
            'total' => $total
        ];
    }

    /**
     * Get meter readings summary
     */
    private function getMeterReadingsSummary($unitId, $limit = 5)
    {
        return MeterReading::with(['meter.service'])
            ->whereHas('meter', function($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            })
            ->latest('reading_date')
            ->limit($limit)
            ->get()
            ->groupBy('meter.service.name');
    }

    /**
     * Get meter readings history
     */
    private function getMeterReadingsHistory($unitId, $startDate)
    {
        return MeterReading::with(['meter.service'])
            ->whereHas('meter', function($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            })
            ->where('reading_date', '>=', $startDate)
            ->latest('reading_date')
            ->get()
            ->groupBy('meter.service.name');
    }

    /**
     * Get address from location (old format)
     */
    private function getLocationAddress($location)
    {
        if (!$location) {
            return null;
        }

        $addressParts = [];
        
        if ($location->street) {
            $addressParts[] = $location->street;
        }
        
        if ($location->ward) {
            $addressParts[] = $location->ward;
        }
        
        if ($location->district) {
            $addressParts[] = $location->district;
        }
        
        if ($location->city) {
            $addressParts[] = $location->city;
        }
        
        if ($location->country && $location->country !== 'Vietnam') {
            $addressParts[] = $location->country;
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : null;
    }

    /**
     * Get address from location2025 (new format)
     */
    private function getLocation2025Address($location2025)
    {
        if (!$location2025) {
            return null;
        }

        $addressParts = [];
        
        if ($location2025->street) {
            $addressParts[] = $location2025->street;
        }
        
        if ($location2025->ward) {
            $addressParts[] = $location2025->ward;
        }
        
        if ($location2025->city) {
            $addressParts[] = $location2025->city;
        }
        
        if ($location2025->country && $location2025->country !== 'Vietnam') {
            $addressParts[] = $location2025->country;
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : null;
    }
}
