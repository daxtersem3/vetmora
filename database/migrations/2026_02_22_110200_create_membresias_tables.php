<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->string('codigo_activacion', 9)->unique();
            $table->date('fecha_activacion');
            $table->date('fecha_vencimiento'); // 3 months after activation
            $table->string('estado', 20)->default('vigente'); // vigente, vencida
            $table->timestamps();
        });

        Schema::create('membresia_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membresia_id')->constrained('membresias')->cascadeOnDelete();
            $table->string('tipo'); // consulta, desparasitacion_corte, ecografia
            $table->string('nombre'); // human-readable name
            $table->boolean('realizado')->default(false);
            $table->date('fecha_realizado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresia_servicios');
        Schema::dropIfExists('membresias');
    }
};
