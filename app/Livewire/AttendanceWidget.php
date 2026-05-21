<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Asistencia;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Support\Carbon;

class AttendanceWidget extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('cedula')
                    ->label('Ingrese su Cédula')
                    ->required()
                    ->numeric(),
            ])
            ->statePath('data');
    }

    public function checkIn(): void
    {
        $data = $this->form->getState();
        $cedula = $data['cedula'];

        $user = User::whereHas('datosPersonales', function ($query) use ($cedula) {
            $query->where('cedula', $cedula);
        })->first();

        if (!$user) {
            Notification::make()
                ->title('Usuario no encontrado')
                ->danger()
                ->send();
            return;
        }

        // Check if ANY attendance exists today to enforce Single Shift per Day
        $existingAsistencia = Asistencia::where('user_id', $user->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        if ($existingAsistencia) {
            Notification::make()
                ->title('Ya tienes un registro de asistencia el día de hoy.')
                ->warning()
                ->persistent()
                ->send();
            return;
        }

        Asistencia::create([
            'user_id' => $user->id,
            'check_in' => Carbon::now(),
        ]);

        Notification::make()
            ->title('Entrada registrada: ' . $user->name)
            ->success()
            ->persistent()
            ->send();

        $this->form->fill();
    }

    public function checkOut(): void
    {
        $data = $this->form->getState();
        $cedula = $data['cedula'];

        $user = User::whereHas('datosPersonales', function ($query) use ($cedula) {
            $query->where('cedula', $cedula);
        })->first();

        if (!$user) {
            Notification::make()
                ->title('Usuario no encontrado')
                ->danger()
                ->send();
            return;
        }

        $asistencia = Asistencia::where('user_id', $user->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (!$asistencia) {
            Notification::make()
                ->title('No tienes una entrada pendiente por cerrar (o ya cerraste turno hoy).')
                ->warning()
                ->send();
            return;
        }

        $asistencia->update([
            'check_out' => Carbon::now(),
        ]);

        Notification::make()
            ->title('Salida registrada: ' . $user->name)
            ->success()
            ->persistent()
            ->send();

        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.attendance-widget');
    }
}
