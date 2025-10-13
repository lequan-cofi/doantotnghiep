<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\PayrollCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollCycleController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = PayrollCycle::whereHas('payslips', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['payslips' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period_month', 'like', '%' . $request->period . '%');
        }

        $payrollCycles = $query->orderBy('period_month', 'desc')->paginate(15);

        $statuses = [
            'open' => 'Mở',
            'locked' => 'Đã khóa',
            'paid' => 'Đã thanh toán'
        ];

        return view('agent.payroll-cycles.index', compact('payrollCycles', 'statuses'));
    }

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $payrollCycle = PayrollCycle::where('id', $id)
            ->whereHas('payslips', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['payslips' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->firstOrFail();

        return view('agent.payroll-cycles.show', compact('payrollCycle'));
    }
}