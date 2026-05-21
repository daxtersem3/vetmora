<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanVacunacionSeeder extends Seeder
{
    public function run(): void
    {
        // Tipo 1: Gato
        $gatoId = DB::table('plan_vacunacion_tipos')->insertGetId([
            'nombre' => 'Gato',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tipo 2: Perro
        $perroId = DB::table('plan_vacunacion_tipos')->insertGetId([
            'nombre' => 'Perro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vacunas para Gato
        $vacunasGato = [
            ['nombre' => 'Desparasitación', 'semana' => 4, 'orden' => 1],
            ['nombre' => 'Triple Felina (1ra dosis)', 'semana' => 6, 'orden' => 2],
            ['nombre' => 'Triple Felina (2da dosis)', 'semana' => 9, 'orden' => 3],
            ['nombre' => 'Quíntuple Felina', 'semana' => 12, 'orden' => 4],
            ['nombre' => 'Rabia', 'semana' => 15, 'orden' => 5],
        ];

        foreach ($vacunasGato as $v) {
            DB::table('plan_vacunacion_tipo_vacunas')->insert([
                'tipo_id' => $gatoId,
                'nombre' => $v['nombre'],
                'semana' => $v['semana'],
                'orden' => $v['orden'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Vacunas para Perro
        $vacunasPerro = [
            ['nombre' => 'Desparasitación', 'semana' => 4, 'orden' => 1],
            ['nombre' => 'Parvo / Puppy DP', 'semana' => 6, 'orden' => 2],
            ['nombre' => 'Puppy DP / Séxtuple', 'semana' => 9, 'orden' => 3],
            ['nombre' => 'Séxtuple', 'semana' => 12, 'orden' => 4],
            ['nombre' => 'Séxtuple', 'semana' => 15, 'orden' => 5],
            ['nombre' => 'Rabia', 'semana' => 18, 'orden' => 6],
        ];

        foreach ($vacunasPerro as $v) {
            DB::table('plan_vacunacion_tipo_vacunas')->insert([
                'tipo_id' => $perroId,
                'nombre' => $v['nombre'],
                'semana' => $v['semana'],
                'orden' => $v['orden'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
