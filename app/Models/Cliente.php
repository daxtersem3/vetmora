<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'cedula',
        'correo',
        'telefono',
        'direccion',
    ];

    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function historialesMedicos()
    {
        return $this->hasManyThrough(HistorialMedico::class, Cita::class);
    }
}
