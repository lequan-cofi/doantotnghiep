<?php

namespace App\Observers;

use App\Models\Lease;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\CommissionEventService;
use Illuminate\Support\Facades\Log;

class LeaseObserver
{
    protected $commissionEventService;

    public function __construct(CommissionEventService $commissionEventService)
    {
        $this->commissionEventService = $commissionEventService;
    }
    /**
     * Handle the Lease "created" event.
     */
    public function created(Lease $lease)
    {
        // Automatically create first month rent invoice when lease is created
        $this->createFirstMonthRentInvoice($lease);
        
        // Automatically create commission events when lease is created
        $this->createCommissionEvents($lease);
    }

    /**
     * Handle the Lease "updated" event.
     */
    public function updated(Lease $lease)
    {
        // Only update invoices if rent_amount or deposit_amount changed
        if ($lease->isDirty(['rent_amount', 'deposit_amount', 'status'])) {
            $this->updateRelatedInvoices($lease);
        }
        
        // Update commission events if relevant fields changed
        if ($lease->isDirty(['rent_amount', 'deposit_amount'])) {
            $this->updateCommissionEvents($lease);
        }
    }

    /**
     * Handle the Lease "deleted" event.
     */
    public function deleted(Lease $lease)
    {
        // When lease is deleted, cancel all related unpaid invoices
        $invoices = Invoice::where('lease_id', $lease->id)
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update([
                'status' => 'cancelled',
                'note' => $invoice->note . "\n[Hủy tự động do hợp đồng bị xóa]"
            ]);
        }

        if ($invoices->count() > 0) {
            Log::info('Invoices automatically cancelled due to lease deletion', [
                'lease_id' => $lease->id,
                'cancelled_invoices' => $invoices->pluck('id')->toArray()
            ]);
        }
        
        // Delete commission events when lease is deleted
        $this->deleteCommissionEvents($lease);
    }

    /**
     * Create first month rent invoice automatically for lease
     */
    private function createFirstMonthRentInvoice(Lease $lease)
    {
        try {
            // Skip if lease doesn't have rent amount
            if (!$lease->rent_amount || $lease->rent_amount <= 0) {
                return;
            }

            // Check if first month invoice already exists
            $existingInvoice = Invoice::where('lease_id', $lease->id)
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', function($query) {
                    $query->where('description', 'like', '%tiền thuê tháng đầu%');
                })
                ->first();

            if ($existingInvoice) {
                return; // Already exists
            }

            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Get unit and property information
            $unit = $lease->unit;
            $property = $unit->property;
            
            // Calculate dates
            $issueDate = $lease->start_date;
            $dueDate = $lease->start_date->copy()->addDays(30); // 30 days from start date
            
            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $lease->organization_id,
                'lease_id' => $lease->id,
                'invoice_no' => $invoiceNumber,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'draft',
                'subtotal' => $lease->rent_amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $lease->rent_amount,
                'currency' => 'VND',
                'note' => "Hóa đơn tiền thuê tháng đầu cho {$property->name} - {$unit->code}",
            ]);
            
            // Create invoice item for rent
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'rent',
                'description' => "Tiền thuê tháng đầu - {$property->name} - {$unit->code}",
                'quantity' => 1,
                'unit_price' => $lease->rent_amount,
                'amount' => $lease->rent_amount,
            ]);
            
            // Create invoice item for deposit if exists
            if ($lease->deposit_amount && $lease->deposit_amount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'deposit',
                    'description' => "Tiền cọc - {$property->name} - {$unit->code}",
                    'quantity' => 1,
                    'unit_price' => $lease->deposit_amount,
                    'amount' => $lease->deposit_amount,
                ]);
                
                // Update invoice totals
                $totalAmount = $lease->rent_amount + $lease->deposit_amount;
                $invoice->update([
                    'subtotal' => $totalAmount,
                    'total_amount' => $totalAmount,
                ]);
            }
            
            Log::info('First month rent invoice created automatically for lease', [
                'lease_id' => $lease->id,
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoiceNumber,
                'rent_amount' => $lease->rent_amount,
                'deposit_amount' => $lease->deposit_amount ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating first month rent invoice for lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update related invoices when lease changes
     */
    private function updateRelatedInvoices(Lease $lease)
    {
        try {
            // Get all unpaid invoices for this lease
            $invoices = Invoice::where('lease_id', $lease->id)
                ->where('status', '!=', 'paid')
                ->get();

            foreach ($invoices as $invoice) {
                $this->updateInvoiceFromLease($invoice, $lease);
            }

            Log::info('Invoices automatically updated due to lease changes', [
                'lease_id' => $lease->id,
                'updated_invoices' => $invoices->pluck('id')->toArray(),
                'changes' => $lease->getDirty()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating invoices from lease changes: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update individual invoice from lease changes
     */
    private function updateInvoiceFromLease(Invoice $invoice, Lease $lease)
    {
        // Update rent-related invoice items
        $rentItems = $invoice->items()->where('description', 'like', '%tiền thuê%')->get();
        foreach ($rentItems as $item) {
            if ($lease->isDirty('rent_amount')) {
                $item->update([
                    'unit_price' => $lease->rent_amount,
                    'amount' => $lease->rent_amount,
                ]);
            }
        }

        // Update deposit-related invoice items
        $depositItems = $invoice->items()->where('description', 'like', '%cọc%')->get();
        foreach ($depositItems as $item) {
            if ($lease->isDirty('deposit_amount')) {
                $item->update([
                    'unit_price' => $lease->deposit_amount,
                    'amount' => $lease->deposit_amount,
                ]);
            }
        }

        // Recalculate invoice totals
        $subtotal = $invoice->items()->sum('amount');
        $invoice->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + $invoice->tax_amount - $invoice->discount_amount,
        ]);
    }

    /**
     * Create commission events for lease
     */
    private function createCommissionEvents(Lease $lease)
    {
        try {
            // Only create commission events for active leases
            if ($lease->status !== 'active') {
                Log::info('Lease not active, skipping commission events creation', [
                    'lease_id' => $lease->id,
                    'status' => $lease->status
                ]);
                return;
            }

            $result = $this->commissionEventService->createCommissionEventsForLease($lease);
            
            if ($result) {
                Log::info('Commission events created successfully via LeaseObserver', [
                    'lease_id' => $lease->id,
                    'created_events_count' => count($result)
                ]);
            } else {
                Log::warning('Failed to create commission events via LeaseObserver', [
                    'lease_id' => $lease->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error creating commission events in LeaseObserver: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update commission events for lease
     */
    private function updateCommissionEvents(Lease $lease)
    {
        try {
            $result = $this->commissionEventService->updateCommissionEventsForLease($lease);
            
            if ($result) {
                Log::info('Commission events updated successfully via LeaseObserver', [
                    'lease_id' => $lease->id
                ]);
            } else {
                Log::warning('Failed to update commission events via LeaseObserver', [
                    'lease_id' => $lease->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating commission events in LeaseObserver: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Delete commission events for lease
     */
    private function deleteCommissionEvents(Lease $lease)
    {
        try {
            $result = $this->commissionEventService->deleteCommissionEventsForLease($lease);
            
            if ($result) {
                Log::info('Commission events deleted successfully via LeaseObserver', [
                    'lease_id' => $lease->id
                ]);
            } else {
                Log::warning('Failed to delete commission events via LeaseObserver', [
                    'lease_id' => $lease->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error deleting commission events in LeaseObserver: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}
