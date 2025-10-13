<?php

namespace App\Observers;

use App\Models\BookingDeposit;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\CommissionEventService;
use Illuminate\Support\Facades\Log;

class BookingDepositObserver
{
    protected $commissionEventService;

    public function __construct(CommissionEventService $commissionEventService)
    {
        $this->commissionEventService = $commissionEventService;
    }
    /**
     * Handle the BookingDeposit "created" event.
     */
    public function created(BookingDeposit $bookingDeposit)
    {
        // Automatically create invoice when booking deposit is created
        $this->createInvoiceForBookingDeposit($bookingDeposit);
    }

    /**
     * Handle the BookingDeposit "updated" event.
     */
    public function updated(BookingDeposit $bookingDeposit)
    {
        // Only update invoice if amount, deposit_type, or hold_until changed
        if ($bookingDeposit->isDirty(['amount', 'deposit_type', 'hold_until', 'notes'])) {
            $this->updateRelatedInvoice($bookingDeposit);
        }
        
        // Commission events for booking deposits are no longer needed
        // Viewing commission events are handled by ViewingObserver
    }

    /**
     * Handle the BookingDeposit "deleted" event.
     */
    public function deleted(BookingDeposit $bookingDeposit)
    {
        // When booking deposit is deleted, cancel related invoice
        if ($bookingDeposit->invoice_id) {
            $invoice = Invoice::find($bookingDeposit->invoice_id);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update([
                    'status' => 'cancelled',
                    'note' => $invoice->note . "\n[Hủy tự động do đặt cọc bị xóa]"
                ]);
                
                Log::info('Invoice automatically cancelled due to booking deposit deletion', [
                    'booking_deposit_id' => $bookingDeposit->id,
                    'invoice_id' => $invoice->id
                ]);
            }
        }
    }

    /**
     * Create invoice automatically for booking deposit
     */
    private function createInvoiceForBookingDeposit(BookingDeposit $bookingDeposit)
    {
        try {
            // Skip if invoice already exists
            if ($bookingDeposit->invoice_id) {
                return;
            }

            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();
            
            // Get unit and property information
            $unit = $bookingDeposit->unit;
            $property = $unit->property;
            
            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $bookingDeposit->organization_id,
                'is_auto_created' => true,
                'booking_deposit_id' => $bookingDeposit->id,
                'invoice_no' => $invoiceNumber,
                'issue_date' => now(),
                'due_date' => $bookingDeposit->hold_until,
                'status' => 'draft',
                'subtotal' => $bookingDeposit->amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $bookingDeposit->amount,
                'currency' => 'VND',
                'note' => "Hóa đơn đặt cọc cho {$property->name} - {$unit->code}. Loại: " . ucfirst($bookingDeposit->deposit_type),
            ]);
            
            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'deposit',
                'description' => "Đặt cọc {$bookingDeposit->deposit_type} - {$property->name} - {$unit->code}",
                'quantity' => 1,
                'unit_price' => $bookingDeposit->amount,
                'amount' => $bookingDeposit->amount,
            ]);
            
            // Update booking deposit with invoice ID
            $bookingDeposit->update(['invoice_id' => $invoice->id]);
            
            Log::info('Invoice created automatically for booking deposit', [
                'booking_deposit_id' => $bookingDeposit->id,
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoiceNumber,
                'amount' => $bookingDeposit->amount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating invoice for booking deposit: ' . $e->getMessage(), [
                'booking_deposit_id' => $bookingDeposit->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update related invoice when booking deposit changes
     */
    private function updateRelatedInvoice(BookingDeposit $bookingDeposit)
    {
        if (!$bookingDeposit->invoice_id) {
            return;
        }

        try {
            $invoice = Invoice::find($bookingDeposit->invoice_id);
            if (!$invoice) {
                return;
            }

            // Update invoice details
            $invoice->update([
                'due_date' => $bookingDeposit->hold_until,
                'subtotal' => $bookingDeposit->amount,
                'total_amount' => $bookingDeposit->amount,
                'note' => "Hóa đơn đặt cọc cho {$bookingDeposit->unit->property->name} - {$bookingDeposit->unit->code}. Loại: " . ucfirst($bookingDeposit->deposit_type),
            ]);

            // Update invoice item
            $invoiceItem = $invoice->items()->first();
            if ($invoiceItem) {
                $invoiceItem->update([
                    'description' => "Đặt cọc {$bookingDeposit->deposit_type} - {$bookingDeposit->unit->property->name} - {$bookingDeposit->unit->code}",
                    'unit_price' => $bookingDeposit->amount,
                    'amount' => $bookingDeposit->amount,
                ]);
            }

            Log::info('Invoice automatically updated due to booking deposit changes', [
                'booking_deposit_id' => $bookingDeposit->id,
                'invoice_id' => $invoice->id,
                'changes' => $bookingDeposit->getDirty()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating invoice from booking deposit changes: ' . $e->getMessage(), [
                'booking_deposit_id' => $bookingDeposit->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    // Commission events logic moved to ViewingObserver for viewing_done trigger
}
