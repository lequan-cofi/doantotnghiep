<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\PayrollPayslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = $user->payslips()->with('payrollCycle');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->whereHas('payrollCycle', function($q) use ($request) {
                $q->where('period_month', 'like', '%' . $request->period . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $payslips = $query->orderBy('created_at', 'desc')->paginate(15);

        $statuses = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán'
        ];

        return view('agent.payslips.index', compact('payslips', 'statuses'));
    }

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $payslip = PayrollPayslip::where('id', $id)
            ->where('user_id', $user->id)
            ->with('payrollCycle')
            ->firstOrFail();

        return view('agent.payslips.show', compact('payslip'));
    }
}
