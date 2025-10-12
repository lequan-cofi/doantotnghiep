<?php

namespace App\Observers;

use App\Models\LeaseService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Log;

class LeaseServiceObserver
{
    /**
     * Handle the LeaseService "updated" event.
     */
    public function updated(LeaseService $leaseService)
    {
        // Only update invoices if price changed
        if ($leaseService->isDirty(['price', 'status'])) {
            $this->updateRelatedInvoices($leaseService);
        }
    }

    /**
     * Handle the LeaseService "deleted" event.
     */
    public function deleted(LeaseService $leaseService)
    {
        // When lease service is deleted, remove related invoice items
        $this->removeServiceFromInvoices($leaseService);
    }

    /**
     * Update related invoices when lease service changes
     */
    private function updateRelatedInvoices(LeaseService $leaseService)
    {
        try {
            // Get all unpaid invoices for this lease
            $invoices = Invoice::where('lease_id', $leaseService->lease_id)
                ->where('status', '!=', 'paid')
                ->get();

            foreach ($invoices as $invoice) {
                $this->updateInvoiceFromService($invoice, $leaseService);
            }

            Log::info('Invoices automatically updated due to lease service changes', [
                'lease_service_id' => $leaseService->id,
                'service_name' => $leaseService->service->name ?? 'Unknown',
                'updated_invoices' => $invoices->pluck('id')->toArray(),
                'changes' => $leaseService->getDirty()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating invoices from lease service changes: ' . $e->getMessage(), [
                'lease_service_id' => $leaseService->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update individual invoice from lease service changes
     */
    private function updateInvoiceFromService(Invoice $invoice, LeaseService $leaseService)
    {
        // Find invoice items related to this service
        $serviceItems = $invoice->items()
            ->where('description', 'like', '%' . ($leaseService->service->name ?? '') . '%')
            ->get();

        foreach ($serviceItems as $item) {
            if ($leaseService->isDirty('price')) {
                $item->update([
                    'unit_price' => $leaseService->price,
                    'amount' => $item->quantity * $leaseService->price,
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
     * Remove service from invoices when service is deleted
     */
    private function removeServiceFromInvoices(LeaseService $leaseService)
    {
        try {
            // Get all unpaid invoices for this lease
            $invoices = Invoice::where('lease_id', $leaseService->lease_id)
                ->where('status', '!=', 'paid')
                ->get();

            foreach ($invoices as $invoice) {
                // Remove invoice items related to this service
                $removedItems = $invoice->items()
                    ->where('description', 'like', '%' . ($leaseService->service->name ?? '') . '%')
                    ->delete();

                if ($removedItems > 0) {
                    // Recalculate invoice totals
                    $subtotal = $invoice->items()->sum('amount');
                    $invoice->update([
                        'subtotal' => $subtotal,
                        'total_amount' => $subtotal + $invoice->tax_amount - $invoice->discount_amount,
                    ]);
                }
            }

            Log::info('Service removed from invoices due to lease service deletion', [
                'lease_service_id' => $leaseService->id,
                'service_name' => $leaseService->service->name ?? 'Unknown',
                'affected_invoices' => $invoices->pluck('id')->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing service from invoices: ' . $e->getMessage(), [
                'lease_service_id' => $leaseService->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}
