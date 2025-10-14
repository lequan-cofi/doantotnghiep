<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the tenant's invoices
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all invoices for the authenticated tenant
        $query = Invoice::with([
            'lease.unit.property.location',
            'lease.unit.property.location2025',
            'lease.unit.property.propertyType',
            'items'
        ])
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('deleted_at');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($status === 'pending') {
                $query->where('status', 'issued')
                      ->where('due_date', '>=', Carbon::now());
            } elseif ($status === 'overdue') {
                $query->where('status', 'issued')
                      ->where('due_date', '<', Carbon::now());
            } elseif ($status === 'draft') {
                $query->where('status', 'draft');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        // Apply month filter
        if ($request->filled('month')) {
            $month = $request->month;
            $query->whereYear('issue_date', substr($month, 0, 4))
                  ->whereMonth('issue_date', substr($month, 5, 2));
        }

        $invoices = $query->latest('issue_date')->paginate(10);

        // Calculate statistics
        $stats = $this->calculateInvoiceStats($user->id);

        return view('tenant.invoice.index', compact('invoices', 'stats'));
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Get invoice with all related data
        $invoice = Invoice::with([
            'lease.unit.property.location',
            'lease.unit.property.location2025',
            'lease.unit.property.propertyType',
            'lease.leaseServices.service',
            'items',
            'lease.agent',
            'lease.tenant'
        ])
        ->where('id', $id)
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('deleted_at')
        ->firstOrFail();

        // Check if invoice is overdue
        $isOverdue = $invoice->status === 'issued' && $invoice->due_date < Carbon::now();

        return view('tenant.invoice.show', compact('invoice', 'isOverdue'));
    }

    /**
     * Process payment for an invoice
     */
    public function pay(Request $request, $id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::where('id', $id)
            ->whereHas('lease', function($q) use ($user) {
                $q->where('tenant_id', $user->id);
            })
            ->whereNull('deleted_at')
            ->firstOrFail();

        if ($invoice->status !== 'issued') {
            return response()->json([
                'success' => false,
                'message' => 'Hóa đơn này không thể thanh toán'
            ], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:momo,bank,vnpay,zalopay',
            'payment_reference' => 'nullable|string|max:255'
        ]);

        // Update invoice status
        $invoice->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công',
            'invoice' => $invoice
        ]);
    }

    /**
     * Download invoice PDF
     */
    public function download($id)
    {
        $user = Auth::user();
        
        $invoice = Invoice::with([
            'lease.unit.property',
            'lease.leaseServices.service',
            'items',
            'lease.agent',
            'lease.tenant'
        ])
        ->where('id', $id)
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('deleted_at')
        ->firstOrFail();

        // Generate PDF (you can implement PDF generation here)
        // For now, return a simple response
        return response()->json([
            'success' => true,
            'message' => 'PDF đang được tạo...',
            'download_url' => route('tenant.invoices.download', $id)
        ]);
    }

    /**
     * Export invoices to Excel
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Get invoices with same filters as index
        $query = Invoice::with([
            'lease.unit.property.location',
            'lease.unit.property.location2025',
            'lease.unit.property.propertyType',
            'items'
        ])
        ->whereHas('lease', function($q) use ($user) {
            $q->where('tenant_id', $user->id);
        })
        ->whereNull('deleted_at');

        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($status === 'pending') {
                $query->where('status', 'issued')
                      ->where('due_date', '>=', Carbon::now());
            } elseif ($status === 'overdue') {
                $query->where('status', 'issued')
                      ->where('due_date', '<', Carbon::now());
            } elseif ($status === 'draft') {
                $query->where('status', 'draft');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        if ($request->filled('month')) {
            $month = $request->month;
            $query->whereYear('issue_date', substr($month, 0, 4))
                  ->whereMonth('issue_date', substr($month, 5, 2));
        }

        $invoices = $query->latest('issue_date')->get();

        // For now, return a simple response
        // In a real application, you would generate an Excel file
        return response()->json([
            'success' => true,
            'message' => 'Export thành công',
            'count' => $invoices->count()
        ]);
    }

    /**
     * Calculate invoice statistics
     */
    private function calculateInvoiceStats($tenantId)
    {
        $now = Carbon::now();

        $paid = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'paid')
        ->whereNull('deleted_at')
        ->count();

        $paidAmount = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'paid')
        ->whereNull('deleted_at')
        ->sum('total_amount');

        $pending = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'issued')
        ->where('due_date', '>=', $now)
        ->whereNull('deleted_at')
        ->count();

        $pendingAmount = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'issued')
        ->where('due_date', '>=', $now)
        ->whereNull('deleted_at')
        ->sum('total_amount');

        $overdue = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'issued')
        ->where('due_date', '<', $now)
        ->whereNull('deleted_at')
        ->count();

        $overdueAmount = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->where('status', 'issued')
        ->where('due_date', '<', $now)
        ->whereNull('deleted_at')
        ->sum('total_amount');

        $total = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->whereNull('deleted_at')
        ->count();

        $totalAmount = Invoice::whereHas('lease', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->whereNull('deleted_at')
        ->sum('total_amount');

        return [
            'paid' => $paid,
            'paid_amount' => $paidAmount,
            'pending' => $pending,
            'pending_amount' => $pendingAmount,
            'overdue' => $overdue,
            'overdue_amount' => $overdueAmount,
            'total' => $total,
            'total_amount' => $totalAmount
        ];
    }
}
