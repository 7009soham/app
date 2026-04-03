<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\WaterTaxRecord::find(1);
if ($r) {
    $r->balance = 0;
    $r->save();
    echo "SUCCESS: Balanced record 1 to 0\n";
} else {
    echo "ERROR: Record 1 not found\n";
}
