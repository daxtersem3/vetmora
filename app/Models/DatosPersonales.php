<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DatosPersonales extends Model
{
    protected $table = 'datos_personales';
    protected $fillable = ['cedula', 'direccion', 'tipo_sangre', 'foto_path'];

    protected static function booted(): void
    {
        static::deleting(function (DatosPersonales $dp) {
            if ($dp->foto_path) {
                Storage::disk('public')->delete($dp->foto_path);
            }
        });
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
