<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\SalaryAdvance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $organization = Auth::user()->organizations()->first();
        
        $query = SalaryAdvance::where('organization_id', $organization->id)
            ->with(['user', 'approver', 'rejector']);

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
            $query->where('advance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('advance_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $salaryAdvances = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get users for filter dropdown
        $users = User::whereHas('organizations', function($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        })->get();

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            'repaid' => 'Đã hoàn trả',
            'partially_repaid' => 'Hoàn trả một phần'
        ];

        return view('manager.salary-advances.index', compact('salaryAdvances', 'users', 'statuses'));
    }

    public function create()
    {
        $organization = Auth::user()->organizations()->first();
        
        $users = User::whereHas('organizations', function($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        })->get();

        $repaymentMethods = [
            'payroll_deduction' => 'Trừ lương',
            'direct_payment' => 'Thanh toán trực tiếp',
            'installment' => 'Trả góp'
        ];

        return view('manager.salary-advances.create', compact('users', 'repaymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'advance_date' => 'required|date',
            'expected_repayment_date' => 'required|date|after:advance_date',
            'reason' => 'required|string|max:1000',
            'repayment_method' => 'required|in:payroll_deduction,direct_payment,installment',
            'installment_months' => 'nullable|integer|min:1|max:12',
            'monthly_deduction' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $organization = Auth::user()->organizations()->first();

            $salaryAdvance = SalaryAdvance::create([
                'organization_id' => $organization->id,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'advance_date' => $request->advance_date,
                'expected_repayment_date' => $request->expected_repayment_date,
                'reason' => $request->reason,
                'status' => 'pending',
                'repaid_amount' => 0,
                'remaining_amount' => $request->amount,
                'repayment_method' => $request->repayment_method,
                'installment_months' => $request->installment_months,
                'monthly_deduction' => $request->monthly_deduction,
                'note' => $request->note,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được tạo thành công!',
                    'data' => $salaryAdvance
                ]);
            }

            return redirect()->route('manager.salary-advances.index')
                ->with('success', 'Đơn ứng lương đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating salary advance: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo đơn ứng lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo đơn ứng lương');
        }
    }

    public function show(SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        $salaryAdvance->load(['user', 'approver', 'rejector']);

        return view('manager.salary-advances.show', compact('salaryAdvance'));
    }

    public function edit(SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Only allow editing pending advances
        if (!$salaryAdvance->canBeDeleted()) {
            return back()->with('error', 'Chỉ có thể chỉnh sửa đơn ứng lương đang chờ duyệt hoặc đã từ chối.');
        }

        $organization = Auth::user()->organizations()->first();
        
        $users = User::whereHas('organizations', function($q) use ($organization) {
            $q->where('organization_id', $organization->id);
        })->get();

        $repaymentMethods = [
            'payroll_deduction' => 'Trừ lương',
            'direct_payment' => 'Thanh toán trực tiếp',
            'installment' => 'Trả góp'
        ];

        return view('manager.salary-advances.edit', compact('salaryAdvance', 'users', 'repaymentMethods'));
    }

    public function update(Request $request, SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Only allow editing pending advances
        if (!$salaryAdvance->canBeDeleted()) {
            return back()->with('error', 'Chỉ có thể chỉnh sửa đơn ứng lương đang chờ duyệt hoặc đã từ chối.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'advance_date' => 'required|date',
            'expected_repayment_date' => 'required|date|after:advance_date',
            'reason' => 'required|string|max:1000',
            'repayment_method' => 'required|in:payroll_deduction,direct_payment,installment',
            'installment_months' => 'nullable|integer|min:1|max:12',
            'monthly_deduction' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $salaryAdvance->update([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'advance_date' => $request->advance_date,
                'expected_repayment_date' => $request->expected_repayment_date,
                'reason' => $request->reason,
                'remaining_amount' => $request->amount, // Reset remaining amount
                'repayment_method' => $request->repayment_method,
                'installment_months' => $request->installment_months,
                'monthly_deduction' => $request->monthly_deduction,
                'note' => $request->note,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được cập nhật thành công!',
                    'data' => $salaryAdvance
                ]);
            }

            return redirect()->route('manager.salary-advances.index')
                ->with('success', 'Đơn ứng lương đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating salary advance: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật đơn ứng lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật đơn ứng lương');
        }
    }

    public function destroy(SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        // Only allow deleting pending or rejected advances
        if (!$salaryAdvance->canBeDeleted()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa đơn ứng lương đang chờ duyệt hoặc đã từ chối'
                ], 400);
            }
            return back()->with('error', 'Chỉ có thể xóa đơn ứng lương đang chờ duyệt hoặc đã từ chối');
        }

        try {
            $salaryAdvance->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.salary-advances.index')
                ->with('success', 'Đơn ứng lương đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting salary advance: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa đơn ứng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa đơn ứng lương');
        }
    }

    public function approve(SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        if (!$salaryAdvance->canBeApproved()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể duyệt đơn ứng lương này'
                ], 400);
            }
            return back()->with('error', 'Không thể duyệt đơn ứng lương này');
        }

        try {
            $salaryAdvance->approve(Auth::id());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được duyệt thành công!'
                ]);
            }

            return back()->with('success', 'Đơn ứng lương đã được duyệt thành công!');

        } catch (\Exception $e) {
            Log::error('Error approving salary advance: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi duyệt đơn ứng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi duyệt đơn ứng lương');
        }
    }

    public function reject(Request $request, SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        if (!$salaryAdvance->canBeRejected()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể từ chối đơn ứng lương này'
                ], 400);
            }
            return back()->with('error', 'Không thể từ chối đơn ứng lương này');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $salaryAdvance->reject(Auth::id(), $request->rejection_reason);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được từ chối!'
                ]);
            }

            return back()->with('success', 'Đơn ứng lương đã được từ chối!');

        } catch (\Exception $e) {
            Log::error('Error rejecting salary advance: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi từ chối đơn ứng lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi từ chối đơn ứng lương');
        }
    }

    public function addRepayment(Request $request, SalaryAdvance $salaryAdvance)
    {
        // Check if user belongs to the same organization
        if ($salaryAdvance->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to salary advance.');
        }

        if (!$salaryAdvance->canBeRepaid()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thêm thanh toán cho đơn ứng lương này'
                ], 400);
            }
            return back()->with('error', 'Không thể thêm thanh toán cho đơn ứng lương này');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $salaryAdvance->remaining_amount
        ]);

        try {
            DB::beginTransaction();

            $salaryAdvance->addRepayment($request->amount);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thanh toán đã được thêm thành công!'
                ]);
            }

            return back()->with('success', 'Thanh toán đã được thêm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding repayment: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm thanh toán'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi thêm thanh toán');
        }
    }
}