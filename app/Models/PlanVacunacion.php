<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVacunacion extends Model
{
    protected $table = 'plan_vacunaciones';

    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'tipo_id',
        'fecha_inicio',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function tipo()
    {
        return $this->belongsTo(PlanVacunacionTipo::class, 'tipo_id');
    }

    public function dosis()
    {
        return $this->hasMany(PlanVacunacionDosis::class, 'plan_id')->orderBy('semana');
    }

    /**
     * Create all doses from the plan type template.
     */
    public static function crearDosis(PlanVacunacion $plan): void
    {
        $vacunas = PlanVacunacionTipoVacuna::where('tipo_id', $plan->tipo_id)
            ->orderBy('orden')
            ->get();

        foreach ($vacunas as $vacuna) {
            $plan->dosis()->create([
                'nombre' => $vacuna->nombre,
                'semana' => $vacuna->semana,
                'fecha_programada' => $plan->fecha_inicio->copy()->addWeeks($vacuna->semana),
            ]);
        }
    }
}
