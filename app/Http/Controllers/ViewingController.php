<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Viewing;
use App\Models\Unit;
use App\Models\Property;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ViewingController extends Controller
{
    /**
     * Get available time slots for a property on a specific date
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'date' => 'required|date|after:today'
        ]);

        $property = Property::findOrFail($request->property_id);
        
        // Get agent for this property
        $agentId = $property->owner_id; // Use owner as agent
        
        // Define available time slots (9 AM to 6 PM, 1 hour intervals)
        $availableSlots = [];
        for ($hour = 9; $hour <= 18; $hour++) {
            $timeSlot = sprintf('%02d:00', $hour);
            
            // Check if this slot is already booked
            $conflict = Viewing::where('property_id', $request->property_id)
                ->whereDate('schedule_at', $request->date)
                ->whereTime('schedule_at', $timeSlot)
            ->whereIn('status', ['requested', 'confirmed'])
            ->exists();

            if (!$conflict) {
                $availableSlots[] = $timeSlot;
            }
        }

        return response()->json([
            'success' => true,
            'available_slots' => $availableSlots
        ]);
    }

    /**
     * Show viewing details
     */
    public function show($id)
    {
        $viewing = Viewing::with(['property', 'unit', 'agent'])
            ->where('tenant_id', Auth::id())
            ->findOrFail($id);

        return view('viewings.show', compact('viewing'));
    }

    /**
     * Show edit form for viewing
     */
    public function edit($id)
    {
        $viewing = Viewing::with(['property', 'unit', 'agent'])
            ->where('tenant_id', Auth::id())
            ->findOrFail($id);

        // Only allow editing if status is 'requested' or 'scheduled'
        if (!in_array($viewing->status, ['requested', 'scheduled'])) {
            return redirect()->route('tenant.appointments')
                ->with('error', 'Không thể chỉnh sửa lịch hẹn đã được xác nhận hoặc hoàn thành.');
        }

        return view('tenant.appointments.edit', compact('viewing'));
    }

    /**
     * Get viewing data for editing (API)
     */
    public function getForEdit($id)
    {
        $viewing = Viewing::where('tenant_id', Auth::id())
            ->findOrFail($id);

        // Only allow editing if status is 'requested' or 'scheduled'
        if (!in_array($viewing->status, ['requested', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa lịch hẹn đã được xác nhận hoặc hoàn thành.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $viewing->id,
                'schedule_date' => $viewing->schedule_at->format('Y-m-d'),
                'schedule_time' => $viewing->schedule_at->format('H:i'),
                'note' => $viewing->note,
                'status' => $viewing->status
            ]
        ]);
    }

    /**
     * Show user's viewings
     */
    public function myViewings()
    {
        $viewings = Viewing::with(['property', 'unit', 'agent'])
            ->where('tenant_id', Auth::id())
            ->orderBy('schedule_at', 'desc')
            ->paginate(10);

        return view('viewings.my-viewings', compact('viewings'));
    }

    /**
     * Show appointments (for authenticated users)
     */
    public function appointments()
    {
        $userId = Auth::id();
        
        // Get all viewings for the authenticated user (using tenant_id, not lead_id)
        $viewings = Viewing::with(['property', 'unit', 'agent'])
            ->where('tenant_id', $userId)
            ->orderBy('schedule_at', 'desc') // Show most recent first
            ->get();

        // Debug: Log viewings data
        if (config('app.debug')) {
            Log::info('Appointments data for user ' . $userId, [
                'total_viewings' => $viewings->count(),
                'viewings' => $viewings->map(function($v) {
                    return [
                        'id' => $v->id,
                        'status' => $v->status,
                        'schedule_at' => $v->schedule_at,
                        'property_id' => $v->property_id,
                        'unit_id' => $v->unit_id,
                        'agent_id' => $v->agent_id,
                        'tenant_id' => $v->tenant_id,
                        'lead_id' => $v->lead_id
                    ];
                })->toArray()
            ]);
        }

        return view('tenant.appointments.index', compact('viewings'));
    }

    /**
     * Cancel a viewing
     */
    public function cancel(Request $request, $id)
    {
        $viewing = Viewing::where('tenant_id', Auth::id())
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

        $reason = $request->input('reason', '');
        $cancelNote = $reason ? "Khách hàng hủy lịch đặt. Lý do: {$reason}" : 'Khách hàng hủy lịch đặt';

        $viewing->update([
            'status' => 'cancelled',
            'result_note' => $cancelNote
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy lịch đặt thành công.'
        ]);
    }

    /**
     * Update viewing status
     */
    public function updateStatus(Request $request, $id)
    {
        $viewing = Viewing::where('tenant_id', Auth::id())
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:done,cancelled', // Removed no_show for tenants
            'result_note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if status change is allowed
        $currentStatus = $viewing->status;
        $newStatus = $request->status;

        // Only allow status changes from 'confirmed' to 'done'
        if ($currentStatus !== 'confirmed' && $newStatus === 'done') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể đánh dấu hoàn thành khi lịch hẹn đã được xác nhận.'
            ], 400);
        }

        // Only allow cancellation from 'requested' or 'confirmed'
        if ($newStatus === 'cancelled' && !in_array($currentStatus, ['requested', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy lịch hẹn đã hoàn thành hoặc đã hủy.'
            ], 400);
        }

        try {
            $viewing->update([
                'status' => $newStatus,
                'result_note' => $request->result_note
            ]);

            $statusMessages = [
                'done' => 'Đã đánh dấu lịch hẹn hoàn thành.',
                'cancelled' => 'Đã hủy lịch hẹn thành công.'
            ];

            return response()->json([
                'success' => true,
                'message' => $statusMessages[$newStatus] ?? 'Đã cập nhật trạng thái thành công.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating viewing status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.'
            ], 500);
        }
    }

    /**
     * Update appointment (for authenticated users)
     */
    public function update(Request $request, $id)
    {
        $viewing = Viewing::where('tenant_id', Auth::id())
            ->findOrFail($id);

        // Only allow editing if status is 'requested' or 'scheduled'
        if (!in_array($viewing->status, ['requested', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chỉnh sửa lịch hẹn đã được xác nhận hoặc hoàn thành.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'schedule_date' => 'required|date|after:today',
            'schedule_time' => 'required|date_format:H:i',
            'note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Combine date and time
            $scheduleAt = Carbon::createFromFormat('Y-m-d H:i', 
                $request->schedule_date . ' ' . $request->schedule_time);

            $viewing->update([
                'schedule_at' => $scheduleAt,
                'note' => $request->note,
                'status' => 'requested' // Reset to requested when edited
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật lịch hẹn thành công.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating viewing: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật lịch hẹn.'
            ], 500);
        }
    }

    /**
     * Show booking form
     */
    public function booking($id = null, $unit_id = null)
    {
        $property = null;
        $unit = null;

        if ($id) {
            $property = Property::with(['location2025', 'owner'])->findOrFail($id);
            
            if ($unit_id) {
                $unit = Unit::where('property_id', $id)
                    ->where('status', 'available')
                    ->findOrFail($unit_id);
            } else {
                // If no unit_id provided, get the first available unit
                $unit = Unit::where('property_id', $id)
                    ->where('status', 'available')
                    ->first();
            }
        }

        // Get user info if authenticated
        $user = Auth::user();
        $userInfo = null;
        
        if ($user) {
            $userInfo = [
                'name' => $user->full_name,
                'phone' => $user->phone,
                'email' => $user->email,
            ];
        }

        // Get assigned agent info for display
        $assignedAgent = null;
        if ($property) {
            $assignedAgent = $this->getAssignedAgent($property->id);
        }

        // Debug: Log unit data
        if (config('app.debug') && $unit) {
            Log::info('Unit data for booking:', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'base_rent' => $unit->base_rent,
                'deposit_amount' => $unit->deposit_amount,
                'status' => $unit->status
            ]);
        }

        return view('tenant.booking', compact('property', 'unit', 'userInfo', 'assignedAgent'));
    }

    /**
     * Get assigned agent for a property
     */
    private function getAssignedAgent($propertyId)
    {
        $property = Property::with(['assignedUsers' => function($query) {
            $query->where('properties_user.role_key', 'agent')
                  ->orderBy('properties_user.assigned_at', 'asc'); // Get the first assigned agent
        }])->find($propertyId);
        
        return $property ? $property->assignedUsers->first() : null;
    }

    /**
     * Store a new viewing request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isAuthenticated = $user !== null;
        
        // Validation rules based on authentication status
        $rules = [
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'nullable|exists:units,id',
            'schedule_date' => 'required|date|after:today',
            'schedule_time' => 'required|string',
            'note' => 'nullable|string|max:1000',
        ];
        
        // Only require lead info if user is not authenticated
        if (!$isAuthenticated) {
            $rules['lead_name'] = 'required|string|max:255';
            $rules['lead_phone'] = 'required|string|max:20';
            $rules['lead_email'] = 'nullable|email|max:255';
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Get property and unit info
            $property = Property::findOrFail($request->property_id);
            
            // Combine date and time
            $scheduleAt = $request->schedule_date . ' ' . $request->schedule_time . ':00';
            
            // Find assigned agent for this property
            $assignedAgent = $this->getAssignedAgent($request->property_id);
            $agentId = $assignedAgent ? $assignedAgent->id : null;
            
            // Log agent assignment
            if ($agentId) {
                Log::info("Viewing booking: Assigned agent {$agentId} ({$assignedAgent->full_name}) to property {$property->id} for viewing request");
            } else {
                Log::warning("Viewing booking: No agent assigned to property {$property->id} - viewing will be created without agent");
            }

            $lead = null;
            $tenantId = null;

            if ($isAuthenticated) {
                // User is authenticated - create viewing directly with tenant_id
                $tenantId = $user->id;
                
                // Create viewing for authenticated user
                $viewing = Viewing::create([
                    'tenant_id' => $tenantId,
                    'property_id' => $request->property_id,
                    'unit_id' => $request->unit_id,
                    'agent_id' => $agentId,
                    'organization_id' => $property->organization_id,
                    'lead_name' => $user->full_name,
                    'lead_phone' => $user->phone,
                    'lead_email' => $user->email,
                    'schedule_at' => $scheduleAt,
                    'status' => 'requested',
                    'note' => $request->note,
                ]);
            } else {
                // User is not authenticated - create lead first, then viewing
                $lead = \App\Models\Lead::create([
                    'organization_id' => $property->organization_id,
                    'source' => 'website',
                    'name' => $request->lead_name,
                    'phone' => $request->lead_phone,
                    'email' => $request->lead_email,
                    'status' => 'new',
                ]);

                // Create viewing for lead
                $viewing = Viewing::create([
                    'lead_id' => $lead->id,
                    'property_id' => $request->property_id,
                    'unit_id' => $request->unit_id,
                    'agent_id' => $agentId,
                    'organization_id' => $property->organization_id,
                    'lead_name' => $request->lead_name,
                    'lead_phone' => $request->lead_phone,
                    'lead_email' => $request->lead_email,
                    'schedule_at' => $scheduleAt,
                    'status' => 'requested',
                    'note' => $request->note,
                ]);
            }

            DB::commit();

            // Prepare response message based on authentication status
            if ($isAuthenticated) {
                $message = 'Đặt lịch xem phòng thành công! Bạn có thể quản lý lịch hẹn trong tài khoản của mình.';
                if ($assignedAgent) {
                    $message .= " Agent phụ trách: {$assignedAgent->full_name}";
                }
            } else {
                $message = 'Đặt lịch xem phòng thành công! Chúng tôi sẽ liên hệ lại để xác nhận.';
                if ($assignedAgent) {
                    $message .= " Agent phụ trách: {$assignedAgent->full_name}";
                }
                $message .= ' Để quản lý lịch hẹn dễ dàng hơn, hãy đăng ký tài khoản!';
            }

            // Redirect to property index with success message
            return redirect()->route('property.index')
                ->with('booking_success', true)
                ->with('viewing_id', $viewing->id)
                ->with('message', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating viewing: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.');
        }
    }
}