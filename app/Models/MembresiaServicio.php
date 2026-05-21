<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembresiaServicio extends Model
{
    protected $fillable = [
        'membresia_id',
        'tipo',
        'nombre',
        'realizado',
        'fecha_realizado',
    ];

    protected $casts = [
        'realizado' => 'boolean',
        'fecha_realizado' => 'date',
    ];

    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }
}
