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
        Log::info('LeaseObserver::created triggered', [
            'lease_id' => $lease->id,
            'rent_amount' => $lease->rent_amount,
            'deposit_amount' => $lease->deposit_amount,
            'status' => $lease->status
        ]);
        
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
        
        // Create commission events and invoices if status changed from draft to active
        if ($lease->isDirty('status') && $lease->status === 'active') {
            $originalStatus = $lease->getOriginal('status');
            if ($originalStatus === 'draft') {
                Log::info('Lease status changed from draft to active, creating commission events and invoices', [
                    'lease_id' => $lease->id,
                    'original_status' => $originalStatus,
                    'new_status' => $lease->status
                ]);
                $this->createCommissionEvents($lease);
                $this->createFirstMonthRentInvoice($lease);
            }
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
            Log::info('LeaseObserver::createFirstMonthRentInvoice started', [
                'lease_id' => $lease->id,
                'rent_amount' => $lease->rent_amount,
                'deposit_amount' => $lease->deposit_amount
            ]);
            
            // Skip if lease doesn't have rent amount
            if (!$lease->rent_amount || $lease->rent_amount <= 0) {
                Log::info('Skipping invoice creation - no rent amount', [
                    'lease_id' => $lease->id,
                    'rent_amount' => $lease->rent_amount
                ]);
                return;
            }

            // Check if first payment cycle invoice already exists
            $existingInvoice = Invoice::where('lease_id', $lease->id)
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', function($query) {
                    $query->where('description', 'like', '%chu kỳ đầu%')
                          ->orWhere('description', 'like', '%tháng đầu%');
                })
                ->first();

            if ($existingInvoice) {
                Log::info('Invoice already exists, skipping creation', [
                    'lease_id' => $lease->id,
                    'existing_invoice_id' => $existingInvoice->id
                ]);
                return; // Already exists
            }

            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();
            
            // Get unit and property information
            $unit = $lease->unit;
            $property = $unit->property;
            
            // Calculate payment cycle details
            $cycleInfo = $this->calculatePaymentCycle($lease);
            
            // Calculate dates
            $issueDate = $lease->start_date;
            $dueDate = $this->calculateDueDate($lease, $issueDate);
            
            // Calculate total amount
            $rentTotal = $cycleInfo['rent_total'];
            $depositAmount = $lease->deposit_amount ?? 0;
            $totalAmount = $rentTotal + $depositAmount;
            
            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $lease->organization_id,
                'is_auto_created' => true,
                'lease_id' => $lease->id,
                'invoice_no' => $invoiceNumber,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'draft',
                'subtotal' => $totalAmount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'currency' => 'VND',
                'note' => "Hóa đơn chu kỳ đầu cho {$property->name} - {$unit->code} ({$cycleInfo['description']})",
            ]);
            
            // Create invoice item for rent (payment cycle)
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'rent',
                'description' => $cycleInfo['description'] . " - {$property->name} - {$unit->code}",
                'quantity' => $cycleInfo['months'],
                'unit_price' => $lease->rent_amount,
                'amount' => $rentTotal,
            ]);
            
            // Create invoice item for deposit if exists
            if ($depositAmount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'deposit',
                    'description' => "Tiền cọc - {$property->name} - {$unit->code}",
                    'quantity' => 1,
                    'unit_price' => $depositAmount,
                    'amount' => $depositAmount,
                ]);
            }
            
            Log::info('First payment cycle invoice created automatically for lease', [
                'lease_id' => $lease->id,
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoiceNumber,
                'payment_cycle' => $lease->lease_payment_cycle,
                'months' => $cycleInfo['months'],
                'rent_total' => $rentTotal,
                'deposit_amount' => $depositAmount,
                'total_amount' => $totalAmount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating first month rent invoice for lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'rent_amount' => $lease->rent_amount,
                'deposit_amount' => $lease->deposit_amount,
                'error' => $e->getTraceAsString()
            ]);
            
            // Don't re-throw the exception to prevent breaking the lease creation process
            // The invoice can be created manually later if needed
        }
    }
    
    /**
     * Manually create invoice for existing lease (for fixing missing invoices)
     */
    public static function createInvoiceForExistingLease(Lease $lease)
    {
        $observer = new self(app(\App\Services\CommissionEventService::class));
        return $observer->createFirstMonthRentInvoice($lease);
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

            // Check if commission events already exist for this lease
            $existingEvents = \App\Models\CommissionEvent::where('lease_id', $lease->id)->count();
            if ($existingEvents > 0) {
                Log::info('Commission events already exist for lease, skipping creation', [
                    'lease_id' => $lease->id,
                    'existing_events_count' => $existingEvents
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

    /**
     * Calculate payment cycle information
     */
    private function calculatePaymentCycle(Lease $lease)
    {
        $paymentCycle = $lease->lease_payment_cycle;
        $customMonths = $lease->lease_custom_months;
        
        switch ($paymentCycle) {
            case 'monthly':
                return [
                    'months' => 1,
                    'rent_total' => $lease->rent_amount,
                    'description' => 'Tiền thuê tháng đầu'
                ];
                
            case 'quarterly':
                return [
                    'months' => 3,
                    'rent_total' => $lease->rent_amount * 3,
                    'description' => 'Tiền thuê quý đầu (3 tháng)'
                ];
                
            case 'yearly':
                return [
                    'months' => 12,
                    'rent_total' => $lease->rent_amount * 12,
                    'description' => 'Tiền thuê năm đầu (12 tháng)'
                ];
                
            case 'custom':
                $months = $customMonths ?? 1;
                return [
                    'months' => $months,
                    'rent_total' => $lease->rent_amount * $months,
                    'description' => "Tiền thuê chu kỳ đầu ({$months} tháng)"
                ];
                
            default:
                // Default to monthly if no payment cycle set
                return [
                    'months' => 1,
                    'rent_total' => $lease->rent_amount,
                    'description' => 'Tiền thuê tháng đầu'
                ];
        }
    }

    /**
     * Calculate due date based on payment cycle
     */
    private function calculateDueDate(Lease $lease, $issueDate)
    {
        $paymentDay = $lease->lease_payment_day ?? 1;
        $paymentCycle = $lease->lease_payment_cycle;
        
        // If no payment cycle set, default to 30 days
        if (!$paymentCycle) {
            return $issueDate->copy()->addDays(30);
        }
        
        // Calculate due date based on payment day
        $dueDate = $issueDate->copy();
        
        // Set to the payment day of the current month
        $dueDate->day($paymentDay);
        
        // If payment day has passed in current month, move to next month
        if ($dueDate->lt($issueDate)) {
            $dueDate->addMonth();
        }
        
        return $dueDate;
    }
}
