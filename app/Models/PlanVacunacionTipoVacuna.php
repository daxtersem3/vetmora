<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVacunacionTipoVacuna extends Model
{
    protected $table = 'plan_vacunacion_tipo_vacunas';

    protected $fillable = ['tipo_id', 'nombre', 'semana', 'orden'];

    public function tipo()
    {
        return $this->belongsTo(PlanVacunacionTipo::class, 'tipo_id');
    }
}
