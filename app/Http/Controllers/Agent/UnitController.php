<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;

class UnitController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
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
                'selectedProperty' => null,
                'selectedStatus' => null,
                'search' => null
            ]);
        }

        // Query units với filter
        $query = Unit::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'amenities', 'leases' => function($q) {
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

        // Get units with sorting - default ID desc
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

        // Load amenities
        $amenities = \App\Models\Amenity::orderBy('category')->orderBy('name')->get();

        return view('agent.units.create', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
            'amenities' => $amenities
        ]);
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $creationMode = $request->input('creation_mode', 'single');
        
        if ($creationMode === 'bulk') {
            return $this->storeBulk($request, $user);
        }
        
        // Validate single unit request
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
            'note' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id'
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

            // Process images
            $imagePaths = [];
            if ($request->hasFile('images')) {
                $uploadedImages = $this->imageService->uploadMultipleImages($request->file('images'), 'units');
                $imagePaths = array_column($uploadedImages, 'original');
            }

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
                'images' => $imagePaths,
            ]);

            // Sync amenities
            if ($request->has('amenities')) {
                $unit->amenities()->sync($request->amenities);
            }

            DB::commit();

            return redirect()->route('agent.units.index')
                ->with('success', 'Tạo phòng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Store multiple units in bulk.
     */
    private function storeBulk(Request $request, $user)
    {
        // Debug logging
        Log::info('Bulk creation request data:', $request->all());
        
        // Determine configuration mode
        $floorConfigMode = $request->input('floor_config_mode', 'simple');
        Log::info('Floor config mode: ' . $floorConfigMode);
        
        if ($floorConfigMode === 'advanced') {
            return $this->storeBulkAdvanced($request, $user);
        }
        
        // Validate simple bulk request
        $request->validate([
            'bulk_property_id' => 'required|exists:properties,id',
            'bulk_unit_type' => 'required|in:room,apartment,dorm,shared',
            'bulk_max_occupancy' => 'required|integer|min:1|max:10',
            'bulk_area_m2' => 'nullable|numeric|min:0|max:1000',
            'bulk_status' => 'required|in:available,reserved,occupied,maintenance',
            'bulk_base_rent' => 'required|numeric|min:0',
            'bulk_deposit_amount' => 'nullable|numeric|min:0',
            'bulk_note' => 'nullable|string|max:1000',
            'start_floor' => 'required|integer|min:1|max:100',
            'end_floor' => 'required|integer|min:1|max:100|gte:start_floor',
            'rooms_per_floor' => 'required|integer|min:1|max:50',
            'room_prefix' => 'nullable|string|max:10',
            'bulk_images' => 'nullable|array',
            'bulk_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id'
        ], [
            'bulk_property_id.required' => 'Vui lòng chọn bất động sản.',
            'bulk_property_id.exists' => 'Bất động sản không tồn tại.',
            'bulk_unit_type.required' => 'Vui lòng chọn loại phòng.',
            'bulk_unit_type.in' => 'Loại phòng không hợp lệ.',
            'bulk_max_occupancy.required' => 'Vui lòng nhập số người tối đa.',
            'bulk_max_occupancy.integer' => 'Số người tối đa phải là số nguyên.',
            'bulk_max_occupancy.min' => 'Số người tối đa phải lớn hơn 0.',
            'bulk_max_occupancy.max' => 'Số người tối đa không được vượt quá 10.',
            'bulk_area_m2.numeric' => 'Diện tích phải là số.',
            'bulk_area_m2.min' => 'Diện tích phải lớn hơn 0.',
            'bulk_area_m2.max' => 'Diện tích không được vượt quá 1000 m².',
            'bulk_status.required' => 'Vui lòng chọn trạng thái.',
            'bulk_status.in' => 'Trạng thái không hợp lệ.',
            'bulk_base_rent.required' => 'Vui lòng nhập giá thuê cơ bản.',
            'bulk_base_rent.numeric' => 'Giá thuê phải là số.',
            'bulk_base_rent.min' => 'Giá thuê phải lớn hơn 0.',
            'bulk_deposit_amount.numeric' => 'Tiền cọc phải là số.',
            'bulk_deposit_amount.min' => 'Tiền cọc phải lớn hơn 0.',
            'bulk_note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'start_floor.required' => 'Vui lòng nhập tầng bắt đầu.',
            'start_floor.integer' => 'Tầng bắt đầu phải là số nguyên.',
            'start_floor.min' => 'Tầng bắt đầu phải lớn hơn 0.',
            'start_floor.max' => 'Tầng bắt đầu không được vượt quá 100.',
            'end_floor.required' => 'Vui lòng nhập tầng kết thúc.',
            'end_floor.integer' => 'Tầng kết thúc phải là số nguyên.',
            'end_floor.min' => 'Tầng kết thúc phải lớn hơn 0.',
            'end_floor.max' => 'Tầng kết thúc không được vượt quá 100.',
            'end_floor.gte' => 'Tầng kết thúc phải lớn hơn hoặc bằng tầng bắt đầu.',
            'rooms_per_floor.required' => 'Vui lòng nhập số phòng mỗi tầng.',
            'rooms_per_floor.integer' => 'Số phòng mỗi tầng phải là số nguyên.',
            'rooms_per_floor.min' => 'Số phòng mỗi tầng phải lớn hơn 0.',
            'rooms_per_floor.max' => 'Số phòng mỗi tầng không được vượt quá 50.',
            'room_prefix.max' => 'Tiền tố mã phòng không được vượt quá 10 ký tự.'
        ]);

        // Kiểm tra xem property có được gán cho agent này không
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        if (!$assignedPropertyIds->contains($request->bulk_property_id)) {
            return back()->withErrors(['bulk_property_id' => 'Bạn không có quyền tạo phòng cho bất động sản này.']);
        }

        $startFloor = $request->start_floor;
        $endFloor = $request->end_floor;
        $roomsPerFloor = $request->rooms_per_floor;
        $roomPrefix = $request->room_prefix ?? 'P';
        
        $totalRooms = ($endFloor - $startFloor + 1) * $roomsPerFloor;
        
        // Giới hạn số phòng tối đa
        if ($totalRooms > 100) {
            return back()->withErrors(['rooms_per_floor' => 'Số phòng tối đa cho phép là 100. Hiện tại sẽ tạo ' . $totalRooms . ' phòng.']);
        }

        try {
            DB::beginTransaction();

            // Process bulk images
            $bulkImagePaths = [];
            if ($request->hasFile('bulk_images')) {
                $uploadedImages = $this->imageService->uploadMultipleImages($request->file('bulk_images'), 'units');
                $bulkImagePaths = array_column($uploadedImages, 'original');
            }

            $createdUnits = [];
            $amenities = $request->input('amenities', []);

            // Tạo phòng cho từng tầng
            for ($floor = $startFloor; $floor <= $endFloor; $floor++) {
                for ($room = 1; $room <= $roomsPerFloor; $room++) {
                    $roomNumber = str_pad($floor, 2, '0', STR_PAD_LEFT) . str_pad($room, 2, '0', STR_PAD_LEFT);
                    $roomCode = $roomPrefix . $roomNumber;

                    // Kiểm tra mã phòng trùng lặp
                    $existingUnit = Unit::where('property_id', $request->bulk_property_id)
                        ->where('code', $roomCode)
                        ->first();
                    
                    if ($existingUnit) {
                        DB::rollBack();
                        return back()->withErrors(['room_prefix' => "Mã phòng {$roomCode} đã tồn tại trong bất động sản này."]);
                    }

                    $unit = Unit::create([
                        'property_id' => $request->bulk_property_id,
                        'code' => $roomCode,
                        'floor' => $floor,
                        'area_m2' => $request->bulk_area_m2,
                        'unit_type' => $request->bulk_unit_type,
                        'base_rent' => $request->bulk_base_rent,
                        'deposit_amount' => $request->bulk_deposit_amount ?? 0,
                        'max_occupancy' => $request->bulk_max_occupancy,
                        'status' => $request->bulk_status,
                        'note' => $request->bulk_note,
                        'images' => $bulkImagePaths, // Gắn ảnh cho tất cả phòng
                    ]);

                    // Sync amenities
                    if (!empty($amenities)) {
                        $unit->amenities()->sync($amenities);
                    }

                    $createdUnits[] = $unit;
                }
            }

            DB::commit();

            $successMessage = "Tạo thành công {$totalRooms} phòng cho bất động sản.";
            if (!empty($bulkImagePaths)) {
                $successMessage .= " Đã gắn " . count($bulkImagePaths) . " hình ảnh cho mỗi phòng.";
            }

            return redirect()->route('agent.units.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk unit creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng hàng loạt: ' . $e->getMessage()]);
        }
    }

    /**
     * Store multiple units in bulk with advanced floor configuration.
     */
    private function storeBulkAdvanced(Request $request, $user)
    {
        // Debug logging
        Log::info('Advanced bulk creation request data:', $request->all());
        Log::info('Floor configs:', $request->input('floor_configs', []));
        
        // Validate advanced bulk request
        $request->validate([
            'bulk_property_id' => 'required|exists:properties,id',
            'bulk_max_occupancy' => 'required|integer|min:1|max:10',
            'bulk_area_m2' => 'nullable|numeric|min:0|max:1000',
            'bulk_status' => 'required|in:available,reserved,occupied,maintenance',
            'bulk_base_rent' => 'required|numeric|min:0',
            'bulk_deposit_amount' => 'nullable|numeric|min:0',
            'bulk_note' => 'nullable|string|max:1000',
            'bulk_images' => 'nullable|array',
            'bulk_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'floor_configs' => 'required|array|min:1',
            'floor_configs.*.floor_number' => 'required|integer|min:1|max:100',
            'floor_configs.*.rooms_count' => 'required|integer|min:1|max:50',
            'floor_configs.*.room_type' => 'required|in:room,apartment,dorm,shared',
            'floor_configs.*.room_prefix' => 'nullable|string|max:10',
            'floor_configs.*.custom_room_numbers' => 'nullable|string|max:500'
        ], [
            'bulk_property_id.required' => 'Vui lòng chọn bất động sản.',
            'bulk_property_id.exists' => 'Bất động sản không tồn tại.',
            'bulk_max_occupancy.required' => 'Vui lòng nhập số người tối đa.',
            'bulk_max_occupancy.integer' => 'Số người tối đa phải là số nguyên.',
            'bulk_max_occupancy.min' => 'Số người tối đa phải lớn hơn 0.',
            'bulk_max_occupancy.max' => 'Số người tối đa không được vượt quá 10.',
            'bulk_area_m2.numeric' => 'Diện tích phải là số.',
            'bulk_area_m2.min' => 'Diện tích phải lớn hơn 0.',
            'bulk_area_m2.max' => 'Diện tích không được vượt quá 1000 m².',
            'bulk_status.required' => 'Vui lòng chọn trạng thái.',
            'bulk_status.in' => 'Trạng thái không hợp lệ.',
            'bulk_base_rent.required' => 'Vui lòng nhập giá thuê cơ bản.',
            'bulk_base_rent.numeric' => 'Giá thuê phải là số.',
            'bulk_base_rent.min' => 'Giá thuê phải lớn hơn 0.',
            'bulk_deposit_amount.numeric' => 'Tiền cọc phải là số.',
            'bulk_deposit_amount.min' => 'Tiền cọc phải lớn hơn 0.',
            'bulk_note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'floor_configs.required' => 'Vui lòng thêm ít nhất một cấu hình tầng.',
            'floor_configs.min' => 'Vui lòng thêm ít nhất một cấu hình tầng.',
            'floor_configs.*.floor_number.required' => 'Vui lòng nhập số tầng.',
            'floor_configs.*.floor_number.integer' => 'Số tầng phải là số nguyên.',
            'floor_configs.*.floor_number.min' => 'Số tầng phải lớn hơn 0.',
            'floor_configs.*.floor_number.max' => 'Số tầng không được vượt quá 100.',
            'floor_configs.*.rooms_count.required' => 'Vui lòng nhập số phòng.',
            'floor_configs.*.rooms_count.integer' => 'Số phòng phải là số nguyên.',
            'floor_configs.*.rooms_count.min' => 'Số phòng phải lớn hơn 0.',
            'floor_configs.*.rooms_count.max' => 'Số phòng không được vượt quá 50.',
            'floor_configs.*.room_type.required' => 'Vui lòng chọn loại phòng.',
            'floor_configs.*.room_type.in' => 'Loại phòng không hợp lệ.',
            'floor_configs.*.room_prefix.max' => 'Tiền tố mã phòng không được vượt quá 10 ký tự.'
        ]);

        // Kiểm tra xem property có được gán cho agent này không
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        if (!$assignedPropertyIds->contains($request->bulk_property_id)) {
            return back()->withErrors(['bulk_property_id' => 'Bạn không có quyền tạo phòng cho bất động sản này.']);
        }

        $floorConfigs = $request->input('floor_configs', []);
        $totalRooms = array_sum(array_column($floorConfigs, 'rooms_count'));
        
        // Giới hạn số phòng tối đa
        if ($totalRooms > 100) {
            return back()->withErrors(['floor_configs' => 'Số phòng tối đa cho phép là 100. Hiện tại sẽ tạo ' . $totalRooms . ' phòng.']);
        }

        try {
            DB::beginTransaction();

            // Process bulk images
            $bulkImagePaths = [];
            if ($request->hasFile('bulk_images')) {
                $uploadedImages = $this->imageService->uploadMultipleImages($request->file('bulk_images'), 'units');
                $bulkImagePaths = array_column($uploadedImages, 'original');
            }

            $createdUnits = [];
            $amenities = $request->input('amenities', []);

            // Tạo phòng theo cấu hình từng tầng
            foreach ($floorConfigs as $config) {
                $floorNumber = $config['floor_number'];
                $roomsCount = $config['rooms_count'];
                $roomType = $config['room_type'];
                $roomPrefix = $config['room_prefix'] ?? 'P';
                $customRoomNumbers = $config['custom_room_numbers'] ?? null;

                // Xác định danh sách số phòng
                $roomNumbers = [];
                if ($customRoomNumbers) {
                    // Parse custom room numbers
                    $roomNumbers = array_filter(array_map('trim', explode(',', $customRoomNumbers)));
                } else {
                    // Generate automatic room numbers
                    for ($room = 1; $room <= $roomsCount; $room++) {
                        $roomNumbers[] = $room;
                    }
                }

                // Tạo phòng cho từng số phòng
                foreach ($roomNumbers as $roomNumber) {
                    $roomCode = $roomPrefix . $roomNumber;

                    // Kiểm tra mã phòng trùng lặp
                    $existingUnit = Unit::where('property_id', $request->bulk_property_id)
                        ->where('code', $roomCode)
                        ->first();
                    
                    if ($existingUnit) {
                        DB::rollBack();
                        return back()->withErrors(['floor_configs' => "Mã phòng {$roomCode} đã tồn tại trong bất động sản này."]);
                    }

                    $unit = Unit::create([
                        'property_id' => $request->bulk_property_id,
                        'code' => $roomCode,
                        'floor' => $floorNumber,
                        'area_m2' => $request->bulk_area_m2,
                        'unit_type' => $roomType,
                        'base_rent' => $request->bulk_base_rent,
                        'deposit_amount' => $request->bulk_deposit_amount ?? 0,
                        'max_occupancy' => $request->bulk_max_occupancy,
                        'status' => $request->bulk_status,
                        'note' => $request->bulk_note,
                        'images' => $bulkImagePaths,
                    ]);

                    // Sync amenities
                    if (!empty($amenities)) {
                        $unit->amenities()->sync($amenities);
                    }

                    $createdUnits[] = $unit;
                }
            }

            DB::commit();

            $successMessage = "Tạo thành công {$totalRooms} phòng cho bất động sản với cấu hình linh hoạt.";
            if (!empty($bulkImagePaths)) {
                $successMessage .= " Đã gắn " . count($bulkImagePaths) . " hình ảnh cho mỗi phòng.";
            }

            return redirect()->route('agent.units.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advanced bulk unit creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo phòng hàng loạt: ' . $e->getMessage()]);
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
            ->with(['property', 'amenities', 'leases' => function($q) {
                $q->where('status', 'active')->whereNull('deleted_at')->with('tenant');
            }])
            ->findOrFail($id);

        $unit->is_rented = $unit->leases->count() > 0;
        $unit->current_lease = $unit->leases->first();

        // Get meters for this unit with their readings
        $meters = \App\Models\Meter::where('unit_id', $unit->id)
            ->with([
                'service',
                'readings' => function($query) {
                    $query->orderBy('reading_date', 'desc');
                }
            ])
            ->get();

        return view('agent.units.show', compact('unit', 'meters'));
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
            ->with(['property', 'amenities'])
            ->findOrFail($id);

        // Lấy danh sách properties được gán
        $properties = $user->assignedProperties()
            ->where('properties.status', 1)
            ->orderBy('name')
            ->get();

        // Load amenities
        $amenities = \App\Models\Amenity::orderBy('category')->orderBy('name')->get();

        return view('agent.units.edit', [
            'unit' => $unit,
            'properties' => $properties,
            'amenities' => $amenities
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
            'note' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id'
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

            // Process images
            $imagePaths = $unit->images ?? [];
            
            // Delete marked images
            if ($request->has('deleted_images')) {
                $this->imageService->deleteMultipleImages($request->deleted_images);
                $imagePaths = array_diff($imagePaths, $request->deleted_images);
            }
            
            // Upload new images
            if ($request->hasFile('images')) {
                $uploadedImages = $this->imageService->uploadMultipleImages($request->file('images'), 'units');
                $newImagePaths = array_column($uploadedImages, 'original');
                $imagePaths = array_merge($imagePaths, $newImagePaths);
            }

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
                'images' => $imagePaths,
            ]);

            // Sync amenities
            if ($request->has('amenities')) {
                $unit->amenities()->sync($request->amenities);
            } else {
                $unit->amenities()->detach();
            }

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