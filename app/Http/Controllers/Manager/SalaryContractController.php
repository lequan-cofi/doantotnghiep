<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\SalaryContract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryContractController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organizations()->first()?->id;
        
        $query = SalaryContract::where('organization_id', $organizationId)
            ->with(['user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('effective_from', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('effective_from', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $salaryContracts = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get users for filter dropdown
        $users = User::whereHas('organizations', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->get();

        $statuses = [
            'active' => 'Đang hoạt động',
            'inactive' => 'Tạm dừng',
            'terminated' => 'Đã chấm dứt'
        ];

        return view('manager.salary-contracts.index', compact('salaryContracts', 'users', 'statuses'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organizations()->first()?->id;
        
        $users = User::whereHas('organizations', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->get();

        $payCycles = [
            'monthly' => 'Hàng tháng',
            'weekly' => 'Hàng tuần',
            'daily' => 'Hàng ngày'
        ];

        $statuses = [
            'active' => 'Đang hoạt động',
            'inactive' => 'Tạm dừng'
        ];

        return view('manager.salary-contracts.create', compact('users', 'payCycles', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'pay_cycle' => 'required|in:monthly,weekly,daily',
            'pay_day' => 'required|integer|min:1|max:31',
            'allowances_json' => 'nullable|string',
            'kpi_target_json' => 'nullable|string',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            DB::beginTransaction();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $organizationId = $user->organizations()->first()?->id;

            // Check if user already has an active contract
            $existingContract = SalaryContract::where('user_id', $request->user_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where(function($q) use ($request) {
                    $q->where('effective_from', '<=', $request->effective_from)
                      ->where(function($q2) use ($request) {
                          $q2->whereNull('effective_to')
                             ->orWhere('effective_to', '>=', $request->effective_from);
                      });
                })
                ->first();

            if ($existingContract) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nhân viên này đã có hợp đồng lương đang hoạt động trong khoảng thời gian này'
                    ], 400);
                }
                return back()->withInput()->with('error', 'Nhân viên này đã có hợp đồng lương đang hoạt động trong khoảng thời gian này');
            }

            // Parse JSON data
            $allowances = [];
            if ($request->allowances_json) {
                $allowances = json_decode($request->allowances_json, true) ?: [];
            }
            
            $kpiTargets = [];
            if ($request->kpi_target_json) {
                $kpiTargets = json_decode($request->kpi_target_json, true) ?: [];
            }

            $salaryContract = SalaryContract::create([
                'organization_id' => $organizationId,
                'user_id' => $request->user_id,
                'base_salary' => $request->base_salary,
                'currency' => $request->currency,
                'pay_cycle' => $request->pay_cycle,
                'pay_day' => $request->pay_day,
                'allowances_json' => $allowances,
                'kpi_target_json' => $kpiTargets,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng lương đã được tạo thành công!',
                    'data' => $salaryContract
                ]);
            }

            return redirect()->route('manager.salary-contracts.index')
                ->with('success', 'Hợp đồng lương đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating salary contract: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo hợp đồng lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hợp đồng lương');
        }
    }

    public function show(SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        $salaryContract->load(['user']);

        return view('manager.salary-contracts.show', compact('salaryContract'));
    }

    public function edit(SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        // Only allow editing if status is 'active' or 'inactive'
        if ($salaryContract->status === 'terminated') {
            return back()->with('error', 'Không thể chỉnh sửa hợp đồng lương đã chấm dứt.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organizations()->first()?->id;
        
        $users = User::whereHas('organizations', function($q) use ($organizationId) {
            $q->where('organization_id', $organizationId);
        })->get();

        $payCycles = [
            'monthly' => 'Hàng tháng',
            'weekly' => 'Hàng tuần',
            'daily' => 'Hàng ngày'
        ];

        $statuses = [
            'active' => 'Đang hoạt động',
            'inactive' => 'Tạm dừng'
        ];

        return view('manager.salary-contracts.edit', compact('salaryContract', 'users', 'payCycles', 'statuses'));
    }

    public function update(Request $request, SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        // Only allow editing if status is 'active' or 'inactive'
        if ($salaryContract->status === 'terminated') {
            return back()->with('error', 'Không thể chỉnh sửa hợp đồng lương đã chấm dứt.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'pay_cycle' => 'required|in:monthly,weekly,daily',
            'pay_day' => 'required|integer|min:1|max:31',
            'allowances_json' => 'nullable|string',
            'kpi_target_json' => 'nullable|string',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            DB::beginTransaction();

            // Check if user already has an active contract (excluding current one)
            $existingContract = SalaryContract::where('user_id', $request->user_id)
                ->where('organization_id', $salaryContract->organization_id)
                ->where('id', '!=', $salaryContract->id)
                ->where('status', 'active')
                ->where(function($q) use ($request) {
                    $q->where('effective_from', '<=', $request->effective_from)
                      ->where(function($q2) use ($request) {
                          $q2->whereNull('effective_to')
                             ->orWhere('effective_to', '>=', $request->effective_from);
                      });
                })
                ->first();

            if ($existingContract) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nhân viên này đã có hợp đồng lương đang hoạt động trong khoảng thời gian này'
                    ], 400);
                }
                return back()->withInput()->with('error', 'Nhân viên này đã có hợp đồng lương đang hoạt động trong khoảng thời gian này');
            }

            // Parse JSON data
            $allowances = [];
            if ($request->allowances_json) {
                $allowances = json_decode($request->allowances_json, true) ?: [];
            }
            
            $kpiTargets = [];
            if ($request->kpi_target_json) {
                $kpiTargets = json_decode($request->kpi_target_json, true) ?: [];
            }

            $salaryContract->update([
                'user_id' => $request->user_id,
                'base_salary' => $request->base_salary,
                'currency' => $request->currency,
                'pay_cycle' => $request->pay_cycle,
                'pay_day' => $request->pay_day,
                'allowances_json' => $allowances,
                'kpi_target_json' => $kpiTargets,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng lương đã được cập nhật thành công!',
                    'data' => $salaryContract
                ]);
            }

            return redirect()->route('manager.salary-contracts.index')
                ->with('success', 'Hợp đồng lương đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating salary contract: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật hợp đồng lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật hợp đồng lương');
        }
    }

    public function destroy(SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        // Only allow deleting if status is 'inactive'
        if ($salaryContract->status === 'active') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa hợp đồng lương đang hoạt động. Hãy tạm dừng trước.'
                ], 400);
            }
            return back()->with('error', 'Không thể xóa hợp đồng lương đang hoạt động. Hãy tạm dừng trước.');
        }

        try {
            $salaryContract->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng lương đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.salary-contracts.index')
                ->with('success', 'Hợp đồng lương đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting salary contract: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa hợp đồng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa hợp đồng lương');
        }
    }

    public function terminate(SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        if ($salaryContract->status === 'terminated') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hợp đồng lương đã được chấm dứt trước đó'
                ], 400);
            }
            return back()->with('error', 'Hợp đồng lương đã được chấm dứt trước đó');
        }

        try {
            $salaryContract->update([
                'status' => 'terminated',
                'effective_to' => now()->toDateString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng lương đã được chấm dứt thành công!'
                ]);
            }

            return back()->with('success', 'Hợp đồng lương đã được chấm dứt thành công!');

        } catch (\Exception $e) {
            Log::error('Error terminating salary contract: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi chấm dứt hợp đồng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi chấm dứt hợp đồng lương');
        }
    }

    public function activate(SalaryContract $salaryContract)
    {
        // Check if user belongs to the same organization
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($salaryContract->organization_id !== $user->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary contract.');
        }

        if ($salaryContract->status === 'active') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hợp đồng lương đã đang hoạt động'
                ], 400);
            }
            return back()->with('error', 'Hợp đồng lương đã đang hoạt động');
        }

        if ($salaryContract->status === 'terminated') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể kích hoạt hợp đồng lương đã chấm dứt'
                ], 400);
            }
            return back()->with('error', 'Không thể kích hoạt hợp đồng lương đã chấm dứt');
        }

        try {
            $salaryContract->update([
                'status' => 'active'
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hợp đồng lương đã được kích hoạt thành công!'
                ]);
            }

            return back()->with('success', 'Hợp đồng lương đã được kích hoạt thành công!');

        } catch (\Exception $e) {
            Log::error('Error activating salary contract: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi kích hoạt hợp đồng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi kích hoạt hợp đồng lương');
        }
    }
}