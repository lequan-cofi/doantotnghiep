<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\SalaryContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryContractController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = $user->salaryContracts()->with('organization');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('effective_from', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('effective_from', '<=', $request->date_to);
        }

        $salaryContracts = $query->orderBy('effective_from', 'desc')->paginate(15);

        $statuses = [
            'active' => 'Đang hoạt động',
            'inactive' => 'Không hoạt động',
            'terminated' => 'Đã chấm dứt'
        ];

        return view('agent.salary-contracts.index', compact('salaryContracts', 'statuses'));
    }

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $salaryContract = SalaryContract::where('id', $id)
            ->where('user_id', $user->id)
            ->with('organization')
            ->firstOrFail();

        return view('agent.salary-contracts.show', compact('salaryContract'));
    }
}
