<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Service;
use App\Models\Lease;
use App\Services\MeterBillingService;
use App\Traits\NotificationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MeterController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of meters
     */
    public function index(Request $request)
    {
        $query = Meter::with(['property', 'unit', 'service', 'readings' => function($q) {
            $q->latest('reading_date')->limit(1);
        }]);

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active');
        }

        $meters = $query->paginate(15);

        // Get filter options
        $properties = Property::select('id', 'name')->get();
        $services = Service::select('id', 'name', 'key_code')->get();

        return view('agent.meters.index', compact('meters', 'properties', 'services'));
    }

    /**
     * Show the form for creating a new meter
     */
    public function create(Request $request)
    {
        $properties = Property::select('id', 'name')->get();
        $services = Service::select('id', 'name', 'key_code', 'unit_label')->get();
        
        $selectedProperty = null;
        $units = collect();
        
        if ($request->filled('property_id')) {
            $selectedProperty = Property::find($request->property_id);
            $units = Unit::where('property_id', $request->property_id)
                ->select('id', 'code', 'unit_type')
                ->get();
        }

        return view('agent.meters.create', compact('properties', 'services', 'selectedProperty', 'units'));
    }

    /**
     * Store a newly created meter
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'required|exists:units,id',
            'service_id' => 'required|exists:services,id',
            'serial_no' => 'required|string|max:255',
            'installed_at' => 'required|date',
            'status' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $meter = Meter::create([
                'property_id' => $request->property_id,
                'unit_id' => $request->unit_id,
                'service_id' => $request->service_id,
                'serial_no' => $request->serial_no,
                'installed_at' => $request->installed_at,
                'status' => $request->boolean('status', true),
            ]);

            DB::commit();

            return $this->jsonResponse(
                true,
                'Công tơ đo đã được tạo thành công!',
                'Tạo công tơ thành công',
                [],
                route('agent.meters.show', $meter->id)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi tạo công tơ đo');
        }
    }

    /**
     * Display the specified meter
     */
    public function show(Meter $meter)
    {
        $meter->load(['property', 'unit', 'service', 'readings' => function($q) {
            $q->with('takenBy')->latest('reading_date');
        }]);

        // Get current lease for this unit
        $currentLease = Lease::where('unit_id', $meter->unit_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->with(['tenant', 'lead'])
            ->first();

        // Get billing history for this meter
        $billingHistory = $this->getBillingHistory($meter);

        return view('agent.meters.show', compact('meter', 'currentLease', 'billingHistory'));
    }

    /**
     * Show the form for editing the specified meter
     */
    public function edit(Meter $meter)
    {
        $meter->load(['property', 'unit', 'service']);
        
        $properties = Property::select('id', 'name')->get();
        $services = Service::select('id', 'name', 'key_code', 'unit_label')->get();
        $units = Unit::where('property_id', $meter->property_id)
            ->select('id', 'code', 'unit_type')
            ->get();

        return view('agent.meters.edit', compact('meter', 'properties', 'services', 'units'));
    }

    /**
     * Update the specified meter
     */
    public function update(Request $request, Meter $meter)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'required|exists:units,id',
            'service_id' => 'required|exists:services,id',
            'serial_no' => 'required|string|max:255',
            'installed_at' => 'required|date',
            'status' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $meter->update([
                'property_id' => $request->property_id,
                'unit_id' => $request->unit_id,
                'service_id' => $request->service_id,
                'serial_no' => $request->serial_no,
                'installed_at' => $request->installed_at,
                'status' => $request->boolean('status', true),
            ]);

            DB::commit();

            return $this->jsonResponse(
                true,
                'Công tơ đo đã được cập nhật thành công!',
                'Cập nhật công tơ thành công',
                [],
                route('agent.meters.show', $meter->id)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi cập nhật công tơ đo');
        }
    }

    /**
     * Remove the specified meter
     */
    public function destroy(Meter $meter)
    {
        try {
            DB::beginTransaction();

            // Check if meter has readings
            if ($meter->readings()->count() > 0) {
                return $this->notifyError(
                    'Không thể xóa công tơ đo đã có số liệu đo. Vui lòng xóa tất cả số liệu trước.',
                    'Không thể xóa'
                );
            }

            $meter->delete();

            DB::commit();

            return $this->notifySuccess(
                'Công tơ đo đã được xóa thành công!',
                'Xóa công tơ thành công'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi xóa công tơ đo');
        }
    }

    /**
     * Get units for a property (AJAX)
     */
    public function getUnits(Request $request)
    {
        try {
            $propertyId = $request->property_id;
            
            if (!$propertyId) {
                return response()->json(['units' => []]);
            }

            $units = Unit::where('property_id', $propertyId)
                ->select('id', 'code', 'unit_type')
                ->get();

            return response()->json(['units' => $units]);

        } catch (\Exception $e) {
            \Log::error('Error loading units for property: ' . $e->getMessage(), [
                'property_id' => $request->property_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Có lỗi xảy ra khi tải danh sách phòng',
                'units' => []
            ], 500);
        }
    }

    /**
     * Get billing history for a meter
     */
    private function getBillingHistory(Meter $meter)
    {
        $billingService = new MeterBillingService();
        return $billingService->getBillingHistory($meter->id);
    }
}