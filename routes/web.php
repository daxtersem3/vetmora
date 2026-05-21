<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/debug-php', function () {
    return [
        'php_version' => phpversion(),
        'intl_loaded' => extension_loaded('intl'),
        'ini_path' => php_ini_loaded_file(),
    ];
});

// PDF Reports (requiere sesión activa de Filament)
Route::get('/admin/historial-medico/{historial}/pdf', \App\Http\Controllers\HistorialMedicoPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('historial-medico.pdf');

Route::get('/admin/cirugia/{cirugia}/pdf', \App\Http\Controllers\CirugiaPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('cirugia.pdf');

// Respaldo de base de datos (solo Administrador, nivel_id = 1)
Route::get('/admin/db/backup/download', [\App\Http\Controllers\DatabaseBackupController::class, 'download'])
    ->middleware(['web', 'auth'])
    ->name('db.backup.download');

// Recuperación de contraseña vía preguntas de seguridad (sin autenticación)
Route::get('/admin/recuperar-clave', \App\Filament\Pages\Auth\RecuperarClave::class)
    ->middleware(['web'])
    ->name('recuperar-clave');

