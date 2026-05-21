<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Mascota extends Model
{
    protected $fillable = [
        'nombre',
        'especie',
        'raza',
        'fecha_nacimiento',
        'cliente_id',
        'foto',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Mascota $mascota) {
            if ($mascota->foto) {
                Storage::disk('public')->delete($mascota->foto);
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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
