<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\ProductionRecord::latest()->first();
echo "Record ID: {$r->id}\n";
echo "Machine ID: '{$r->machine_id}'\n";
echo "Operation ID: '{$r->operation_id}'\n";

$prostredek = App\Models\Prostredek::where('KlicProstredku', $r->machine_id)->first();
echo "Prostredek: " . ($prostredek ? trim($prostredek->KlicProstredku) : 'NULL') . "\n";

$operace = App\Models\Operace::withoutGlobalScopes()->where('KlicPoloz', $r->operation_id)->first();
echo "Operace (without scopes): " . ($operace ? trim($operace->KlicPoloz) : 'NULL') . "\n";

$operace2 = App\Models\Operace::where('KlicPoloz', $r->operation_id)->first();
echo "Operace (with scopes): " . ($operace2 ? trim($operace2->KlicPoloz) : 'NULL') . "\n";
