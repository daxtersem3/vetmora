<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Plan types: id=1 Gato, id=2 Perro
        Schema::create('plan_vacunacion_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Gato, Perro
            $table->timestamps();
        });

        // 2. Template vaccines per plan type
        Schema::create('plan_vacunacion_tipo_vacunas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_id')->constrained('plan_vacunacion_tipos')->cascadeOnDelete();
            $table->string('nombre'); // vaccine name
            $table->integer('semana'); // week number
            $table->integer('orden');  // display order
            $table->timestamps();
        });

        // 3. Client+Mascota plan registrations
        Schema::create('plan_vacunaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('tipo_id')->constrained('plan_vacunacion_tipos')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->string('estado', 20)->default('activo'); // activo, completado
            $table->timestamps();
        });

        // 4. Individual doses tracking
        Schema::create('plan_vacunacion_dosis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan_vacunaciones')->cascadeOnDelete();
            $table->string('nombre');
            $table->integer('semana');
            $table->date('fecha_programada');
            $table->boolean('realizado')->default(false);
            $table->date('fecha_realizado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_vacunacion_dosis');
        Schema::dropIfExists('plan_vacunaciones');
        Schema::dropIfExists('plan_vacunacion_tipo_vacunas');
        Schema::dropIfExists('plan_vacunacion_tipos');
    }
};
