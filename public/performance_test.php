<?php
header('Content-Type: text/plain');

$start = microtime(true);

// 1. Bootstrap Laravel fully
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

$bootstrapTime = round((microtime(true) - $start) * 1000);

// 2. Test database connection
$dbStart = microtime(true);
$count = Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as total FROM citas')[0]->total ?? 0;
$dbTime = round((microtime(true) - $dbStart) * 1000);

// 3. Test a typical query with eager loading
$queryStart = microtime(true);
$citas = App\Models\Cita::with(['cliente', 'mascota', 'veterinario'])
    ->where('estado', 'pendiente')
    ->limit(25)
    ->get();
$queryTime = round((microtime(true) - $queryStart) * 1000);

$totalTime = round((microtime(true) - $start) * 1000);

echo "=== Diagnostico de Rendimiento VetMora ===\n\n";
echo "Laravel Bootstrap: {$bootstrapTime}ms\n";
echo "DB Connection + COUNT: {$dbTime}ms (Total citas: {$count})\n";
echo "Query con eager loading (25 citas): {$queryTime}ms ({$citas->count()} resultados)\n";
echo "---------------------------------------\n";
echo "TOTAL: {$totalTime}ms\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OPcache: " . (function_exists('opcache_get_status') && opcache_get_status(false) ? 'ON' : 'OFF') . "\n";
echo "Memory: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
