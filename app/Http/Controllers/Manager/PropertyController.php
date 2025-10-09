<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use App\Models\Location2025;
use App\Models\User;
use App\Models\GeoProvince;
use App\Models\GeoDistrict;
use App\Models\GeoProvince2025;
use App\Models\GeoWard2025;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        // Start with basic query - temporarily disable organization scope for debugging
        $query = Property::withoutGlobalScope('organization')->with(['propertyType', 'location', 'location2025', 'owner']);

        // // Debug: Log request parameters
        // Log::info('Request parameters: ' . json_encode($request->all()));

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('location', function($locationQuery) use ($search) {
                      $locationQuery->where('street', 'like', "%{$search}%")
                                   ->orWhere('district', 'like', "%{$search}%")
                                   ->orWhere('ward', 'like', "%{$search}%")
                                   ->orWhere('city', 'like', "%{$search}%");
                  })
                  ->orWhereHas('location2025', function($locationQuery) use ($search) {
                      $locationQuery->where('street', 'like', "%{$search}%")
                                   ->orWhere('district', 'like', "%{$search}%")
                                   ->orWhere('ward', 'like', "%{$search}%")
                                   ->orWhere('city', 'like', "%{$search}%");
                  })
                  ->orWhereHas('owner', function($ownerQuery) use ($search) {
                      $ownerQuery->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by property type
        if ($request->filled('type')) {
            $query->where('property_type_id', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by owner
        if ($request->filled('owner')) {
            $query->where('owner_id', $request->owner);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by location (old system)
        if ($request->filled('province')) {
            $query->whereHas('location', function($locationQuery) use ($request) {
                $locationQuery->where('province_code', $request->province);
            });
        }
        if ($request->filled('district')) {
            $query->whereHas('location', function($locationQuery) use ($request) {
                $locationQuery->where('district_code', $request->district);
            });
        }

        // Filter by location (new system 2025)
        if ($request->filled('province_2025')) {
            $query->whereHas('location2025', function($locationQuery) use ($request) {
                $locationQuery->where('province_code', $request->province_2025);
            });
        }
        if ($request->filled('ward_2025')) {
            $query->whereHas('location2025', function($locationQuery) use ($request) {
                $locationQuery->where('ward_code', $request->ward_2025);
            });
        }

        // // Debug: Log the SQL query
        // Log::info('Properties Query SQL: ' . $query->toSql());
        // Log::info('Properties Query Bindings: ' . json_encode($query->getBindings()));
        
        // // Debug: Count before pagination
        // $totalCount = $query->count();
        // Log::info('Total count before pagination: ' . $totalCount);
        
        $properties = $query->orderBy('created_at', 'desc')->paginate(10);
        $propertyTypes = PropertyType::all();
        $owners = User::whereHas('userRoles', function($query) {
            $query->where('key_code', 'landlord');
        })->get();
        
        // Get geo data for location filters
        $provinces = \App\Models\GeoProvince::all();
        $districts = \App\Models\GeoDistrict::all();
        $provinces2025 = \App\Models\GeoProvince2025::all();
        
        // Get wards2025 based on selected province_2025
        $wards2025 = collect();
        if ($request->filled('province_2025')) {
            $wards2025 = \App\Models\GeoWard2025::where('province_code', $request->province_2025)->get();
        }

        return view('manager.properties.index', compact('properties', 'propertyTypes', 'owners', 'provinces', 'districts', 'provinces2025', 'wards2025'));
    }

    public function create()
    {
        $propertyTypes = PropertyType::all();
        $owners = User::whereHas('userRoles', function($query) {
            $query->where('key_code', 'landlord');
        })->get();

        // Get both old and new geo data
        $provinces = GeoProvince::where('country_code', 'VN')->get();
        $provinces2025 = GeoProvince2025::where('country_code', 'VN')->get();

        return view('manager.properties.create', compact('propertyTypes', 'owners', 'provinces', 'provinces2025'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'property_type_id' => 'nullable|exists:property_types,id',
                'owner_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string',
                'total_floors' => 'nullable|integer|min:1',
                'total_rooms' => 'nullable|integer|min:0',
                'status' => 'nullable|integer|in:0,1',
                // Old location fields
                'province_code' => 'nullable|string|max:20|exists:geo_provinces,code',
                'district_code' => 'nullable|string|max:20|exists:geo_districts,code',
                'ward_code' => 'nullable|string|max:20|exists:geo_wards,code',
                'street' => 'nullable|string|max:255',
                // New location fields
                'province_code_2025' => 'nullable|string|max:20|exists:geo_provinces_2025,code',
                'ward_code_2025' => 'nullable|string|max:20|exists:geo_wards_2025,code',
                'street_2025' => 'nullable|string|max:255',
            ]);

            // Additional validation for geo relationships
            if ($request->filled('district_code') && $request->filled('province_code')) {
                $district = DB::table('geo_districts')
                    ->where('code', $request->district_code)
                    ->where('province_code', $request->province_code)
                    ->first();
                
                if (!$district) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quận/Huyện được chọn không thuộc Tỉnh/Thành phố đã chọn. Vui lòng kiểm tra lại thông tin địa chỉ.'
                    ], 422);
                }
            }

            if ($request->filled('ward_code') && $request->filled('district_code')) {
                $ward = DB::table('geo_wards')
                    ->where('code', $request->ward_code)
                    ->where('district_code', $request->district_code)
                    ->first();
                
                if (!$ward) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phường/Xã được chọn không thuộc Quận/Huyện đã chọn. Vui lòng kiểm tra lại thông tin địa chỉ.'
                    ], 422);
                }
            }

            DB::beginTransaction();
            try {
                // Create old location if provided
                $locationId = null;
                if ($request->filled('province_code')) {
                    // Get names from geo tables
                    $province = DB::table('geo_provinces')->where('code', $request->province_code)->first();
                    $district = $request->district_code ? DB::table('geo_districts')->where('code', $request->district_code)->first() : null;
                    $ward = $request->ward_code ? DB::table('geo_wards')->where('code', $request->ward_code)->first() : null;
                    $country = DB::table('geo_countries')->where('code', 'VN')->first();

                    $location = Location::create([
                        'country_code' => 'VN',
                        'province_code' => $request->province_code,
                        'district_code' => $request->district_code,
                        'ward_code' => $request->ward_code,
                        'street' => $request->street,
                        // Store names for quick access
                        'country' => $country->name ?? 'Việt Nam',
                        'city' => $province->name ?? null,
                        'district' => $district->name ?? null,
                        'ward' => $ward->name ?? null,
                    ]);
                    $locationId = $location->id;
                }

                // Create new location if provided
                $locationId2025 = null;
                if ($request->filled('province_code_2025')) {
                    // Get names from geo tables
                    $province2025 = DB::table('geo_provinces_2025')->where('code', $request->province_code_2025)->first();
                    $ward2025 = $request->ward_code_2025 ? DB::table('geo_wards_2025')->where('code', $request->ward_code_2025)->first() : null;
                    $country = DB::table('geo_countries')->where('code', 'VN')->first();

                    $location2025 = Location2025::create([
                        'country_code' => 'VN',
                        'province_code' => $request->province_code_2025,
                        'ward_code' => $request->ward_code_2025,
                        'street' => $request->street_2025,
                        // Store names for quick access
                        'country' => $country->name ?? 'Việt Nam',
                        'city' => $province2025->name ?? null,
                        'ward' => $ward2025->name ?? null,
                    ]);
                    $locationId2025 = $location2025->id;
                }

                $property = Property::create([
                    'name' => $validated['name'],
                    'property_type_id' => $validated['property_type_id'] ?? null,
                    'owner_id' => $validated['owner_id'] ?? null,
                    'location_id' => $locationId,
                    'location_id_2025' => $locationId2025,
                    'description' => $validated['description'] ?? null,
                    'total_floors' => $validated['total_floors'] ?? null,
                    'total_rooms' => $validated['total_rooms'] ?? 0,
                    'status' => $validated['status'] ?? 1,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bất động sản đã được tạo thành công!',
                    'property_id' => $property->id
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin bất động sản không hợp lệ: ' . implode(', ', $e->validator->errors()->all()) . '. Vui lòng kiểm tra lại và thử lại.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    public function show($id)
    {
        $property = Property::with([
            'propertyType', 
            'location',
            'location2025',
            'owner', 
            'units'
        ])->findOrFail($id);
        
        return view('manager.properties.show', compact('property'));
    }

    public function edit($id)
    {
        $property = Property::with(['location', 'location2025', 'propertyType', 'owner'])->findOrFail($id);
        $propertyTypes = PropertyType::all();

        $owners = User::whereHas('userRoles', function($query) {
            $query->where('key_code', 'landlord');
        })->get();

        // Get both old and new geo data
        $provinces = GeoProvince::where('country_code', 'VN')->get();
        $provinces2025 = GeoProvince2025::where('country_code', 'VN')->get();
        
        $districts = [];
        $wards = [];
        $wards2025 = [];

        if ($property->location) {
            if ($property->location->province_code) {
                $districts = DB::table('geo_districts')
                    ->where('province_code', $property->location->province_code)
                    ->get();
            }
            if ($property->location->district_code) {
                $wards = DB::table('geo_wards')
                    ->where('district_code', $property->location->district_code)
                    ->get();
            }
        }

        if ($property->location2025) {
            if ($property->location2025->province_code) {
                $wards2025 = DB::table('geo_wards_2025')
                    ->where('province_code', $property->location2025->province_code)
                    ->get();
            }
        }

        return view('manager.properties.edit', compact('property', 'propertyTypes', 'owners', 'provinces', 'provinces2025', 'districts', 'wards', 'wards2025'));
    }

    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'property_type_id' => 'nullable|exists:property_types,id',
                'owner_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string',
                'total_floors' => 'nullable|integer|min:1',
                'total_rooms' => 'nullable|integer|min:0',
                'status' => 'nullable|integer|in:0,1',
                // Old location fields
                'province_code' => 'nullable|string|max:20|exists:geo_provinces,code',
                'district_code' => 'nullable|string|max:20|exists:geo_districts,code',
                'ward_code' => 'nullable|string|max:20|exists:geo_wards,code',
                'street' => 'nullable|string|max:255',
                // New location fields
                'province_code_2025' => 'nullable|string|max:20|exists:geo_provinces_2025,code',
                'ward_code_2025' => 'nullable|string|max:20|exists:geo_wards_2025,code',
                'street_2025' => 'nullable|string|max:255',
            ]);

            // Additional validation for geo relationships
            if ($request->filled('district_code') && $request->filled('province_code')) {
                $district = DB::table('geo_districts')
                    ->where('code', $request->district_code)
                    ->where('province_code', $request->province_code)
                    ->first();
                
                if (!$district) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quận/Huyện được chọn không thuộc Tỉnh/Thành phố đã chọn. Vui lòng kiểm tra lại thông tin địa chỉ.'
                    ], 422);
                }
            }

            if ($request->filled('ward_code') && $request->filled('district_code')) {
                $ward = DB::table('geo_wards')
                    ->where('code', $request->ward_code)
                    ->where('district_code', $request->district_code)
                    ->first();
                
                if (!$ward) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phường/Xã được chọn không thuộc Quận/Huyện đã chọn. Vui lòng kiểm tra lại thông tin địa chỉ.'
                    ], 422);
                }
            }

            DB::beginTransaction();
            try {
                // Update or create old location
                if ($request->filled('province_code')) {
                    // Get names from geo tables
                    $province = DB::table('geo_provinces')->where('code', $request->province_code)->first();
                    $district = $request->district_code ? DB::table('geo_districts')->where('code', $request->district_code)->first() : null;
                    $ward = $request->ward_code ? DB::table('geo_wards')->where('code', $request->ward_code)->first() : null;
                    $country = DB::table('geo_countries')->where('code', 'VN')->first();

                    if ($property->location_id) {
                        $property->location->update([
                            'province_code' => $request->province_code,
                            'district_code' => $request->district_code,
                            'ward_code' => $request->ward_code,
                            'street' => $request->street,
                            // Update names for quick access
                            'country' => $country->name ?? 'Việt Nam',
                            'city' => $province->name ?? null,
                            'district' => $district->name ?? null,
                            'ward' => $ward->name ?? null,
                        ]);
                    } else {
                        $location = Location::create([
                            'country_code' => 'VN',
                            'province_code' => $request->province_code,
                            'district_code' => $request->district_code,
                            'ward_code' => $request->ward_code,
                            'street' => $request->street,
                            // Store names for quick access
                            'country' => $country->name ?? 'Việt Nam',
                            'city' => $province->name ?? null,
                            'district' => $district->name ?? null,
                            'ward' => $ward->name ?? null,
                        ]);
                        $property->location_id = $location->id;
                    }
                }

                // Update or create new location
                if ($request->filled('province_code_2025')) {
                    // Get names from geo tables
                    $province2025 = DB::table('geo_provinces_2025')->where('code', $request->province_code_2025)->first();
                    $ward2025 = $request->ward_code_2025 ? DB::table('geo_wards_2025')->where('code', $request->ward_code_2025)->first() : null;
                    $country = DB::table('geo_countries')->where('code', 'VN')->first();

                    if ($property->location_id_2025) {
                        $property->location2025->update([
                            'province_code' => $request->province_code_2025,
                            'ward_code' => $request->ward_code_2025,
                            'street' => $request->street_2025,
                            // Update names for quick access
                            'country' => $country->name ?? 'Việt Nam',
                            'city' => $province2025->name ?? null,
                            'ward' => $ward2025->name ?? null,
                        ]);
                    } else {
                        $location2025 = Location2025::create([
                            'country_code' => 'VN',
                            'province_code' => $request->province_code_2025,
                            'ward_code' => $request->ward_code_2025,
                            'street' => $request->street_2025,
                            // Store names for quick access
                            'country' => $country->name ?? 'Việt Nam',
                            'city' => $province2025->name ?? null,
                            'ward' => $ward2025->name ?? null,
                        ]);
                        $property->location_id_2025 = $location2025->id;
                    }
                }

                $property->update([
                    'name' => $validated['name'],
                    'property_type_id' => $validated['property_type_id'] ?? null,
                    'owner_id' => $validated['owner_id'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'total_floors' => $validated['total_floors'] ?? null,
                    'total_rooms' => $validated['total_rooms'] ?? 0,
                    'status' => $validated['status'] ?? 1,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bất động sản đã được cập nhật thành công!'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin bất động sản không hợp lệ: ' . implode(', ', $e->validator->errors()->all()) . '. Vui lòng kiểm tra lại và thử lại.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $property = Property::findOrFail($id);
            
            // Delete associated locations if exist
            if ($property->location_id) {
                $property->location->delete();
            }
            
            if ($property->location_id_2025) {
                $property->location2025->delete();
            }
            
            // Delete the property
            $property->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bất động sản và địa chỉ đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // API helpers for cascading dropdowns
    public function getDistricts($provinceCode)
    {
        $districts = DB::table('geo_districts')
            ->where('province_code', $provinceCode)
            ->get();

        return response()->json($districts);
    }

    public function getWards($districtCode)
    {
        $wards = DB::table('geo_wards')
            ->where('district_code', $districtCode)
            ->get();

        return response()->json($wards);
    }

    public function getWards2025($provinceCode)
    {
        $wards = DB::table('geo_wards_2025')
            ->where('province_code', $provinceCode)
            ->get();

        return response()->json($wards);
    }
}