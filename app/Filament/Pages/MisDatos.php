<?php

namespace App\Filament\Pages;

use App\Models\PreguntaSeguridad;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use BackedEnum;

class MisDatos extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected string $view = 'filament.pages.mis-datos';

    protected static ?string $navigationLabel = 'Mis Datos';

    protected static ?string $title = 'Mis Datos';

    public ?array $data = [];

    protected static array $preguntasDisponibles = [
        '¿Cuál es el nombre de tu primera mascota?' => '¿Cuál es el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?' => '¿En qué ciudad naciste?',
        '¿Cuál es el nombre de tu madre?' => '¿Cuál es el nombre de tu madre?',
        '¿Nombre de tu escuela primaria?' => '¿Nombre de tu escuela primaria?',
        '¿Cuál es tu comida favorita?' => '¿Cuál es tu comida favorita?',
        '¿Nombre de tu mejor amigo de infancia?' => '¿Nombre de tu mejor amigo de infancia?',
        '¿Cuál fue tu primer trabajo?' => '¿Cuál fue tu primer trabajo?',
        '¿El apodo de tu abuela?' => '¿El apodo de tu abuela?',
        '¿Modelo de tu primer carro?' => '¿Modelo de tu primer carro?',
        '¿Cuál es tu película favorita?' => '¿Cuál es tu película favorita?',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $user->load('datosPersonales');

        $data = $user->toArray();
        if ($user->datosPersonales) {
            $data['datosPersonales'] = $user->datosPersonales->toArray();
        }

        $this->form->fill($data);
    }

    #[On('openPreguntasAction')]
    public function openPreguntasModal(): void
    {
        $this->mountAction('mostrarPreguntas');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información de Cuenta')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Nueva Contraseña (Dejar en blanco para mantener)')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(false),
                    ]),
                Section::make('Información Personal')
                    ->statePath('datosPersonales')
                    ->schema([
                        TextInput::make('cedula')
                            ->label('Cédula')
                            ->required(),
                        TextInput::make('direccion')
                            ->label('Dirección'),
                        TextInput::make('tipo_sangre')
                            ->label('Tipo de Sangre'),
                        FileUpload::make('foto_path')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('empleados')
                            ->visibility('public'),
                    ]),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    protected function getHeaderActions(): array
    {
        return [
            // Step 1 – asks for password, then opens step 2
            Action::make('preguntasSeguridad')
                ->label('Preguntas de Seguridad')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->modalHeading('Verificar identidad')
                ->modalDescription('Ingresa tu contraseña para ver tus preguntas de seguridad.')
                ->modalWidth('sm')
                ->form([
                    TextInput::make('password_actual')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    if (!Hash::check($data['password_actual'] ?? '', auth()->user()->password)) {
                        Notification::make()
                            ->danger()
                            ->title('Contraseña incorrecta')
                            ->send();
                        throw new Halt();
                    }
                    // Dispatch so the listener opens the second modal AFTER this one closes
                    $this->dispatch('openPreguntasAction');
                }),

            // Read-only questions display (button hidden via CSS)
            Action::make('mostrarPreguntas')
                ->label('')
                ->icon('heroicon-o-eye')
                ->extraAttributes(['style' => 'display:none !important;'])
                ->modalHeading('Tus preguntas de seguridad')
                ->modalWidth('sm')
                ->modalContent(function (): \Illuminate\Support\HtmlString {
                    $ps = auth()->user()->preguntaSeguridad;
                    if (!$ps || (blank($ps->pregunta_1) && blank($ps->pregunta_2))) {
                        return new \Illuminate\Support\HtmlString(
                            '<p style="text-align:center;color:#6b7280;padding:8px 0;">
                                No tienes preguntas de seguridad configuradas.
                            </p>'
                        );
                    }
                    $q1 = e($ps->pregunta_1 ?? '—');
                    $q2 = e($ps->pregunta_2 ?? '—');
                    return new \Illuminate\Support\HtmlString("
                        <div style='display:flex;flex-direction:column;gap:12px;padding:4px 0;'>
                            <div style='background:#f5f3ff;border-left:4px solid #7c3aed;
                                        padding:10px 14px;border-radius:6px;'>
                                <p style='font-size:11px;color:#7c3aed;font-weight:700;
                                           margin:0 0 4px;letter-spacing:.5px;'>PREGUNTA 1</p>
                                <p style='margin:0;font-size:14px;color:#1f2937;'>{$q1}</p>
                            </div>
                            <div style='background:#f5f3ff;border-left:4px solid #7c3aed;
                                        padding:10px 14px;border-radius:6px;'>
                                <p style='font-size:11px;color:#7c3aed;font-weight:700;
                                           margin:0 0 4px;letter-spacing:.5px;'>PREGUNTA 2</p>
                                <p style='margin:0;font-size:14px;color:#1f2937;'>{$q2}</p>
                            </div>
                        </div>
                    ");
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),
        ];
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            $user = auth()->user();

            $userProps = collect($data)->except('datosPersonales')->toArray();
            if (empty($userProps['password'])) {
                unset($userProps['password']);
            }

            $user->fill($userProps);
            $user->save();

            if (isset($data['datosPersonales'])) {
                $dpData = $data['datosPersonales'];

                if ($user->datos_personales_id && $user->datosPersonales) {
                    // Update existing record
                    $user->datosPersonales->update($dpData);
                } else {
                    // Create new DatosPersonales and associate
                    $dp = \App\Models\DatosPersonales::create($dpData);
                    $user->datos_personales_id = $dp->id;
                    $user->save();
                }
            }

            Notification::make()
                ->success()
                ->title('Datos actualizados correctamente')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar Cambios')
                ->submit('submit'),
        ];
    }
}