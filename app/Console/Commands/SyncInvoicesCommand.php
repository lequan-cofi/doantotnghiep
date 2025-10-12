<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceSyncService;
use App\Models\Invoice;

class SyncInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sync 
                            {--all : Sync all pending invoices}
                            {--invoice-id= : Sync specific invoice by ID}
                            {--force : Force sync even if invoice is paid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invoices with related data changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceSyncService = new InvoiceSyncService();
        
        if ($this->option('invoice-id')) {
            return $this->syncSpecificInvoice($invoiceSyncService);
        }
        
        if ($this->option('all')) {
            return $this->syncAllInvoices($invoiceSyncService);
        }
        
        $this->info('Invoice Sync Command');
        $this->line('Available options:');
        $this->line('  --all              Sync all pending invoices');
        $this->line('  --invoice-id=ID    Sync specific invoice');
        $this->line('  --force            Force sync even if paid');
        
        return 0;
    }

    /**
     * Sync specific invoice
     */
    private function syncSpecificInvoice(InvoiceSyncService $service)
    {
        $invoiceId = $this->option('invoice-id');
        $force = $this->option('force');
        
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            $this->error("Invoice with ID {$invoiceId} not found.");
            return 1;
        }
        
        if (!$force && in_array($invoice->status, ['paid', 'cancelled'])) {
            $this->warn("Invoice {$invoiceId} is {$invoice->status}. Use --force to sync anyway.");
            return 1;
        }
        
        $this->info("Syncing invoice {$invoiceId}...");
        
        if ($service->recalculateInvoiceTotals($invoice)) {
            $this->info("✓ Invoice {$invoiceId} synced successfully.");
            return 0;
        } else {
            $this->error("✗ Failed to sync invoice {$invoiceId}.");
            return 1;
        }
    }

    /**
     * Sync all invoices
     */
    private function syncAllInvoices(InvoiceSyncService $service)
    {
        $this->info('Syncing all pending invoices...');
        
        $syncedCount = $service->syncAllPendingInvoices();
        
        if ($syncedCount > 0) {
            $this->info("✓ Successfully synced {$syncedCount} invoices.");
        } else {
            $this->warn('No invoices were synced.');
        }
        
        return 0;
    }
}
