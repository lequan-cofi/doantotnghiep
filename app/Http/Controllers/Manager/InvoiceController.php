<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Lease;
use App\Models\BookingDeposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Start with basic query
            $query = Invoice::with([
                'lease.unit.property',
                'lease.tenant',
                'bookingDeposit.unit.property',
                'bookingDeposit.tenantUser',
                'bookingDeposit.lead',
                'organization',
                'items',
                'payments.method'
            ]);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhereHas('lease.tenant', function($tenantQuery) use ($search) {
                          $tenantQuery->where('full_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                                     ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->orWhereHas('lease.unit.property', function($propertyQuery) use ($search) {
                          $propertyQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('bookingDeposit.tenantUser', function($tenantQuery) use ($search) {
                          $tenantQuery->where('full_name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%")
                                     ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->orWhereHas('bookingDeposit.lead', function($leadQuery) use ($search) {
                          $leadQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->orWhereHas('bookingDeposit.unit.property', function($propertyQuery) use ($search) {
                          $propertyQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by lease
            if ($request->filled('lease_id')) {
                $query->where('lease_id', $request->lease_id);
            }

            // Filter by booking deposit
            if ($request->filled('booking_deposit_id')) {
                $query->where('booking_deposit_id', $request->booking_deposit_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('issue_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('issue_date', '<=', $request->date_to);
            }

            // Filter by amount range
            if ($request->filled('amount_min')) {
                $query->where('total_amount', '>=', $request->amount_min);
            }
            if ($request->filled('amount_max')) {
                $query->where('total_amount', '<=', $request->amount_max);
            }

            $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading invoices: ' . $e->getMessage());
            $invoices = Invoice::query()->paginate(10);
        }

        // Get filter data - ensure variables are always defined
        $leases = collect();
        $bookingDeposits = collect();
        $paymentMethods = collect();
        
        try {
            $leases = Lease::with(['unit.property', 'tenant'])->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading leases: ' . $e->getMessage());
        }
        
        try {
            $bookingDeposits = BookingDeposit::with(['unit.property', 'tenantUser', 'lead'])->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading booking deposits: ' . $e->getMessage());
        }
        
        try {
            $paymentMethods = PaymentMethod::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading payment methods: ' . $e->getMessage());
        }

        return view('manager.invoices.index', [
            'invoices' => $invoices,
            'leases' => $leases,
            'bookingDeposits' => $bookingDeposits,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function create()
    {
        // Ensure variables are always defined
        $leases = collect();
        $bookingDeposits = collect();
        $paymentMethods = collect();
        
        try {
            $leases = Lease::with(['unit.property', 'tenant'])->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading leases in create: ' . $e->getMessage());
        }
        
        try {
            $bookingDeposits = BookingDeposit::with(['unit.property', 'tenantUser', 'lead'])->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading booking deposits in create: ' . $e->getMessage());
        }
        
        try {
            $paymentMethods = PaymentMethod::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading payment methods in create: ' . $e->getMessage());
        }

        return view('manager.invoices.create', [
            'leases' => $leases,
            'bookingDeposits' => $bookingDeposits,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'lease_id' => 'nullable|exists:leases,id',
                'booking_deposit_id' => 'nullable|exists:booking_deposits,id',
                'invoice_no' => 'nullable|string|max:100|unique:invoices,invoice_no',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'status' => 'required|in:draft,issued,paid,overdue,cancelled',
                'subtotal' => 'required|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10',
                'note' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:rent,service,meter,deposit,other',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.amount' => 'required|numeric|min:0',
            ]);

            // Ensure either lease_id or booking_deposit_id is provided
            if (empty($validated['lease_id']) && empty($validated['booking_deposit_id'])) {
                return back()->withInput()->with('error', 'Vui lòng chọn hợp đồng thuê hoặc đặt cọc.');
            }

            DB::beginTransaction();

            // Get organization from current user
            $currentUser = Auth::user();
            $organization = \App\Models\OrganizationUser::where('user_id', $currentUser->id)->first()?->organization;

            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $organization?->id,
                'lease_id' => $validated['lease_id'],
                'booking_deposit_id' => $validated['booking_deposit_id'],
                'invoice_no' => $validated['invoice_no'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'currency' => $validated['currency'] ?? 'VND',
                'note' => $validated['note'],
            ]);

            // Add invoice items
            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'item_type' => $itemData['item_type'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'amount' => $itemData['amount'],
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hóa đơn đã được tạo thành công!',
                    'redirect' => route('manager.invoices.show', $invoice->id)
                ]);
            }

            return redirect()->route('manager.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo hóa đơn: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hóa đơn: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'lease.unit.property.propertyType',
            'lease.unit.property.location',
            'lease.unit.property.location2025',
            'lease.tenant',
            'lease.agent',
            'organization',
            'items',
            'payments.method',
            'payments.payerUser'
        ])->findOrFail($id);

        return view('manager.invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with([
            'lease.unit.property',
            'lease.tenant',
            'items'
        ])->findOrFail($id);

        // Ensure variables are always defined
        $leases = collect();
        $paymentMethods = collect();
        
        try {
            $leases = Lease::with(['unit.property', 'tenant'])->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading leases in edit: ' . $e->getMessage());
        }
        
        try {
            $paymentMethods = PaymentMethod::all();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading payment methods in edit: ' . $e->getMessage());
        }

        return view('manager.invoices.edit', [
            'invoice' => $invoice,
            'leases' => $leases,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $validated = $request->validate([
                'lease_id' => 'required|exists:leases,id',
                'invoice_no' => 'nullable|string|max:100|unique:invoices,invoice_no,' . $id,
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'status' => 'required|in:draft,issued,paid,overdue,cancelled',
                'subtotal' => 'required|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10',
                'note' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:rent,service,meter,deposit,other',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.amount' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Update invoice
            $invoice->update([
                'lease_id' => $validated['lease_id'],
                'invoice_no' => $validated['invoice_no'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'currency' => $validated['currency'] ?? 'VND',
                'note' => $validated['note'],
            ]);

            // Update invoice items
            $invoice->items()->delete();
            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'item_type' => $itemData['item_type'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'amount' => $itemData['amount'],
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hóa đơn đã được cập nhật thành công!',
                    'redirect' => route('manager.invoices.show', $invoice->id)
                ]);
            }

            return redirect()->route('manager.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật hóa đơn: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật hóa đơn: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Soft delete the invoice (trait sẽ tự động set deleted_by)
            $invoice->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hóa đơn đã được xóa thành công!'
                ]);
            }

            return redirect()->route('manager.invoices.index')
                ->with('success', 'Hóa đơn đã được xóa thành công!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting invoice: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa hóa đơn: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi xóa hóa đơn: ' . $e->getMessage());
        }
    }

    // API method to get lease details for invoice
    public function getLeaseDetails($leaseId)
    {
        $lease = Lease::with([
            'unit.property',
            'tenant',
            'leaseServices.service'
        ])->findOrFail($leaseId);

        return response()->json([
            'lease' => $lease,
            'rent_amount' => $lease->rent_amount,
            'services' => $lease->leaseServices->map(function($ls) {
                return [
                    'service_id' => $ls->service_id,
                    'service_name' => $ls->service->name,
                    'price' => $ls->price,
                    'pricing_type' => $ls->service->pricing_type ?? 'fixed',
                    'unit_label' => $ls->service->unit_label ?? 'tháng'
                ];
            })
        ]);
    }
}
