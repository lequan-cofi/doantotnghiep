<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    /**
     * Display a listing of units for properties assigned to the agent.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy các properties được gán cho agent này
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            return view('agent.units.index', [
                'units' => collect(),
                'properties' => collect(),
                'selectedProperty' => null
            ]);
        }

        // Query units với filter
        $query = Unit::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'leases' => function($q) {
                $q->where('status', 'active')->whereNull('deleted_at');
            }]);

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by code
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        // Get units with sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['id', 'created_at', 'code', 'floor', 'property_id', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $units = $query->orderBy($sortBy, $sortOrder)->get();

        // Lấy danh sách properties để hiển thị trong filter
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Thêm thông tin trạng thái thuê cho mỗi unit
        $units->each(function ($unit) {
            $unit->is_rented = $unit->leases->count() > 0;
            $unit->current_lease = $unit->leases->first();
        });

        return view('agent.units.index', [
            'units' => $units,
            'properties' => $properties,
            'selectedProperty' => $request->property_id,
            'selectedStatus' => $request->status,
            'search' => $request->search
        ]);
    }

    /**
     * Show the form for creating a new unit.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy các properties được gán cho agent này
        $properties = $user->assignedProperties()
            ->where('properties.status', 1)
            ->orderBy('name')
            ->get();

        if ($properties->isEmpty()) {
            return redirect()->route('agent.units.index')
                ->with('error', 'Bạn chưa được gán quản lý bất động sản nào.');
        }

        // Pre-select property if provided
        $selectedProperty = null;
        if ($request->filled('property_id')) {
            $selectedProperty = $properties->find($request->property_id);
        }

        return view('agent.units.create', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty
        ]);
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'code' => 'required|string|max:50',
            'floor' => 'nullable|integer|min:1|max:100',
            'area_m2' => 'nullable|numeric|min:0|max:1000',
            'unit_type' => 'required|in:room,apartment,dorm,shared',
            'base_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:10',
            'status' => 'required|in:available,reserved,occupied,maintenance',
            'note' => 'nullable|string|max:1000'
        ], [
            'property_id.required' => 'Vui lòng chọn bất động sản.',
            'property_id.exists' => 'Bất động sản không tồn tại.',
            'code.required' => 'Vui lòng nhập mã phòng.',
            'code.max' => 'Mã phòng không được vượt quá 50 ký tự.',
            'floor.integer' => 'Số tầng phải là số nguyên.',
            'floor.min' => 'Số tầng phải lớn hơn 0.',
            'floor.max' => 'Số tầng không được vượt quá 100.',
            'area_m2.numeric' => 'Diện tích phải là số.',
            'area_m2.min' => 'Diện tích phải lớn hơn 0.',
            'area_m2.max' => 'Diện tích không được vượt quá 1000 m².',
            'unit_type.required' => 'Vui lòng chọn loại phòng.',
            'unit_type.in' => 'Loại phòng không hợp lệ.',
            'base_rent.required' => 'Vui lòng nhập giá thuê cơ bản.',
            'base_rent.numeric' => 'Giá thuê phải là số.',
            'base_rent.min' => 'Giá thuê phải lớn hơn 0.',
            'deposit_amount.numeric' => 'Tiền cọc phải là số.',
            'deposit_amount.min' => 'Tiền cọc phải lớn hơn 0.',
            'max_occupancy.required' => 'Vui lòng nhập số người tối đa.',
            'max_occupancy.integer' => 'Số người tối đa phải là số nguyên.',
            'max_occupancy.min' => 'Số người tối đa phải lớn hơn 0.',
            'max_occupancy.max' => 'Số người tối đa không được vượt quá 10.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.'
        ]);

        // Kiểm tra xem property có được gán cho agent này không
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        if (!$assignedPropertyIds->contains($request->property_id)) {
            return back()->withErrors(['property_id' => 'Bạn không có quyền tạo phòng cho bất động sản này.']);
        }

        // Kiểm tra mã phòng trùng lặp trong cùng property
        $existingUnit = Unit::where('property_id', $request->property_id)
            ->where('code', $request->code)
            ->first();
        
        if ($existingUnit) {
            return back()->withErrors(['code' => 'Mã phòng đã tồn tại trong bất động sản này.']);
        }

        try {
            DB::beginTransaction();

            $unit = Unit::create([
                'property_id' => $request->property_id,
                'code' => $request->code,
                'floor' => $request->floor,
                'area_m2' => $request->area_m2,
                'unit_type' => $request->unit_type,
                'base_rent' => $request->base_rent,
                'deposit_amount' => $request->deposit_amount ?? 0,
                'max_occupancy' => $request->max_occupancy,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            DB::commit();

            return redirect()->route('agent.units.index')
                ->with('success', 'Tạo phòng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Display the specified unit.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy unit và kiểm tra quyền truy cập
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $unit = Unit::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'leases' => function($q) {
                $q->where('status', 'active')->whereNull('deleted_at')->with('tenant');
            }])
            ->findOrFail($id);

        $unit->is_rented = $unit->leases->count() > 0;
        $unit->current_lease = $unit->leases->first();

        return view('agent.units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified unit.
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy unit và kiểm tra quyền truy cập
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $unit = Unit::whereIn('property_id', $assignedPropertyIds)
            ->with('property')
            ->findOrFail($id);

        // Lấy danh sách properties được gán
        $properties = $user->assignedProperties()
            ->where('properties.status', 1)
            ->orderBy('name')
            ->get();

        return view('agent.units.edit', [
            'unit' => $unit,
            'properties' => $properties
        ]);
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy unit và kiểm tra quyền truy cập
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $unit = Unit::whereIn('property_id', $assignedPropertyIds)
            ->findOrFail($id);

        // Validate request
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'code' => 'required|string|max:50',
            'floor' => 'nullable|integer|min:1|max:100',
            'area_m2' => 'nullable|numeric|min:0|max:1000',
            'unit_type' => 'required|in:room,apartment,dorm,shared',
            'base_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:10',
            'status' => 'required|in:available,reserved,occupied,maintenance',
            'note' => 'nullable|string|max:1000'
        ], [
            'property_id.required' => 'Vui lòng chọn bất động sản.',
            'property_id.exists' => 'Bất động sản không tồn tại.',
            'code.required' => 'Vui lòng nhập mã phòng.',
            'code.max' => 'Mã phòng không được vượt quá 50 ký tự.',
            'floor.integer' => 'Số tầng phải là số nguyên.',
            'floor.min' => 'Số tầng phải lớn hơn 0.',
            'floor.max' => 'Số tầng không được vượt quá 100.',
            'area_m2.numeric' => 'Diện tích phải là số.',
            'area_m2.min' => 'Diện tích phải lớn hơn 0.',
            'area_m2.max' => 'Diện tích không được vượt quá 1000 m².',
            'unit_type.required' => 'Vui lòng chọn loại phòng.',
            'unit_type.in' => 'Loại phòng không hợp lệ.',
            'base_rent.required' => 'Vui lòng nhập giá thuê cơ bản.',
            'base_rent.numeric' => 'Giá thuê phải là số.',
            'base_rent.min' => 'Giá thuê phải lớn hơn 0.',
            'deposit_amount.numeric' => 'Tiền cọc phải là số.',
            'deposit_amount.min' => 'Tiền cọc phải lớn hơn 0.',
            'max_occupancy.required' => 'Vui lòng nhập số người tối đa.',
            'max_occupancy.integer' => 'Số người tối đa phải là số nguyên.',
            'max_occupancy.min' => 'Số người tối đa phải lớn hơn 0.',
            'max_occupancy.max' => 'Số người tối đa không được vượt quá 10.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.'
        ]);

        // Kiểm tra xem property có được gán cho agent này không
        if (!$assignedPropertyIds->contains($request->property_id)) {
            return back()->withErrors(['property_id' => 'Bạn không có quyền chỉnh sửa phòng cho bất động sản này.']);
        }

        // Kiểm tra mã phòng trùng lặp trong cùng property (trừ phòng hiện tại)
        $existingUnit = Unit::where('property_id', $request->property_id)
            ->where('code', $request->code)
            ->where('id', '!=', $id)
            ->first();
        
        if ($existingUnit) {
            return back()->withErrors(['code' => 'Mã phòng đã tồn tại trong bất động sản này.']);
        }

        try {
            DB::beginTransaction();

            $unit->update([
                'property_id' => $request->property_id,
                'code' => $request->code,
                'floor' => $request->floor,
                'area_m2' => $request->area_m2,
                'unit_type' => $request->unit_type,
                'base_rent' => $request->base_rent,
                'deposit_amount' => $request->deposit_amount ?? 0,
                'max_occupancy' => $request->max_occupancy,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            DB::commit();

            return redirect()->route('agent.units.index')
                ->with('success', 'Cập nhật phòng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Lấy unit và kiểm tra quyền truy cập
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        $unit = Unit::whereIn('property_id', $assignedPropertyIds)
            ->with('leases')
            ->findOrFail($id);

        // Kiểm tra xem phòng có đang được thuê không
        $activeLeases = $unit->leases->where('status', 'active')->whereNull('deleted_at');
        if ($activeLeases->count() > 0) {
            return back()->withErrors(['error' => 'Không thể xóa phòng đang có hợp đồng thuê hoạt động.']);
        }

        try {
            DB::beginTransaction();

            $unit->delete();

            DB::commit();

            return redirect()->route('agent.units.index')
                ->with('success', 'Xóa phòng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa phòng. Vui lòng thử lại.']);
        }
    }
}