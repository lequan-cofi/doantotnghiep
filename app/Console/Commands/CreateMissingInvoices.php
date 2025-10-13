<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Observers\LeaseObserver;
use Illuminate\Support\Facades\Log;

class CreateMissingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:create-missing {--dry-run : Show what would be done without actually creating invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing invoices for leases that don\'t have any invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no invoices will be created');
        }
        
        $this->info('Checking for leases without invoices...');
        
        $leases = Lease::with('invoices')->get();
        $missingInvoices = [];
        
        foreach ($leases as $lease) {
            if ($lease->invoices->count() == 0) {
                $missingInvoices[] = $lease;
            }
        }
        
        $this->info("Found " . count($missingInvoices) . " leases without invoices.");
        
        if (count($missingInvoices) == 0) {
            $this->info('All leases have invoices. Nothing to do.');
            return 0;
        }
        
        $this->table(
            ['Lease ID', 'Rent Amount', 'Deposit Amount', 'Status', 'Created At'],
            collect($missingInvoices)->map(function($lease) {
                return [
                    $lease->id,
                    number_format($lease->rent_amount, 0, ',', '.') . ' VNĐ',
                    number_format($lease->deposit_amount, 0, ',', '.') . ' VNĐ',
                    $lease->status,
                    $lease->created_at->format('Y-m-d H:i:s')
                ];
            })
        );
        
        if ($isDryRun) {
            $this->info('DRY RUN completed. Use without --dry-run to actually create invoices.');
            return 0;
        }
        
        if (!$this->confirm('Do you want to create invoices for these leases?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $this->info('Creating invoices...');
        $progressBar = $this->output->createProgressBar(count($missingInvoices));
        $progressBar->start();
        
        $created = 0;
        $errors = 0;
        
        foreach ($missingInvoices as $lease) {
            try {
                if ($lease->rent_amount && $lease->rent_amount > 0) {
                    // Use the same logic as LeaseObserver
                    $this->createInvoiceForLease($lease);
                    $created++;
                } else {
                    $this->warn("Skipping lease {$lease->id} - no rent amount");
                }
            } catch (\Exception $e) {
                $this->error("Error creating invoice for lease {$lease->id}: " . $e->getMessage());
                $errors++;
                Log::error('Error creating invoice via command', [
                    'lease_id' => $lease->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Completed! Created {$created} invoices, {$errors} errors.");
        
        return 0;
    }
    
    /**
     * Create invoice for a lease using the same logic as LeaseObserver
     */
    private function createInvoiceForLease(Lease $lease)
    {
        // Generate invoice number
        $invoiceNumber = Invoice::generateInvoiceNumber();
        
        // Get unit and property information
        $unit = $lease->unit;
        $property = $unit->property;
        
        // Calculate dates
        $issueDate = $lease->start_date;
        $dueDate = $lease->start_date->copy()->addDays(30);
        
        // Create invoice
        $invoice = Invoice::create([
            'organization_id' => $lease->organization_id,
            'is_auto_created' => true,
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
        
        Log::info('Invoice created via command', [
            'lease_id' => $lease->id,
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoiceNumber,
            'total_amount' => $invoice->total_amount
        ]);
    }
}