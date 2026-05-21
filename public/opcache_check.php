<?php
header('Content-Type: text/plain');

if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status) {
        echo "OPcache: ACTIVADO ✅\n";
        echo "Memoria usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "Memoria libre: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "Scripts cacheados: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
    } else {
        echo "OPcache: DESACTIVADO ❌\n";
        echo "opcache_get_status() returned false\n";
    }
} else {
    echo "OPcache: NO DISPONIBLE ❌\n";
    echo "La extensión no está cargada\n";
}

echo "\nopcache.enable = " . ini_get('opcache.enable') . "\n";
echo "opcache.enable_cli = " . ini_get('opcache.enable_cli') . "\n";
echo "opcache.memory_consumption = " . ini_get('opcache.memory_consumption') . "\n";
echo "opcache.max_accelerated_files = " . ini_get('opcache.max_accelerated_files') . "\n";
echo "opcache.validate_timestamps = " . ini_get('opcache.validate_timestamps') . "\n";
