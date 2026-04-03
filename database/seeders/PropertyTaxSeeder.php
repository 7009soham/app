<?php

namespace Database\Seeders;

use App\Models\PropertyTaxRecord;
use App\Models\Citizen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the CSV file
        $csvFile = base_path('property_tax_csv_dummy_data.txt');
        
        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            return;
        }

        $this->command->info('Starting Property Tax import...');
        
        // Open and read CSV file
        $file = fopen($csvFile, 'r');
        
        // Skip header row
        $header = fgetcsv($file);
        
        $imported = 0;
        $errors = 0;
        $citizenMatches = 0;
        
        DB::beginTransaction();
        
        try {
            while (($row = fgetcsv($file)) !== false) {
                try {
                    // Parse CSV columns
                    $data = [
                        'sr_no' => $row[0] ?? null,
                        'property_no' => $row[1] ?? null,
                        'property_type' => $row[2] ?? 'RCC',
                        'property_holder_name' => $row[3] ?? null,
                        'previous_house_tax' => floatval($row[4] ?? 0),
                        'previous_electricity_tax' => floatval($row[5] ?? 0),
                        'previous_health_tax' => floatval($row[6] ?? 0),
                        'previous_total' => floatval($row[7] ?? 0),
                        'current_house_tax' => floatval($row[8] ?? 0),
                        'current_electricity_tax' => floatval($row[9] ?? 0),
                        'current_health_tax' => floatval($row[10] ?? 0),
                        'current_total' => floatval($row[11] ?? 0),
                        'mobile_no' => $row[12] ?? null,
                        'aadhaar_no' => $row[13] ?? null,
                    ];
                    
                    // Skip if essential data is missing
                    if (empty($data['property_no']) || empty($data['property_holder_name'])) {
                        continue;
                    }
                    
                    // Clean phone number (remove spaces, dashes)
                    $phone = preg_replace('/[^0-9]/', '', $data['mobile_no']);
                    
                    // Try to find citizen by phone number
                    $citizen = null;
                    if (!empty($phone) && strlen($phone) == 10) {
                        $citizen = Citizen::where('phone', $phone)->first();
                        if ($citizen) {
                            $citizenMatches++;
                            $this->command->info("✓ Matched property {$data['property_no']} to citizen: {$citizen->name} ({$phone})");
                        }
                    }
                    
                    // Calculate balance (current total - any payments made)
                    // For now, we'll use current_total as the balance
                    $balance = $data['current_total'];
                    
                    // Create property tax record
                    PropertyTaxRecord::create([
                        'a_no' => intval($data['sr_no']),
                        'customer_no' => $data['property_no'], // Using property_no as customer_no for uniqueness
                        'property_no' => $data['property_no'],
                        'property_type' => $data['property_type'],
                        'customer_name' => $data['property_holder_name'],
                        'previous_house_tax' => $data['previous_house_tax'],
                        'previous_electricity_tax' => $data['previous_electricity_tax'],
                        'previous_health_tax' => $data['previous_health_tax'],
                        'previous_total' => $data['previous_total'],
                        'current_house_tax' => $data['current_house_tax'],
                        'current_electricity_tax' => $data['current_electricity_tax'],
                        'current_health_tax' => $data['current_health_tax'],
                        'current_total' => $data['current_total'],
                        'balance' => $balance,
                        'phone' => !empty($phone) && strlen($phone) == 10 ? $phone : null,
                        'aadhaar_no' => $data['aadhaar_no'],
                        'citizen_id' => $citizen ? $citizen->id : null,
                    ]);
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors++;
                    $this->command->warn("Error importing row: " . $e->getMessage());
                    Log::error("Property Tax Import Error: " . $e->getMessage(), ['row' => $row]);
                }
            }
            
            DB::commit();
            fclose($file);
            
            $this->command->info("\n" . str_repeat('=', 60));
            $this->command->info("Property Tax Import Complete!");
            $this->command->info(str_repeat('=', 60));
            $this->command->info("✓ Total records imported: {$imported}");
            $this->command->info("✓ Matched to existing citizens: {$citizenMatches}");
            if ($errors > 0) {
                $this->command->warn("✗ Errors encountered: {$errors}");
            }
            $this->command->info(str_repeat('=', 60));
            
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            $this->command->error("Import failed: " . $e->getMessage());
            throw $e;
        }
    }
}
