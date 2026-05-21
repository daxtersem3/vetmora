<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cirugia extends Model
{
    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'veterinario_id',
        'fecha',
        'hora',
        'motivo',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
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
}
