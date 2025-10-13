<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SalaryAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryAdvanceController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = $user->salaryAdvances()->with(['approver', 'rejector']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('advance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('advance_date', '<=', $request->date_to);
        }

        $salaryAdvances = $query->orderBy('created_at', 'desc')->paginate(15);

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            'repaid' => 'Đã hoàn trả',
            'partially_repaid' => 'Hoàn trả một phần'
        ];

        // Statistics
        $stats = [
            'total' => $user->salaryAdvances()->count(),
            'pending' => $user->salaryAdvances()->where('status', 'pending')->count(),
            'approved' => $user->salaryAdvances()->where('status', 'approved')->count(),
            'total_amount' => $user->salaryAdvances()->sum('amount'),
            'remaining_amount' => $user->salaryAdvances()->whereIn('status', ['approved', 'partially_repaid'])->sum('remaining_amount')
        ];

        return view('agent.salary-advances.index', compact('salaryAdvances', 'statuses', 'stats'));
    }

    public function create()
    {
        $repaymentMethods = [
            'payroll_deduction' => 'Trừ lương',
            'direct_payment' => 'Thanh toán trực tiếp',
            'installment' => 'Trả góp'
        ];

        return view('agent.salary-advances.create', compact('repaymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:50000000',
            'currency' => 'required|string|max:3',
            'advance_date' => 'required|date|before_or_equal:today',
            'expected_repayment_date' => 'required|date|after:advance_date',
            'reason' => 'required|string|max:1000',
            'repayment_method' => 'required|in:payroll_deduction,direct_payment,installment',
            'installment_months' => 'nullable|integer|min:1|max:12',
            'monthly_deduction' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $organization = $user->organizations()->first();

            $salaryAdvance = SalaryAdvance::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
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

            return redirect()->route('agent.salary-advances.index')
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

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $salaryAdvance = SalaryAdvance::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['approver', 'rejector'])
            ->firstOrFail();

        return view('agent.salary-advances.show', compact('salaryAdvance'));
    }

    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $salaryAdvance = SalaryAdvance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Only allow editing pending or rejected advances
        if (!$salaryAdvance->canBeDeleted()) {
            return back()->with('error', 'Chỉ có thể chỉnh sửa đơn ứng lương đang chờ duyệt hoặc đã từ chối.');
        }

        $repaymentMethods = [
            'payroll_deduction' => 'Trừ lương',
            'direct_payment' => 'Thanh toán trực tiếp',
            'installment' => 'Trả góp'
        ];

        return view('agent.salary-advances.edit', compact('salaryAdvance', 'repaymentMethods'));
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $salaryAdvance = SalaryAdvance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Only allow editing pending or rejected advances
        if (!$salaryAdvance->canBeDeleted()) {
            return back()->with('error', 'Chỉ có thể chỉnh sửa đơn ứng lương đang chờ duyệt hoặc đã từ chối.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:100000|max:50000000',
            'currency' => 'required|string|max:3',
            'advance_date' => 'required|date|before_or_equal:today',
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

            return redirect()->route('agent.salary-advances.index')
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

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $salaryAdvance = SalaryAdvance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Only allow deleting pending or rejected advances
        if (!$salaryAdvance->canBeDeleted()) {
            return back()->with('error', 'Chỉ có thể xóa đơn ứng lương đang chờ duyệt hoặc đã từ chối.');
        }

        try {
            DB::beginTransaction();

            $salaryAdvance->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn ứng lương đã được xóa thành công!'
                ]);
            }

            return redirect()->route('agent.salary-advances.index')
                ->with('success', 'Đơn ứng lương đã được xóa thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
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
}
