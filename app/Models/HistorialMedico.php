<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cita;

class HistorialMedico extends Model
{
    protected $table = 'historial_medico';

    protected $fillable = [
        'cita_id',
        'fecha',
        'sintomas',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'peso',
        'temperatura',
        'sistemas_evaluados',
        'examenes_realizados',
    ];

    protected $casts = [
        'fecha' => 'date',
        'sistemas_evaluados' => 'array',
        'examenes_realizados' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (HistorialMedico $historial) {
            if ($historial->cita_id) {
                Cita::where('id', $historial->cita_id)->update(['estado' => 'tomada']);
            }
        });

        static::deleted(function (HistorialMedico $historial) {
            if ($historial->cita_id) {
                Cita::where('id', $historial->cita_id)->update(['estado' => 'pendiente']);
            }
        });
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}
