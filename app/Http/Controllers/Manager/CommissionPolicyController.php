<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CommissionPolicy;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionPolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = CommissionPolicy::with(['organization'])
            ->withCount('events')
            ->where('organization_id', Auth::user()->organizations()->first()?->id);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('trigger_event')) {
            $query->where('trigger_event', $request->trigger_event);
        }

        if ($request->filled('calc_type')) {
            $query->where('calc_type', $request->calc_type);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        $policies = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('manager.commission-policies.index', compact('policies'));
    }

    public function create()
    {
        $triggerEvents = [
            'deposit_paid' => 'Thanh toán cọc',
            'lease_signed' => 'Ký hợp đồng',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'viewing_done' => 'Hoàn thành xem phòng',
            'listing_published' => 'Đăng tin'
        ];

        $calcTypes = [
            'percent' => 'Phần trăm',
            'flat' => 'Số tiền cố định',
            'tiered' => 'Bậc thang'
        ];

        $basisTypes = [
            'cash' => 'Tiền mặt',
            'accrual' => 'Dồn tích'
        ];

        return view('manager.commission-policies.create', compact('triggerEvents', 'calcTypes', 'basisTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:commission_policies,code',
            'title' => 'required|string|max:150',
            'trigger_event' => 'required|in:deposit_paid,lease_signed,invoice_paid,viewing_done,listing_published',
            'basis' => 'required|in:cash,accrual',
            'calc_type' => 'required|in:percent,flat,tiered',
            'percent_value' => 'nullable|numeric|min:0|max:100',
            'flat_amount' => 'nullable|numeric|min:0',
            'apply_limit_months' => 'nullable|integer|min:1|max:12',
            'min_amount' => 'nullable|numeric|min:0',
            'cap_amount' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $organization = Auth::user()->organizations()->first();
            
            $policy = CommissionPolicy::create([
                'organization_id' => $organization->id,
                'code' => $request->code,
                'title' => $request->title,
                'trigger_event' => $request->trigger_event,
                'basis' => $request->basis,
                'calc_type' => $request->calc_type,
                'percent_value' => $request->percent_value,
                'flat_amount' => $request->flat_amount,
                'apply_limit_months' => $request->apply_limit_months,
                'min_amount' => $request->min_amount,
                'cap_amount' => $request->cap_amount,
                'active' => $request->boolean('active', true),
            ]);


            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chính sách hoa hồng đã được tạo thành công!',
                    'data' => $policy
                ]);
            }

            return redirect()->route('manager.commission-policies.index')
                ->with('success', 'Chính sách hoa hồng đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating commission policy: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo chính sách hoa hồng'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo chính sách hoa hồng');
        }
    }

    public function show(CommissionPolicy $commissionPolicy)
    {
        // Check if user belongs to the same organization
        if ($commissionPolicy->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission policy.');
        }
        
        $commissionPolicy->load(['organization', 'events.agent', 'events.lease', 'events.unit']);

        return view('manager.commission-policies.show', compact('commissionPolicy'));
    }

    public function edit(CommissionPolicy $commissionPolicy)
    {
        // Check if user belongs to the same organization
        if ($commissionPolicy->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission policy.');
        }

        $triggerEvents = [
            'deposit_paid' => 'Thanh toán cọc',
            'lease_signed' => 'Ký hợp đồng',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'viewing_done' => 'Hoàn thành xem phòng',
            'listing_published' => 'Đăng tin'
        ];

        $calcTypes = [
            'percent' => 'Phần trăm',
            'flat' => 'Số tiền cố định',
            'tiered' => 'Bậc thang'
        ];

        $basisTypes = [
            'cash' => 'Tiền mặt',
            'accrual' => 'Dồn tích'
        ];


        return view('manager.commission-policies.edit', compact('commissionPolicy', 'triggerEvents', 'calcTypes', 'basisTypes'));
    }

    public function update(Request $request, CommissionPolicy $commissionPolicy)
    {
        // Check if user belongs to the same organization
        if ($commissionPolicy->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission policy.');
        }

        $request->validate([
            'code' => 'required|string|max:50|unique:commission_policies,code,' . $commissionPolicy->id,
            'title' => 'required|string|max:150',
            'trigger_event' => 'required|in:deposit_paid,lease_signed,invoice_paid,viewing_done,listing_published',
            'basis' => 'required|in:cash,accrual',
            'calc_type' => 'required|in:percent,flat,tiered',
            'percent_value' => 'nullable|numeric|min:0|max:100',
            'flat_amount' => 'nullable|numeric|min:0',
            'apply_limit_months' => 'nullable|integer|min:1|max:12',
            'min_amount' => 'nullable|numeric|min:0',
            'cap_amount' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $commissionPolicy->update([
                'code' => $request->code,
                'title' => $request->title,
                'trigger_event' => $request->trigger_event,
                'basis' => $request->basis,
                'calc_type' => $request->calc_type,
                'percent_value' => $request->percent_value,
                'flat_amount' => $request->flat_amount,
                'apply_limit_months' => $request->apply_limit_months,
                'min_amount' => $request->min_amount,
                'cap_amount' => $request->cap_amount,
                'active' => $request->boolean('active', true),
            ]);


            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chính sách hoa hồng đã được cập nhật thành công!',
                    'data' => $commissionPolicy
                ]);
            }

            return redirect()->route('manager.commission-policies.index')
                ->with('success', 'Chính sách hoa hồng đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating commission policy: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật chính sách hoa hồng'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật chính sách hoa hồng');
        }
    }

    public function destroy(CommissionPolicy $commissionPolicy)
    {
        // Check if user belongs to the same organization
        if ($commissionPolicy->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to commission policy.');
        }

        try {
            $commissionPolicy->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chính sách hoa hồng đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.commission-policies.index')
                ->with('success', 'Chính sách hoa hồng đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting commission policy: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa chính sách hoa hồng'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa chính sách hoa hồng');
        }
    }
}
