<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('historial_medico', function (Blueprint $table) {
            $table->text('anamnesis_motivo_consulta')->nullable()->after('examenes_realizados');
            $table->string('anamnesis_dieta')->nullable()->after('anamnesis_motivo_consulta');
            $table->string('anamnesis_vomito', 10)->nullable()->after('anamnesis_dieta');
            $table->string('anamnesis_diarrea', 10)->nullable()->after('anamnesis_vomito');
            $table->string('anamnesis_garrapatas', 10)->nullable()->after('anamnesis_diarrea');
            $table->string('anamnesis_esquema_vacunal')->nullable()->after('anamnesis_garrapatas');
            $table->string('anamnesis_desparasitacion')->nullable()->after('anamnesis_esquema_vacunal');
            $table->string('anamnesis_enfermedades_previas')->nullable()->after('anamnesis_desparasitacion');
            $table->string('anamnesis_tx_recientes')->nullable()->after('anamnesis_enfermedades_previas');
            $table->string('anamnesis_esterilizado', 10)->nullable()->after('anamnesis_tx_recientes');
            $table->unsignedTinyInteger('anamnesis_num_partos')->nullable()->after('anamnesis_esterilizado');
            $table->string('anamnesis_vive_con_animales', 10)->nullable()->after('anamnesis_num_partos');
            $table->string('anamnesis_cuales_animales')->nullable()->after('anamnesis_vive_con_animales');
        });
    }

    public function down(): void
    {
        Schema::table('historial_medico', function (Blueprint $table) {
            $table->dropColumn([
                'anamnesis_motivo_consulta',
                'anamnesis_dieta',
                'anamnesis_vomito',
                'anamnesis_diarrea',
                'anamnesis_garrapatas',
                'anamnesis_esquema_vacunal',
                'anamnesis_desparasitacion',
                'anamnesis_enfermedades_previas',
                'anamnesis_tx_recientes',
                'anamnesis_esterilizado',
                'anamnesis_num_partos',
                'anamnesis_vive_con_animales',
                'anamnesis_cuales_animales',
            ]);
        });
    }
};
