<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Viewing;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ViewingController extends Controller
{
    /**
     * Store a new viewing request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'nullable|exists:units,id',
            'lead_name' => 'required|string|max:255',
            'lead_phone' => 'required|string|max:20',
            'lead_email' => 'nullable|email|max:255',
            'schedule_date' => 'required|date|after:today',
            'schedule_time' => 'required|string',
            'note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get property and agent info
        $property = Property::with('agent')->findOrFail($request->property_id);
        $agentId = $property->agent_id;

        // Combine date and time
        $scheduleAt = Carbon::createFromFormat('Y-m-d H:i', 
            $request->schedule_date . ' ' . $request->schedule_time);

        // Check for conflicts
        $conflict = Viewing::where('agent_id', $agentId)
            ->where('schedule_at', '>=', $scheduleAt->copy()->subHour())
            ->where('schedule_at', '<=', $scheduleAt->copy()->addHour())
            ->whereIn('status', ['requested', 'confirmed'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Agent đã có lịch trong khoảng thời gian này. Vui lòng chọn thời gian khác.'
            ], 409);
        }

        // Create viewing
        $viewing = Viewing::create([
            'lead_id' => Auth::id(),
            'listing_id' => $request->property_id,
            'agent_id' => $agentId,
            'unit_id' => $request->unit_id,
            'lead_name' => $request->lead_name,
            'lead_phone' => $request->lead_phone,
            'lead_email' => $request->lead_email,
            'schedule_at' => $scheduleAt,
            'status' => 'requested',
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt lịch thành công! Agent sẽ liên hệ lại với bạn sớm nhất.',
            'viewing' => $viewing
        ]);
    }

    /**
     * Get available time slots for a specific date and agent
     */
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::findOrFail($request->property_id);
        $agentId = $property->agent_id;
        $date = Carbon::parse($request->date);

        // Define available time slots
        $timeSlots = [
            '08:00', '09:00', '10:00', '11:00',
            '14:00', '15:00', '16:00', '17:00'
        ];

        // Get booked slots for this date
        $bookedSlots = Viewing::where('agent_id', $agentId)
            ->whereDate('schedule_at', $date)
            ->whereIn('status', ['requested', 'confirmed'])
            ->pluck('schedule_at')
            ->map(function ($datetime) {
                return Carbon::parse($datetime)->format('H:i');
            })
            ->toArray();

        // Filter available slots
        $availableSlots = array_diff($timeSlots, $bookedSlots);

        return response()->json([
            'success' => true,
            'available_slots' => array_values($availableSlots),
            'booked_slots' => $bookedSlots
        ]);
    }

    /**
     * Get user's viewing history
     */
    public function myViewings()
    {
        $viewings = Viewing::with(['property', 'unit', 'agent'])
            ->where('lead_id', Auth::id())
            ->orderBy('schedule_at', 'desc')
            ->paginate(10);

        return view('viewings.my-viewings', compact('viewings'));
    }

    /**
     * Show user's appointments (enhanced view)
     */
    public function appointments()
    {
        $viewings = Viewing::with(['property.location2025', 'unit', 'agent'])
            ->where('lead_id', Auth::id())
            ->orderBy('schedule_at', 'desc')
            ->get();

        return view('tenant.appointments', compact('viewings'));
    }

    /**
     * Cancel a viewing
     */
    public function cancel(Request $request, $id)
    {
        $viewing = Viewing::where('lead_id', Auth::id())
            ->findOrFail($id);

        if ($viewing->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Lịch đặt này đã được hủy trước đó.'
            ], 400);
        }

        if ($viewing->status === 'done') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy lịch đặt đã hoàn thành.'
            ], 400);
        }

        $viewing->update([
            'status' => 'cancelled',
            'result_note' => 'Khách hàng hủy lịch đặt'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy lịch đặt thành công.'
        ]);
    }

    /**
     * Show viewing details
     */
    public function show($id)
    {
        $viewing = Viewing::with(['property', 'unit', 'agent'])
            ->where('lead_id', Auth::id())
            ->findOrFail($id);

        return view('viewings.show', compact('viewing'));
    }

    /**
     * Show booking form
     */
    public function booking($property_id = null, $unit_id = null)
    {
        $property = null;
        $unit = null;

        if ($property_id) {
            $property = Property::with(['location2025', 'agent'])->findOrFail($property_id);
            
            if ($unit_id) {
                $unit = Unit::where('property_id', $property_id)
                    ->where('status', 'available')
                    ->findOrFail($unit_id);
            }
        }

        return view('tenant.booking', compact('property', 'unit'));
    }
}
