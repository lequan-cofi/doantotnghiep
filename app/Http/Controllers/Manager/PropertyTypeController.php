<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PropertyType::withCount(['properties' => function($q) {
            // Temporarily disable organization scope for properties count
            $q->withoutGlobalScope('organization');
        }]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key_code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $propertyTypes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('manager.property-types.index', compact('propertyTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manager.property-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_code' => 'required|string|max:100|unique:property_types,key_code',
            'name' => 'required|string|max:150',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $propertyType = PropertyType::create([
                'key_code' => $validated['key_code'],
                'name' => $validated['name'],
                'icon' => $validated['icon'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loại bất động sản đã được tạo thành công!',
                'property_type_id' => $propertyType->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $propertyType = PropertyType::withCount('properties')->findOrFail($id);
        return view('manager.property-types.show', compact('propertyType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $propertyType = PropertyType::findOrFail($id);
        return view('manager.property-types.edit', compact('propertyType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $propertyType = PropertyType::findOrFail($id);

        $validated = $request->validate([
            'key_code' => 'required|string|max:100|unique:property_types,key_code,' . $id,
            'name' => 'required|string|max:150',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $propertyType->update([
                'key_code' => $validated['key_code'],
                'name' => $validated['name'],
                'icon' => $validated['icon'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loại bất động sản đã được cập nhật thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $propertyType = PropertyType::findOrFail($id);
            
            // Check if property type is being used
            $propertiesCount = $propertyType->properties()->count();
            if ($propertiesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa loại bất động sản này vì đang được sử dụng bởi {$propertiesCount} bất động sản."
                ], 400);
            }

            $propertyType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Loại bất động sản đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        try {
            $propertyType = PropertyType::onlyTrashed()->findOrFail($id);
            $propertyType->restore();

            return response()->json([
                'success' => true,
                'message' => 'Loại bất động sản đã được khôi phục thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    /**
     * Force delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        try {
            $propertyType = PropertyType::withTrashed()->findOrFail($id);
            $propertyType->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Loại bất động sản đã được xóa vĩnh viễn!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage() . '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.'
            ], 500);
        }
    }

    /**
     * Get property types for API/Select options
     */
    public function getOptions()
    {
        $propertyTypes = PropertyType::where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'key_code']);

        return response()->json($propertyTypes);
    }
}
