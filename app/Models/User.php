<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nivel_id',
        'datos_personales_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class);
    }

    public function datosPersonales()
    {
        return $this->belongsTo(DatosPersonales::class, 'datos_personales_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function preguntaSeguridad()
    {
        return $this->hasOne(PreguntaSeguridad::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->nivel_id !== null;
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Delete related DatosPersonales (triggers photo cleanup)
            if ($user->datosPersonales) {
                $user->datosPersonales->delete();
            }
            // Delete related PreguntaSeguridad
            $user->preguntaSeguridad?->delete();
        });
    }
}
