<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get assigned properties
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id');
        
        if ($assignedPropertyIds->isEmpty()) {
            $leads = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $properties = collect();
            return view('agent.leads.index', compact('leads', 'properties', 'request'));
        }

        // Base query for leads
        $query = Lead::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by budget range
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', $request->budget_min);
        }
        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', $request->budget_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['id', 'created_at', 'name', 'phone', 'email', 'status', 'source'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $leads = $query->orderBy($sortBy, $sortOrder)->paginate(15)->withQueryString();

        // Get properties for filter dropdown
        $properties = Property::whereIn('id', $assignedPropertyIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('agent.leads.index', compact(
            'leads',
            'properties',
            'request'
        ));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        return view('agent.leads.create');
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        // Clean and validate currency inputs
        $budgetMin = $request->budget_min ? str_replace(['.', ','], '', $request->budget_min) : null;
        $budgetMax = $request->budget_max ? str_replace(['.', ','], '', $request->budget_max) : null;
        
        // Validate request
        $request->validate([
            'source' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'desired_city' => 'nullable|string|max:100',
            'budget_min' => 'nullable|string|regex:/^[\d.,]+$/',
            'budget_max' => 'nullable|string|regex:/^[\d.,]+$/',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,converted,lost',
        ], [
            'source.required' => 'Vui lòng chọn nguồn lead.',
            'name.required' => 'Vui lòng nhập tên khách hàng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.email' => 'Email không hợp lệ.',
            'budget_min.regex' => 'Ngân sách tối thiểu chỉ được chứa số và dấu phẩy/chấm.',
            'budget_max.regex' => 'Ngân sách tối đa chỉ được chứa số và dấu phẩy/chấm.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        // Additional validation for budget ranges
        if ($budgetMin && $budgetMax && (int)$budgetMin > (int)$budgetMax) {
            return back()->withErrors(['budget_max' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.'])->withInput();
        }

        // Validate numeric values after cleaning
        if ($budgetMin && (!is_numeric($budgetMin) || (int)$budgetMin < 0)) {
            return back()->withErrors(['budget_min' => 'Ngân sách tối thiểu phải là số dương hợp lệ.'])->withInput();
        }
        
        if ($budgetMax && (!is_numeric($budgetMax) || (int)$budgetMax < 0)) {
            return back()->withErrors(['budget_max' => 'Ngân sách tối đa phải là số dương hợp lệ.'])->withInput();
        }

        try {
            // Get the authenticated user's organization
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $organization = $user->organizations()->first();
            
            if (!$organization) {
                return back()->withInput()->with('error', 'Không tìm thấy thông tin tổ chức của bạn.');
            }

            $lead = Lead::create([
                'organization_id' => $organization->id,
                'source' => $request->source,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'desired_city' => $request->desired_city,
                'budget_min' => $budgetMin ? (int)$budgetMin : null,
                'budget_max' => $budgetMax ? (int)$budgetMax : null,
                'note' => $request->note,
                'status' => $request->status,
            ]);

            return redirect()->route('agent.leads.show', $lead->id)
                ->with('success', 'Lead đã được tạo thành công!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo lead: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id);

        // Get viewings for this lead
        $viewings = $lead->viewings()
            ->with(['unit.property', 'agent'])
            ->orderBy('schedule_at', 'desc')
            ->get();

        // Get booking deposits for this lead
        $bookingDeposits = $lead->bookingDeposits()
            ->with(['unit.property', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.leads.show', compact('lead', 'viewings', 'bookingDeposits'));
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        return view('agent.leads.edit', compact('lead'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        // Clean and validate currency inputs
        $budgetMin = $request->budget_min ? str_replace(['.', ','], '', $request->budget_min) : null;
        $budgetMax = $request->budget_max ? str_replace(['.', ','], '', $request->budget_max) : null;
        
        // Validate request
        $request->validate([
            'source' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'desired_city' => 'nullable|string|max:100',
            'budget_min' => 'nullable|string|regex:/^[\d.,]+$/',
            'budget_max' => 'nullable|string|regex:/^[\d.,]+$/',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,converted,lost',
        ], [
            'source.required' => 'Vui lòng chọn nguồn lead.',
            'name.required' => 'Vui lòng nhập tên khách hàng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.email' => 'Email không hợp lệ.',
            'budget_min.regex' => 'Ngân sách tối thiểu chỉ được chứa số và dấu phẩy/chấm.',
            'budget_max.regex' => 'Ngân sách tối đa chỉ được chứa số và dấu phẩy/chấm.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        // Additional validation for budget ranges
        if ($budgetMin && $budgetMax && (int)$budgetMin > (int)$budgetMax) {
            return back()->withErrors(['budget_max' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.'])->withInput();
        }

        // Validate numeric values after cleaning
        if ($budgetMin && (!is_numeric($budgetMin) || (int)$budgetMin < 0)) {
            return back()->withErrors(['budget_min' => 'Ngân sách tối thiểu phải là số dương hợp lệ.'])->withInput();
        }
        
        if ($budgetMax && (!is_numeric($budgetMax) || (int)$budgetMax < 0)) {
            return back()->withErrors(['budget_max' => 'Ngân sách tối đa phải là số dương hợp lệ.'])->withInput();
        }

        try {
            $lead->update([
                'source' => $request->source,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'desired_city' => $request->desired_city,
                'budget_min' => $budgetMin ? (int)$budgetMin : null,
                'budget_max' => $budgetMax ? (int)$budgetMax : null,
                'note' => $request->note,
                'status' => $request->status,
            ]);

            return redirect()->route('agent.leads.show', $lead->id)
                ->with('success', 'Lead đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật lead: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);

        try {
            $lead->delete();

            return redirect()->route('agent.leads.index')
                ->with('success', 'Lead đã được xóa thành công!');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa lead: ' . $e->getMessage());
        }
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,converted,lost',
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        try {
            $lead->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái lead đã được cập nhật thành công!',
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lead statistics
     */
    public function statistics()
    {
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $contactedLeads = Lead::where('status', 'contacted')->count();
        $qualifiedLeads = Lead::where('status', 'qualified')->count();
        $convertedLeads = Lead::where('status', 'converted')->count();
        $lostLeads = Lead::where('status', 'lost')->count();

        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        // Leads by source
        $leadsBySource = Lead::selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();

        // Leads by month (last 12 months)
        $leadsByMonth = Lead::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('agent.leads.statistics', compact(
            'totalLeads',
            'newLeads',
            'contactedLeads',
            'qualifiedLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate',
            'leadsBySource',
            'leadsByMonth'
        ));
    }
}
