<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cirugias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('mascota_id')->constrained('mascotas')->cascadeOnDelete();
            $table->foreignId('veterinario_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('hora', 5); // 09:00, 12:00, 16:00, 19:00
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->timestamps();

            // Only one surgery per time slot
            $table->unique(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cirugias');
    }
};
