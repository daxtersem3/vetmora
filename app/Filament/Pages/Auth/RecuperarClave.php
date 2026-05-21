<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;

class RecuperarClave extends SimplePage
{
    protected string $view = 'filament.pages.auth.recuperar-clave';

    public static function getSlug(): string
    {
        return 'recuperar-clave';
    }

    // Step: 1 = email, 2 = security questions, 3 = new password
    public int $step = 1;

    public string $email = '';
    public string $respuesta_1 = '';
    public string $respuesta_2 = '';
    public string $nueva_password = '';
    public string $nueva_password_confirmation = '';

    #[Locked]
    public ?int $userId = null;

    public string $pregunta_1 = '';
    public string $pregunta_2 = '';
    public string $error = '';

    // Step 1: find user by email
    public function buscarUsuario(): void
    {
        $this->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $this->email)->first();

        if (!$user || !$user->preguntaSeguridad) {
            $this->error = 'Este usuario no tiene preguntas de seguridad configuradas.';
            return;
        }

        $this->userId = $user->id;
        $this->pregunta_1 = $user->preguntaSeguridad->pregunta_1;
        $this->pregunta_2 = $user->preguntaSeguridad->pregunta_2;
        $this->error = '';
        $this->step = 2;
    }

    // Step 2: validate answers
    public function validarRespuestas(): void
    {
        $this->validate([
            'respuesta_1' => 'required|string',
            'respuesta_2' => 'required|string',
        ]);

        $user = User::find($this->userId);
        $ps = $user?->preguntaSeguridad;

        if (
            !$ps ||
            !Hash::check(strtolower(trim($this->respuesta_1)), $ps->respuesta_1) ||
            !Hash::check(strtolower(trim($this->respuesta_2)), $ps->respuesta_2)
        ) {
            $this->error = 'Una o ambas respuestas son incorrectas.';
            return;
        }

        $this->error = '';
        $this->step = 3;
    }

    // Step 3: update password
    public function actualizarClave(): void
    {
        $this->validate([
            'nueva_password' => 'required|min:8|confirmed',
            'nueva_password_confirmation' => 'required',
        ]);

        $user = User::find($this->userId);

        if (!$user) {
            $this->error = 'Usuario no encontrado.';
            return;
        }

        $user->update(['password' => Hash::make($this->nueva_password)]);

        session()->flash('recovery_success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        $this->redirect(filament()->getLoginUrl());
    }

    public function volver(): void
    {
        $this->step--;
        $this->error = '';
    }
}
