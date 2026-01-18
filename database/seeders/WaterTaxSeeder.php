<?php

namespace Database\Seeders;

use App\Models\Citizen;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use Illuminate\Database\Seeder;

class WaterTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waterTaxData = [
            ['a_no' => 1, 'customer_no' => '4686', 'customer_name' => 'Ambernath Jaihind Co.O.', 'monthly_bill' => 500, 'period' => null, 'balance' => 0, 'phone' => '9876500001'],
            ['a_no' => 2, 'customer_no' => '1068', 'customer_name' => 'Mahendra Kantilal Purohit', 'monthly_bill' => 125, 'period' => null, 'balance' => 0, 'phone' => '9876500002'],
            ['a_no' => 3, 'customer_no' => '1708', 'customer_name' => 'Mahendra Vitthaldas Shah', 'monthly_bill' => 500, 'period' => null, 'balance' => 0, 'phone' => '9876500003'],
            ['a_no' => 4, 'customer_no' => '4169', 'customer_name' => 'Milind Vasant Sane', 'monthly_bill' => 500, 'period' => null, 'balance' => 0, 'phone' => '9876500004'],
            ['a_no' => 5, 'customer_no' => '378', 'customer_name' => 'Sudhir Vasant Sane', 'monthly_bill' => 250, 'period' => null, 'balance' => 0, 'phone' => '9876500005'],
            ['a_no' => 6, 'customer_no' => '385', 'customer_name' => 'Vivek Madhukar Bhadsawale', 'monthly_bill' => 125, 'period' => 'July 23–March 24', 'balance' => 800, 'phone' => '9876500006'],
            ['a_no' => 7, 'customer_no' => '209', 'customer_name' => 'Keshav Vaman Bhadsawale', 'monthly_bill' => 125, 'period' => null, 'balance' => 0, 'phone' => '9876500007'],
            ['a_no' => 8, 'customer_no' => '91', 'customer_name' => 'Kamlakar Moreshwar Bhadsawale', 'monthly_bill' => 100, 'period' => null, 'balance' => 0, 'phone' => '9876500008'],
            ['a_no' => 9, 'customer_no' => '4014', 'customer_name' => 'Sooman Tribank Bhadsawale (D.P.)', 'monthly_bill' => 100, 'period' => 'Apr 16–Mar 24', 'balance' => 8240, 'phone' => '9876500009'],
            ['a_no' => 10, 'customer_no' => '4015', 'customer_name' => 'Sudhir Govind Bhadsawle (D.P.)', 'monthly_bill' => 100, 'period' => 'Apr 20–Mar 24', 'balance' => 4300, 'phone' => '9876500010'],
            ['a_no' => 11, 'customer_no' => '81', 'customer_name' => 'Hari Govind Bhadsawale (DP)', 'monthly_bill' => 100, 'period' => 'Apr 16–Mar 24', 'balance' => 8526, 'phone' => '9876500011'],
            ['a_no' => 12, 'customer_no' => '4016', 'customer_name' => 'Milind Yashwant Bhadsawle (DP)', 'monthly_bill' => 0, 'period' => 'Jun 16–Mar 24', 'balance' => 8162, 'phone' => '9876500012'],
            ['a_no' => 13, 'customer_no' => '320', 'customer_name' => 'Madhavi Ramachandra Bhadsawale', 'monthly_bill' => 100, 'period' => null, 'balance' => 0, 'phone' => '9876500013'],
            ['a_no' => 14, 'customer_no' => '330', 'customer_name' => 'Aarti Deepak Bhadsawale (G.P.)', 'monthly_bill' => 100, 'period' => 'Apr 15–Mar 24', 'balance' => 9296, 'phone' => '9876500014'],
            ['a_no' => 15, 'customer_no' => '960', 'customer_name' => 'Ujwala Shankar Bhadsawale', 'monthly_bill' => 100, 'period' => null, 'balance' => 0, 'phone' => '9876500015'],
            ['a_no' => 16, 'customer_no' => '367', 'customer_name' => 'Prabhakar Krishna Ratnaparkhi', 'monthly_bill' => 100, 'period' => 'Apr 15–Mar 24', 'balance' => 9296, 'phone' => '9876500016'],
            ['a_no' => 17, 'customer_no' => '466', 'customer_name' => 'Vivek Madhukar Bhadsawale', 'monthly_bill' => 100, 'period' => null, 'balance' => 0, 'phone' => '9876500017'],
            ['a_no' => 18, 'customer_no' => '319', 'customer_name' => 'Vivek Madhukar Bhadsawale', 'monthly_bill' => 125, 'period' => null, 'balance' => 0, 'phone' => '9876500018'],
            ['a_no' => 19, 'customer_no' => '1112', 'customer_name' => 'Pramod Sridhar Bhadsawale', 'monthly_bill' => 100, 'period' => null, 'balance' => 0, 'phone' => '9876500019'],
            ['a_no' => 20, 'customer_no' => '118', 'customer_name' => 'Ramilabai Dyalal Shah (B.M.)', 'monthly_bill' => 125, 'period' => 'Back to Mar 24', 'balance' => 14575, 'phone' => '9876500020'],
            ['a_no' => 21, 'customer_no' => '449', 'customer_name' => 'Jayantilal Vadilal Shah (D.B.M.)', 'monthly_bill' => 125, 'period' => 'Back to Mar 24', 'balance' => 14150, 'phone' => '9876500021'],
        ];

        foreach ($waterTaxData as $data) {
            // Create or find citizen
            $citizen = Citizen::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'customer_no' => $data['customer_no'],
                    'name' => $data['customer_name'],
                ]
            );

            // Create water tax record
            WaterTaxRecord::updateOrCreate(
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

        $this->command->info('Water tax records seeded successfully!');
    }
}
