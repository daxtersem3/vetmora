<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVacunacionTipo extends Model
{
    protected $table = 'plan_vacunacion_tipos';

    protected $fillable = ['nombre'];

    public function vacunas()
    {
        return $this->hasMany(PlanVacunacionTipoVacuna::class, 'tipo_id')->orderBy('orden');
    }
}
