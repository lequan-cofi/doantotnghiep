<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PayrollPayslip;
use App\Models\PayrollCycle;
use App\Models\SalaryContract;
use App\Models\CommissionEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollPayslipController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollPayslip::with(['user', 'payrollCycle'])
            ->whereHas('payrollCycle', function($q) {
                $q->where('organization_id', Auth::user()->organizations()->first()?->id);
            });

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cycle_id')) {
            $query->where('payroll_cycle_id', $request->cycle_id);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('payrollCycle', function($q) use ($request) {
                $q->where('period_month', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('payrollCycle', function($q) use ($request) {
                $q->where('period_month', '<=', $request->date_to);
            });
        }

        $payslips = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get cycles for filter dropdown
        $cycles = PayrollCycle::where('organization_id', Auth::user()->organizations()->first()?->id)
            ->orderBy('period_month', 'desc')
            ->get();

        return view('manager.payroll-payslips.index', compact('payslips', 'cycles'));
    }

    public function show(PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        $payrollPayslip->load(['user', 'payrollCycle']);

        // Get salary breakdown
        $salaryContract = SalaryContract::where('user_id', $payrollPayslip->user_id)
            ->where('status', 'active')
            ->where('effective_from', '<=', Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->endOfMonth())
            ->where(function($q) use ($payrollPayslip) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->startOfMonth());
            })
            ->first();

        // Get commission details for the period
        $periodStart = Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->startOfMonth();
        $periodEnd = Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->endOfMonth();

        $commissionEvents = CommissionEvent::where('agent_id', $payrollPayslip->user_id)
            ->where('status', 'paid')
            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
            ->with(['policy', 'lease', 'unit'])
            ->get();

        $totalCommission = $commissionEvents->sum('commission_total');

        return view('manager.payroll-payslips.show', compact('payrollPayslip', 'salaryContract', 'commissionEvents', 'totalCommission'));
    }

    public function edit(PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        // Only allow editing if cycle is not locked
        if ($payrollPayslip->payrollCycle->status === 'locked') {
            return back()->with('error', 'Không thể chỉnh sửa phiếu lương của kỳ lương đã khóa');
        }

        return view('manager.payroll-payslips.edit', compact('payrollPayslip'));
    }

    public function update(Request $request, PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        // Only allow editing if cycle is not locked
        if ($payrollPayslip->payrollCycle->status === 'locked') {
            return back()->with('error', 'Không thể chỉnh sửa phiếu lương của kỳ lương đã khóa');
        }

        $request->validate([
            'gross_amount' => 'required|numeric|min:0',
            'deduction_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255'
        ]);

        try {
            $netAmount = $request->gross_amount - $request->deduction_amount;

            $payrollPayslip->update([
                'gross_amount' => $request->gross_amount,
                'deduction_amount' => $request->deduction_amount,
                'net_amount' => $netAmount,
                'note' => $request->note,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phiếu lương đã được cập nhật thành công!',
                    'data' => $payrollPayslip
                ]);
            }

            return redirect()->route('manager.payroll-payslips.show', $payrollPayslip->id)
                ->with('success', 'Phiếu lương đã được cập nhật thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating payslip: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật phiếu lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật phiếu lương');
        }
    }

    public function destroy(PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        // Only allow deletion if cycle is not locked
        if ($payrollPayslip->payrollCycle->status === 'locked') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa phiếu lương của kỳ lương đã khóa'
                ], 400);
            }
            return back()->with('error', 'Không thể xóa phiếu lương của kỳ lương đã khóa');
        }

        try {
            $payrollPayslip->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phiếu lương đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.payroll-payslips.index')
                ->with('success', 'Phiếu lương đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting payslip: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa phiếu lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa phiếu lương');
        }
    }

    public function markAsPaid(PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        try {
            $payrollPayslip->update([
                'status' => 'paid', // Ensure status is always a string
                'paid_at' => now()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phiếu lương đã được đánh dấu là đã thanh toán!'
                ]);
            }

            return back()->with('success', 'Phiếu lương đã được đánh dấu là đã thanh toán!');

        } catch (\Exception $e) {
            Log::error('Error marking payslip as paid: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi đánh dấu phiếu lương: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi đánh dấu phiếu lương');
        }
    }

    public function recalculate(PayrollPayslip $payrollPayslip)
    {
        // Check if user belongs to the same organization
        if ($payrollPayslip->payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        // Only allow recalculation if cycle is not locked
        if ($payrollPayslip->payrollCycle->status === 'locked') {
            return back()->with('error', 'Không thể tính lại phiếu lương của kỳ lương đã khóa');
        }

        try {
            DB::beginTransaction();

            $periodStart = Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->startOfMonth();
            $periodEnd = Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->endOfMonth();

            // Get salary contract
            $salaryContract = SalaryContract::where('user_id', $payrollPayslip->user_id)
                ->where('status', 'active')
                ->where('effective_from', '<=', $periodEnd)
                ->where(function($q) use ($periodStart) {
                    $q->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $periodStart);
                })
                ->first();

            if (!$salaryContract) {
                throw new \Exception('Không tìm thấy hợp đồng lương cho nhân viên này');
            }

            // Calculate basic salary
            $basicSalary = $salaryContract->base_salary;

            // Calculate allowances
            $allowances = 0;
            if ($salaryContract->allowances_json) {
                foreach ($salaryContract->allowances_json as $allowance) {
                    $allowances += $allowance;
                }
            }

            // Calculate commission for the period
            $commission = CommissionEvent::where('agent_id', $payrollPayslip->user_id)
                ->where('status', 'paid')
                ->whereBetween('occurred_at', [$periodStart, $periodEnd])
                ->sum('commission_total');

            // Calculate gross amount
            $grossAmount = $basicSalary + $allowances + $commission;

            // Update payslip
            $payrollPayslip->update([
                'gross_amount' => $grossAmount,
                'net_amount' => $grossAmount - $payrollPayslip->deduction_amount,
            ]);

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phiếu lương đã được tính lại thành công!'
                ]);
            }

            return back()->with('success', 'Phiếu lương đã được tính lại thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recalculating payslip: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tính lại phiếu lương: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tính lại phiếu lương: ' . $e->getMessage());
        }
    }
}
