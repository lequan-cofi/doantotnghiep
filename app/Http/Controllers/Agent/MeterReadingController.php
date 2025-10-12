<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Lease;
use App\Models\LeaseService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\MeterBillingService;
use App\Traits\NotificationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MeterReadingController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of meter readings
     */
    public function index(Request $request)
    {
        $query = MeterReading::with(['meter.property', 'meter.unit', 'meter.service', 'takenBy']);

        // Filter by meter
        if ($request->filled('meter_id')) {
            $query->where('meter_id', $request->meter_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('reading_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('reading_date', '<=', $request->date_to);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->whereHas('meter', function($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }

        $readings = $query->latest('reading_date')->paginate(20);

        // Get filter options
        $meters = Meter::with(['property', 'unit', 'service'])
            ->select('id', 'property_id', 'unit_id', 'service_id', 'serial_no')
            ->get();

        return view('agent.meter-readings.index', compact('readings', 'meters'));
    }

    /**
     * Show the form for creating a new meter reading
     */
    public function create(Request $request)
    {
        $meters = Meter::with(['property', 'unit', 'service'])
            ->where('status', true)
            ->get();

        $selectedMeter = null;
        $lastReading = null;

        if ($request->filled('meter_id')) {
            $selectedMeter = Meter::with(['property', 'unit', 'service'])
                ->find($request->meter_id);
            
            if ($selectedMeter) {
                $lastReading = $selectedMeter->readings()
                    ->latest('reading_date')
                    ->first();
            }
        }

        return view('agent.meter-readings.create', compact('meters', 'selectedMeter', 'lastReading'));
    }

    /**
     * Store a newly created meter reading
     */
    public function store(Request $request)
    {
        $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'reading_date' => 'required|date',
            'value' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $meter = Meter::findOrFail($request->meter_id);

            // Check if reading already exists for this date
            $existingReading = $meter->readings()
                ->whereDate('reading_date', $request->reading_date)
                ->first();

            if ($existingReading) {
                return $this->notifyError(
                    'Đã tồn tại số liệu đo cho ngày này. Vui lòng chọn ngày khác hoặc cập nhật số liệu hiện có.',
                    'Số liệu đã tồn tại'
                );
            }

            // Get last reading to validate value
            $lastReading = $meter->readings()
                ->latest('reading_date')
                ->first();

            if ($lastReading && $request->value < $lastReading->value) {
                return $this->notifyError(
                    'Số liệu đo mới không được nhỏ hơn số liệu trước đó (' . $lastReading->value . ').',
                    'Số liệu không hợp lệ'
                );
            }

            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = $this->uploadImage($request->file('image'), $meter);
            }

            $reading = MeterReading::create([
                'meter_id' => $request->meter_id,
                'reading_date' => $request->reading_date,
                'value' => $request->value,
                'image_url' => $imageUrl,
                'taken_by' => Auth::id(),
                'note' => $request->note,
            ]);

            // Calculate usage and create billing if needed
            $billingService = new MeterBillingService();
            $billingService->processBilling($reading);

            DB::commit();

            return $this->jsonResponse(
                true,
                'Số liệu đo đã được lưu thành công!',
                'Lưu số liệu thành công',
                [],
                route('agent.meter-readings.show', $reading->id)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi lưu số liệu đo');
        }
    }

    /**
     * Display the specified meter reading
     */
    public function show(MeterReading $meterReading)
    {
        $meterReading->load(['meter.property', 'meter.unit', 'meter.service', 'takenBy']);

        // Get previous and next readings
        $previousReading = MeterReading::where('meter_id', $meterReading->meter_id)
            ->where('reading_date', '<', $meterReading->reading_date)
            ->latest('reading_date')
            ->first();

        $nextReading = MeterReading::where('meter_id', $meterReading->meter_id)
            ->where('reading_date', '>', $meterReading->reading_date)
            ->oldest('reading_date')
            ->first();

        // Calculate usage
        $usage = $previousReading ? $meterReading->value - $previousReading->value : 0;

        // Get current lease and pricing
        $currentLease = Lease::where('unit_id', $meterReading->meter->unit_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->with(['services' => function($q) use ($meterReading) {
                $q->where('service_id', $meterReading->meter->service_id);
            }])
            ->first();

        $servicePrice = 0;
        if ($currentLease && $currentLease->services->isNotEmpty()) {
            $servicePrice = $currentLease->services->first()->price;
        }

        $cost = $usage * $servicePrice;

        return view('agent.meter-readings.show', compact(
            'meterReading', 
            'previousReading', 
            'nextReading', 
            'usage', 
            'servicePrice', 
            'cost'
        ));
    }

    /**
     * Show the form for editing the specified meter reading
     */
    public function edit(MeterReading $meterReading)
    {
        $meterReading->load(['meter.property', 'meter.unit', 'meter.service']);

        return view('agent.meter-readings.edit', compact('meterReading'));
    }

    /**
     * Update the specified meter reading
     */
    public function update(Request $request, MeterReading $meterReading)
    {
        $request->validate([
            'reading_date' => 'required|date',
            'value' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Check if reading already exists for this date (excluding current reading)
            $existingReading = MeterReading::where('meter_id', $meterReading->meter_id)
                ->whereDate('reading_date', $request->reading_date)
                ->where('id', '!=', $meterReading->id)
                ->first();

            if ($existingReading) {
                return $this->notifyError(
                    'Đã tồn tại số liệu đo cho ngày này. Vui lòng chọn ngày khác.',
                    'Ngày đã tồn tại'
                );
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($meterReading->image_url) {
                    Storage::disk('public')->delete($meterReading->image_url);
                }
                $imageUrl = $this->uploadImage($request->file('image'), $meterReading->meter);
            } else {
                $imageUrl = $meterReading->image_url;
            }

            $meterReading->update([
                'reading_date' => $request->reading_date,
                'value' => $request->value,
                'image_url' => $imageUrl,
                'note' => $request->note,
            ]);

            DB::commit();

            return $this->jsonResponse(
                true,
                'Số liệu đo đã được cập nhật thành công!',
                'Cập nhật số liệu thành công',
                [],
                route('agent.meter-readings.show', $meterReading->id)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi cập nhật số liệu đo');
        }
    }

    /**
     * Remove the specified meter reading
     */
    public function destroy(MeterReading $meterReading)
    {
        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($meterReading->image_url) {
                Storage::disk('public')->delete($meterReading->image_url);
            }

            $meterReading->delete();

            DB::commit();

            return $this->notifySuccess(
                'Số liệu đo đã được xóa thành công!',
                'Xóa số liệu thành công'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Có lỗi xảy ra khi xóa số liệu đo');
        }
    }

    /**
     * Get last reading for a meter (AJAX)
     */
    public function getLastReading(Request $request)
    {
        $meterId = $request->meter_id;
        
        if (!$meterId) {
            return response()->json(['lastReading' => null]);
        }

        $lastReading = MeterReading::where('meter_id', $meterId)
            ->latest('reading_date')
            ->first();

        return response()->json(['lastReading' => $lastReading]);
    }

    /**
     * Upload meter reading image
     */
    private function uploadImage($file, Meter $meter)
    {
        $path = 'meter-readings/' . $meter->property_id . '/' . $meter->unit_id;
        $filename = time() . '_' . $file->getClientOriginalName();
        
        return $file->storeAs($path, $filename, 'public');
    }

}
