<?php
header('Content-Type: text/plain');
$start = microtime(true);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$bootstrapTime = round((microtime(true) - $start) * 1000);
$dbStart = microtime(true);
$count = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as total FROM citas')[0]->total ?? 0;
$dbTime = round((microtime(true) - $dbStart) * 1000);
$totalTime = round((microtime(true) - $start) * 1000);
echo "=== Bench2 (con filament:optimize) ===\n\n";
echo "Laravel Bootstrap: {$bootstrapTime}ms\n";
echo "DB: {$dbTime}ms ({$count} citas)\n";
echo "TOTAL: {$totalTime}ms\n";
echo "OPcache: " . (opcache_get_status(false) ? 'ON' : 'OFF') . "\n";
$kernel->terminate($request, $response);
