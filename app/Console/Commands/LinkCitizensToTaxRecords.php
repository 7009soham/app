<?php

namespace App\Console\Commands;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\MonthlyTaxBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkCitizensToTaxRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'citizens:link-tax-records';

    /**
     * The console command description.
     */
    protected $description = 'Automatically link citizens to their property tax and water tax records based on phone numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic citizen-tax linking...');
        $this->newLine();

        // Link Property Tax Records
        $this->info('🏠 Linking Property Tax Records...');
        $propertyLinked = $this->linkPropertyTaxRecords();
        
        // Link Water Tax Bills
        $this->info('💧 Linking Water Tax Bills...');
        $waterLinked = $this->linkWaterTaxBills();

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('✅ Linking Complete!');
        $this->info('═══════════════════════════════════════');
        $this->info("🏠 Property Tax Records Linked: {$propertyLinked}");
        $this->info("💧 Water Tax Bills Linked: {$waterLinked}");
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }

    /**
     * Link property tax records to citizens
     */
    private function linkPropertyTaxRecords(): int
    {
        $linked = 0;
        
        // Get all property tax records that have a phone but no citizen_id
        $records = PropertyTaxRecord::whereNotNull('phone')
            ->whereNull('citizen_id')
            ->get();

        foreach ($records as $record) {
            // Find citizen with matching phone
            $citizen = Citizen::where('phone', $record->phone)->first();
            
            if ($citizen) {
                $record->citizen_id = $citizen->id;
                $record->save();
                
                $this->line("  ✓ Linked property {$record->property_no} to {$citizen->name} ({$citizen->phone})");
                $linked++;
            } else {
                $this->warn("  ✗ No citizen found for phone: {$record->phone} (Property: {$record->property_no})");
            }
        }

        return $linked;
    }

    /**
     * Link water tax bills to citizens
     */
    private function linkWaterTaxBills(): int
    {
        $linked = 0;
        
        // Get all water tax bills that have a phone but no citizen_id
        $bills = MonthlyTaxBill::whereNotNull('phone')
            ->whereNull('citizen_id')
            ->get();

        foreach ($bills as $bill) {
            // Find citizen with matching phone
            $citizen = Citizen::where('phone', $bill->phone)->first();
            
            if ($citizen) {
                $bill->citizen_id = $citizen->id;
                $bill->save();
                
                $this->line("  ✓ Linked bill {$bill->customer_no} ({$bill->month_name} {$bill->bill_year}) to {$citizen->name}");
                $linked++;
            }
        }

        return $linked;
    }
}
