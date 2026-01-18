<?php

namespace Database\Seeders;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use Illuminate\Database\Seeder;

class PropertyTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Note: Same schema as water tax. Adding sample data for demonstration.
     */
    public function run(): void
    {
        // Sample property tax data with same schema as water tax
        // In production, this would be imported from actual property tax records
        $propertyTaxData = [
            ['a_no' => 1, 'customer_no' => '4686', 'customer_name' => 'Ambernath Jaihind Co.O.', 'monthly_bill' => 1200, 'period' => null, 'balance' => 0, 'phone' => '9876500001'],
            ['a_no' => 2, 'customer_no' => '1068', 'customer_name' => 'Mahendra Kantilal Purohit', 'monthly_bill' => 800, 'period' => null, 'balance' => 2400, 'phone' => '9876500002'],
            ['a_no' => 3, 'customer_no' => '1708', 'customer_name' => 'Mahendra Vitthaldas Shah', 'monthly_bill' => 1500, 'period' => null, 'balance' => 0, 'phone' => '9876500003'],
            ['a_no' => 4, 'customer_no' => '4169', 'customer_name' => 'Milind Vasant Sane', 'monthly_bill' => 1000, 'period' => 'Apr 23–Mar 24', 'balance' => 12000, 'phone' => '9876500004'],
            ['a_no' => 5, 'customer_no' => '378', 'customer_name' => 'Sudhir Vasant Sane', 'monthly_bill' => 600, 'period' => null, 'balance' => 0, 'phone' => '9876500005'],
            ['a_no' => 6, 'customer_no' => '385', 'customer_name' => 'Vivek Madhukar Bhadsawale', 'monthly_bill' => 750, 'period' => 'July 23–March 24', 'balance' => 5625, 'phone' => '9876500006'],
            ['a_no' => 7, 'customer_no' => '209', 'customer_name' => 'Keshav Vaman Bhadsawale', 'monthly_bill' => 500, 'period' => null, 'balance' => 0, 'phone' => '9876500007'],
            ['a_no' => 8, 'customer_no' => '91', 'customer_name' => 'Kamlakar Moreshwar Bhadsawale', 'monthly_bill' => 450, 'period' => null, 'balance' => 1350, 'phone' => '9876500008'],
        ];

        foreach ($propertyTaxData as $data) {
            // Find citizen (should already exist from water tax seeder)
            $citizen = Citizen::where('phone', $data['phone'])->first();

            if (!$citizen) {
                $citizen = Citizen::create([
                    'customer_no' => $data['customer_no'],
                    'name' => $data['customer_name'],
                    'phone' => $data['phone'],
                ]);
            }

            // Create property tax record
            PropertyTaxRecord::updateOrCreate(
                [
                    'customer_no' => $data['customer_no'],
                    'a_no' => $data['a_no'],
                ],
                [
                    'customer_name' => $data['customer_name'],
                    'monthly_bill' => $data['monthly_bill'],
                    'period' => $data['period'],
                    'balance' => $data['balance'],
                    'oversize_charge' => $data['balance'] > 0 ? $data['balance'] * 0.10 : 0,
                    'phone' => $data['phone'],
                    'citizen_id' => $citizen->id,
                ]
            );
        }

        $this->command->info('Property tax records seeded successfully!');
    }
}
