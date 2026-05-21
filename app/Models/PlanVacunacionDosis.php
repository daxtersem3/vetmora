<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVacunacionDosis extends Model
{
    protected $table = 'plan_vacunacion_dosis';

    protected $fillable = [
        'plan_id',
        'nombre',
        'semana',
        'fecha_programada',
        'realizado',
        'fecha_realizado',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizado' => 'date',
        'realizado' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(PlanVacunacion::class, 'plan_id');
    }
}
