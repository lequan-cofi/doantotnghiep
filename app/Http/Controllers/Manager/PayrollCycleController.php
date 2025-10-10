<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PayrollCycle;
use App\Models\PayrollPayslip;
use App\Models\SalaryContract;
use App\Models\CommissionEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollCycleController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollCycle::where('organization_id', Auth::user()->organizations()->first()?->id)
            ->withCount(['payslips', 'items']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('period_month', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('period_month', 'like', $request->year . '%');
        }

        $cycles = $query->orderBy('period_month', 'desc')->paginate(15);

        return view('manager.payroll-cycles.index', compact('cycles'));
    }

    public function create()
    {
        // Get current month and next few months for selection
        $currentMonth = Carbon::now()->format('Y-m');
        $availableMonths = [];
        
        for ($i = 0; $i < 12; $i++) {
            $month = Carbon::now()->addMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->addMonths($i)->format('m/Y');
            $availableMonths[$month] = $monthLabel;
        }

        return view('manager.payroll-cycles.create', compact('availableMonths', 'currentMonth'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_month' => 'required|date_format:Y-m|unique:payroll_cycles,period_month,NULL,id,organization_id,' . Auth::user()->organizations()->first()?->id,
            'note' => 'nullable|string|max:255'
        ]);

        try {
            $organization = Auth::user()->organizations()->first();
            
            $cycle = PayrollCycle::create([
                'organization_id' => $organization->id,
                'period_month' => $request->period_month,
                'status' => 'open', // Ensure status is always a string
                'note' => $request->note,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kỳ lương đã được tạo thành công!',
                    'data' => $cycle
                ]);
            }

            return redirect()->route('manager.payroll-cycles.index')
                ->with('success', 'Kỳ lương đã được tạo thành công!');

        } catch (\Exception $e) {
            Log::error('Error creating payroll cycle: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo kỳ lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo kỳ lương');
        }
    }

    public function show(PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        $payrollCycle->load(['payslips.user', 'items.user']);
        
        // Get summary statistics
        $totalGross = $payrollCycle->payslips->sum('gross_amount');
        $totalDeductions = $payrollCycle->payslips->sum('deduction_amount');
        $totalNet = $payrollCycle->payslips->sum('net_amount');
        $totalEmployees = $payrollCycle->payslips->count();

        return view('manager.payroll-cycles.show', compact('payrollCycle', 'totalGross', 'totalDeductions', 'totalNet', 'totalEmployees'));
    }

    public function edit(PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        // Only allow editing if status is 'open'
        if ($payrollCycle->status !== 'open') {
            return back()->with('error', 'Chỉ có thể chỉnh sửa kỳ lương đang mở');
        }

        return view('manager.payroll-cycles.edit', compact('payrollCycle'));
    }

    public function update(Request $request, PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        // Only allow editing if status is 'open'
        if ($payrollCycle->status !== 'open') {
            return back()->with('error', 'Chỉ có thể chỉnh sửa kỳ lương đang mở');
        }

        $request->validate([
            'note' => 'nullable|string|max:255'
        ]);

        try {
            $payrollCycle->update([
                'note' => $request->note,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kỳ lương đã được cập nhật thành công!',
                    'data' => $payrollCycle
                ]);
            }

            return redirect()->route('manager.payroll-cycles.index')
                ->with('success', 'Kỳ lương đã được cập nhật thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating payroll cycle: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật kỳ lương'
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật kỳ lương');
        }
    }

    public function destroy(PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        // Only allow deletion if status is 'open' and no payslips exist
        if ($payrollCycle->status !== 'open') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa kỳ lương đang mở'
                ], 400);
            }
            return back()->with('error', 'Chỉ có thể xóa kỳ lương đang mở');
        }

        if ($payrollCycle->payslips()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa kỳ lương đã có phiếu lương'
                ], 400);
            }
            return back()->with('error', 'Không thể xóa kỳ lương đã có phiếu lương');
        }

        try {
            $payrollCycle->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kỳ lương đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.payroll-cycles.index')
                ->with('success', 'Kỳ lương đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting payroll cycle: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa kỳ lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa kỳ lương');
        }
    }

    public function lock(PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        if ($payrollCycle->status !== 'open') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể khóa kỳ lương đang mở'
                ], 400);
            }
            return back()->with('error', 'Chỉ có thể khóa kỳ lương đang mở');
        }

        try {
            $payrollCycle->update([
                'status' => 'locked', // Ensure status is always a string
                'locked_at' => now()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kỳ lương đã được khóa thành công!'
                ]);
            }

            return back()->with('success', 'Kỳ lương đã được khóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error locking payroll cycle: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi khóa kỳ lương'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi khóa kỳ lương');
        }
    }

    public function generatePayslips(PayrollCycle $payrollCycle)
    {
        // Check if user belongs to the same organization
        if ($payrollCycle->organization_id !== Auth::user()->organizations()->first()?->id) {
            abort(403, 'Unauthorized access to payroll cycle.');
        }

        if ($payrollCycle->status !== 'open') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể tạo phiếu lương cho kỳ lương đang mở'
                ], 400);
            }
            return back()->with('error', 'Chỉ có thể tạo phiếu lương cho kỳ lương đang mở');
        }

        try {
            DB::beginTransaction();

            $organization = Auth::user()->organizations()->first();
            $periodStart = Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->startOfMonth();
            $periodEnd = Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->endOfMonth();

            // Get all active salary contracts for the organization
            $salaryContracts = SalaryContract::where('organization_id', $organization->id)
                ->where('status', 'active')
                ->where('effective_from', '<=', $periodEnd)
                ->where(function($q) use ($periodStart) {
                    $q->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $periodStart);
                })
                ->with('user')
                ->get();

            $createdCount = 0;
            foreach ($salaryContracts as $contract) {
                // Check if payslip already exists
                $existingPayslip = PayrollPayslip::where('payroll_cycle_id', $payrollCycle->id)
                    ->where('user_id', $contract->user_id)
                    ->first();

                if ($existingPayslip) {
                    continue; // Skip if payslip already exists
                }

                // Calculate basic salary
                $basicSalary = $contract->base_salary;

                // Calculate allowances
                $allowances = 0;
                if ($contract->allowances_json) {
                    foreach ($contract->allowances_json as $allowance) {
                        $allowances += $allowance;
                    }
                }

                // Calculate commission for the period
                $commission = CommissionEvent::where('agent_id', $contract->user_id)
                    ->where('status', 'paid')
                    ->whereBetween('occurred_at', [$periodStart, $periodEnd])
                    ->sum('commission_total');

                // Calculate salary advances deduction for the period
                $salaryAdvanceDeduction = 0;
                $salaryAdvances = \App\Models\SalaryAdvance::where('user_id', $contract->user_id)
                    ->where('organization_id', $organization->id)
                    ->where('repayment_method', 'payroll_deduction')
                    ->whereIn('status', ['approved', 'partially_repaid'])
                    ->where('remaining_amount', '>', 0)
                    ->get();

                foreach ($salaryAdvances as $advance) {
                    $monthlyDeduction = $advance->calculateMonthlyDeduction();
                    $salaryAdvanceDeduction += $monthlyDeduction;
                }

                // Calculate gross amount
                $grossAmount = $basicSalary + $allowances + $commission;
                
                // Calculate net amount (after salary advance deductions)
                $netAmount = $grossAmount - $salaryAdvanceDeduction;

                // Create payslip
                PayrollPayslip::create([
                    'payroll_cycle_id' => $payrollCycle->id,
                    'user_id' => $contract->user_id,
                    'gross_amount' => $grossAmount,
                    'deduction_amount' => $salaryAdvanceDeduction, // Salary advance deductions
                    'net_amount' => $netAmount, // Net amount after salary advance deductions
                    'status' => 'pending', // Ensure status is always a string
                ]);

                // Update salary advances with actual deductions
                foreach ($salaryAdvances as $advance) {
                    $monthlyDeduction = $advance->calculateMonthlyDeduction();
                    if ($monthlyDeduction > 0) {
                        $advance->addRepayment($monthlyDeduction);
                    }
                }

                $createdCount++;
            }

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã tạo thành công {$createdCount} phiếu lương!",
                    'created_count' => $createdCount
                ]);
            }

            return back()->with('success', "Đã tạo thành công {$createdCount} phiếu lương!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating payslips: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo phiếu lương: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tạo phiếu lương');
        }
    }
}
