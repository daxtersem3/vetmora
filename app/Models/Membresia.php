<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'codigo_activacion',
        'fecha_activacion',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_activacion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    /**
     * Auto-check and update estado based on fecha_vencimiento.
     */
    public function getEstadoAttribute($value): string
    {
        if ($value === 'vigente' && $this->fecha_vencimiento && $this->fecha_vencimiento->isPast()) {
            // Auto-update in DB
            $this->newQuery()->where('id', $this->id)->update(['estado' => 'vencida']);
            return 'vencida';
        }
        return $value;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function servicios()
    {
        return $this->hasMany(MembresiaServicio::class);
    }

    /**
     * Create the 3 default services when a membership is activated.
     */
    public static function crearServicios(Membresia $membresia): void
    {
        $servicios = [
            ['tipo' => 'consulta', 'nombre' => 'Consulta Veterinaria'],
            ['tipo' => 'desparasitacion_corte', 'nombre' => 'Desparasitación + Corte de Uñas'],
            ['tipo' => 'ecografia', 'nombre' => 'Ecografía'],
        ];

        foreach ($servicios as $servicio) {
            $membresia->servicios()->create($servicio);
        }
    }
}
