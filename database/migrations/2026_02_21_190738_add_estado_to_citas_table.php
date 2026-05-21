<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('citas', 'estado')) {
            // Fix column type to varchar(20) if it was something else
            Schema::table('citas', function (Blueprint $table) {
                $table->string('estado', 20)->default('pendiente')->change();
            });
        } else {
            Schema::table('citas', function (Blueprint $table) {
                $table->string('estado', 20)->default('pendiente')->after('motivo');
            });
        }

        // Mark existing citas that already have historial as 'tomada'
        \DB::table('citas')
            ->whereIn('id', \DB::table('historial_medico')->pluck('cita_id'))
            ->update(['estado' => 'tomada']);
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
