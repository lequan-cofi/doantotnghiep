<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\BookingDeposit;
use App\Models\Service;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Lấy các hợp đồng mà agent này quản lý
            $managedLeaseIds = Lease::where('agent_id', $user->id)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();

            // Lấy các booking deposits mà agent này quản lý
            $managedBookingIds = BookingDeposit::where('agent_id', $user->id)
                ->pluck('id')
                ->toArray();

            // Start with basic query - include both lease and booking deposit invoices
            $query = Invoice::with([
                'lease.unit.property',
                'lease.tenant',
                'lease.agent',
                'bookingDeposit.unit.property',
                'bookingDeposit.tenantUser',
                'bookingDeposit.lead',
                'organization',
                'items',
                'payments.method'
            ])->where(function($q) use ($managedLeaseIds, $managedBookingIds) {
                $q->whereIn('lease_id', $managedLeaseIds)
                  ->orWhereIn('booking_deposit_id', $managedBookingIds);
            });

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
                          $leadQuery->where('full_name', 'like', "%{$search}%")
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

            // Filter by invoice type
            if ($request->filled('invoice_type')) {
                if ($request->invoice_type === 'lease') {
                    $query->whereNotNull('lease_id');
                } elseif ($request->invoice_type === 'booking') {
                    $query->whereNotNull('booking_deposit_id');
                }
            }

            // Filter by lease
            if ($request->filled('lease_id')) {
                $query->where('lease_id', $request->lease_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->where('issue_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('issue_date', '<=', $request->date_to);
            }

            // Sort - mặc định sắp xếp theo ID giảm dần
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $invoices = $query->paginate(20);

            // Get managed leases for filter dropdown
            $managedLeases = Lease::with(['unit.property', 'tenant'])
                ->where('agent_id', $user->id)
                ->where('status', 'active')
                ->get();

            // Statistics - include both lease and booking deposit invoices
            $statsQuery = Invoice::where(function($q) use ($managedLeaseIds, $managedBookingIds) {
                $q->whereIn('lease_id', $managedLeaseIds)
                  ->orWhereIn('booking_deposit_id', $managedBookingIds);
            });
            
            $stats = [
                'total' => $statsQuery->count(),
                'draft' => $statsQuery->where('status', 'draft')->count(),
                'issued' => $statsQuery->where('status', 'issued')->count(),
                'paid' => $statsQuery->where('status', 'paid')->count(),
                'overdue' => $statsQuery->where('status', 'overdue')->count(),
                'total_amount' => $statsQuery->sum('total_amount'),
                'paid_amount' => $statsQuery->where('status', 'paid')->sum('total_amount'),
            ];

            return view('agent.invoices.index', compact('invoices', 'managedLeases', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách hóa đơn.');
        }
    }

    public function create(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Lấy các hợp đồng mà agent này quản lý
            $managedLeases = Lease::with(['unit.property', 'tenant'])
                ->where('agent_id', $user->id)
                ->where('status', 'active')
                ->get();

            // Lấy các dịch vụ có sẵn (nếu bảng tồn tại)
            $services = collect();
            try {
                $services = Service::all();
            } catch (\Exception $e) {
                Log::warning('Services table not found or error: ' . $e->getMessage());
            }

            // Lấy các phương thức thanh toán (nếu bảng tồn tại)
            $paymentMethods = collect();
            try {
                $paymentMethods = PaymentMethod::all();
            } catch (\Exception $e) {
                Log::warning('PaymentMethods table not found or error: ' . $e->getMessage());
            }

            // Pre-select lease if provided
            $selectedLease = null;
            if ($request->filled('lease_id')) {
                $selectedLease = $managedLeases->find($request->lease_id);
            }

            return view('agent.invoices.create', compact('managedLeases', 'services', 'paymentMethods', 'selectedLease'));
        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@create: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải trang tạo hóa đơn: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $request->validate([
                'lease_id' => 'required|exists:leases,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.amount' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
            ]);

            // Check if user manages this lease
            $lease = Lease::where('id', $request->lease_id)
                ->where('agent_id', $user->id)
                ->where('status', 'active')
                ->firstOrFail();

            DB::beginTransaction();

            // Generate invoice number
            $invoiceNo = Invoice::generateInvoiceNumber();

            // Calculate totals
            $subtotal = collect($request->items)->sum('amount');
            $taxRate = $request->tax_rate ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Get user's organization
            $userOrganization = $user->organizations()->first();
            $organizationId = $userOrganization ? $userOrganization->id : null;

            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $organizationId,
                'is_auto_created' => false, // Manual invoice creation
                'lease_id' => $request->lease_id,
                'invoice_no' => $invoiceNo,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => 'VND',
                'note' => $request->note,
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('agent.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được tạo thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in InvoiceController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo hóa đơn: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::with([
                'lease.unit.property',
                'lease.tenant',
                'lease.agent',
                'bookingDeposit.unit.property',
                'bookingDeposit.tenantUser',
                'bookingDeposit.lead',
                'bookingDeposit.agent',
                'organization',
                'items',
                'payments.method'
            ])->findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền xem hóa đơn này.');
            }

            return view('agent.invoices.show', compact('invoice'));
        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải hóa đơn.');
        }
    }

    public function edit($id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::with([
                'lease.unit.property',
                'lease.tenant',
                'bookingDeposit.unit.property',
                'bookingDeposit.tenantUser',
                'bookingDeposit.lead',
                'items'
            ])->findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền chỉnh sửa hóa đơn này.');
            }

            // Only allow editing draft invoices
            if ($invoice->status !== 'draft') {
                return redirect()->route('agent.invoices.show', $invoice->id)
                    ->with('error', 'Chỉ có thể chỉnh sửa hóa đơn ở trạng thái nháp.');
            }

            // Don't allow editing booking deposit invoices
            if ($invoice->bookingDeposit) {
                return redirect()->route('agent.invoices.show', $invoice->id)
                    ->with('error', 'Không thể chỉnh sửa hóa đơn đặt cọc. Hóa đơn này được tạo tự động.');
            }

            // Lấy các hợp đồng mà agent này quản lý
            $managedLeases = Lease::with(['unit.property', 'tenant'])
                ->where('agent_id', $user->id)
                ->where('status', 'active')
                ->get();

            // Lấy các dịch vụ có sẵn
            $services = Service::all();

            return view('agent.invoices.edit', compact('invoice', 'managedLeases', 'services'));
        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải trang chỉnh sửa hóa đơn.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền chỉnh sửa hóa đơn này.');
            }

            // Only allow editing draft invoices
            if ($invoice->status !== 'draft') {
                return redirect()->route('agent.invoices.show', $invoice->id)
                    ->with('error', 'Chỉ có thể chỉnh sửa hóa đơn ở trạng thái nháp.');
            }

            // Validate request
            $request->validate([
                'lease_id' => 'required|exists:leases,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.amount' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Calculate totals
            $subtotal = collect($request->items)->sum('amount');
            $taxRate = $request->tax_rate ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Update invoice
            $invoice->update([
                'lease_id' => $request->lease_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'note' => $request->note,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Create new invoice items
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('agent.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được cập nhật thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in InvoiceController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật hóa đơn: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền xóa hóa đơn này.');
            }

            // Only allow deleting draft invoices
            if ($invoice->status !== 'draft') {
                return redirect()->back()->with('error', 'Chỉ có thể xóa hóa đơn ở trạng thái nháp.');
            }

            $invoice->delete();

            return redirect()->route('agent.invoices.index')
                ->with('success', 'Hóa đơn đã được xóa thành công.');

        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa hóa đơn.');
        }
    }

    public function issue($id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền phát hành hóa đơn này.');
            }

            // Only allow issuing draft invoices
            if ($invoice->status !== 'draft') {
                return redirect()->back()->with('error', 'Chỉ có thể phát hành hóa đơn ở trạng thái nháp.');
            }

            $invoice->update(['status' => 'issued']);

            return redirect()->route('agent.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được phát hành thành công.');

        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@issue: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi phát hành hóa đơn.');
        }
    }

    public function cancel($id)
    {
        try {
            $user = Auth::user();
            
            $invoice = Invoice::findOrFail($id);

            // Check if user manages this invoice (either through lease or booking deposit)
            $hasAccess = false;
            if ($invoice->lease && $invoice->lease->agent_id === $user->id) {
                $hasAccess = true;
            } elseif ($invoice->bookingDeposit && $invoice->bookingDeposit->agent_id === $user->id) {
                $hasAccess = true;
            }

            if (!$hasAccess) {
                abort(403, 'Bạn không có quyền hủy hóa đơn này.');
            }

            // Only allow cancelling issued invoices
            if ($invoice->status !== 'issued') {
                return redirect()->back()->with('error', 'Chỉ có thể hủy hóa đơn đã phát hành.');
            }

            $invoice->update(['status' => 'cancelled']);

            return redirect()->route('agent.invoices.show', $invoice->id)
                ->with('success', 'Hóa đơn đã được hủy thành công.');

        } catch (\Exception $e) {
            Log::error('Error in InvoiceController@cancel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi hủy hóa đơn.');
        }
    }

    public function getLeaseInfo($leaseId)
    {
        try {
            $user = Auth::user();
            
            $lease = Lease::with(['unit.property', 'tenant', 'services'])
                ->where('id', $leaseId)
                ->where('agent_id', $user->id)
                ->where('status', 'active')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'lease' => $lease,
                'rent_amount' => $lease->rent_amount,
                'services' => $lease->services,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền truy cập.',
            ], 404);
        }
    }

}
