<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaSeguridad extends Model
{
    protected $table = 'preguntas_seguridad';

    protected $fillable = [
        'user_id',
        'pregunta_1',
        'respuesta_1',
        'pregunta_2',
        'respuesta_2',
    ];

    protected $hidden = [
        'respuesta_1',
        'respuesta_2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
