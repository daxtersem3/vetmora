<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for frequently filtered/sorted/searched columns.
 */
return new class extends Migration {
    public function up(): void
    {
        // --- Citas ---
        Schema::table('citas', function (Blueprint $table) {
            $table->index('estado');
            $table->index('cliente_id');
            $table->index('mascota_id');
            $table->index('veterinario_id');
        });

        // --- Cirugías ---
        Schema::table('cirugias', function (Blueprint $table) {
            $table->index('estado');
            $table->index('cliente_id');
            $table->index('mascota_id');
            $table->index('veterinario_id');
        });

        // --- Membresías ---
        Schema::table('membresias', function (Blueprint $table) {
            $table->index('estado');
            $table->index('cliente_id');
            $table->index('mascota_id');
            $table->index('fecha_vencimiento');
        });

        // --- Servicios de membresía ---
        Schema::table('membresia_servicios', function (Blueprint $table) {
            $table->index(['membresia_id', 'realizado']);
        });

        // --- Plan de vacunación ---
        Schema::table('plan_vacunaciones', function (Blueprint $table) {
            $table->index('estado');
            $table->index('cliente_id');
            $table->index('mascota_id');
            $table->index('tipo_id');
        });

        // --- Dosis de vacunación ---
        Schema::table('plan_vacunacion_dosis', function (Blueprint $table) {
            $table->index(['plan_id', 'realizado']);
            $table->index('fecha_programada');
        });

        // --- Mascotas ---
        Schema::table('mascotas', function (Blueprint $table) {
            $table->index('cliente_id');
        });

        // --- Historial Médico ---
        if (Schema::hasTable('historial_medicos')) {
            Schema::table('historial_medicos', function (Blueprint $table) {
                $table->index('cita_id');
            });
        }

        // --- Usuarios ---
        Schema::table('users', function (Blueprint $table) {
            $table->index('nivel_id');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['mascota_id']);
            $table->dropIndex(['veterinario_id']);
        });
        Schema::table('cirugias', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['mascota_id']);
            $table->dropIndex(['veterinario_id']);
        });
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['mascota_id']);
            $table->dropIndex(['fecha_vencimiento']);
        });
        Schema::table('membresia_servicios', function (Blueprint $table) {
            $table->dropIndex(['membresia_id', 'realizado']);
        });
        Schema::table('plan_vacunaciones', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['mascota_id']);
            $table->dropIndex(['tipo_id']);
        });
        Schema::table('plan_vacunacion_dosis', function (Blueprint $table) {
            $table->dropIndex(['plan_id', 'realizado']);
            $table->dropIndex(['fecha_programada']);
        });
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropIndex(['cliente_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nivel_id']);
        });
    }
};
