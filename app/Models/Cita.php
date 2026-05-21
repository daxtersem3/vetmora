<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HistorialMedico;

class Cita extends Model
{
    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'veterinario_id',
        'fecha_hora',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario()
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }

    public function historialMedico()
    {
        return $this->hasOne(HistorialMedico::class, 'cita_id');
    }
}
