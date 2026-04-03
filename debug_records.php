<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WaterTaxRecord;

$records = WaterTaxRecord::all();
$output = "Total Records: " . count($records) . "\n\n";

foreach ($records as $r) {
    $output .= "ID: " . $r->id . "\n";
    $output .= "Customer No: " . $r->customer_no . "\n";
    $output .= "Period: " . $r->period . "\n";
    $output .= "Monthly Bill: " . $r->monthly_bill . "\n";
    $output .= "Balance: " . $r->balance . "\n";
    $output .= "Other: " . $r->oversize_charge . "\n";
    $output .= "Paid: " . $r->amount_paid . "\n";
    $output .= "Citizen ID: " . $r->citizen_id . "\n";
    $output .= "------------------------\n";
}

file_put_contents('debug_records.txt', $output);
echo "Records saved to debug_records.txt\n";
