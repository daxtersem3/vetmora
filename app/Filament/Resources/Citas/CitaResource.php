<?php

namespace App\Filament\Resources\Citas;

use App\Filament\Resources\Citas\Pages\CreateCita;
use App\Filament\Resources\Citas\Pages\EditCita;
use App\Filament\Resources\Citas\Pages\ListCitas;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    public static function getNavigationGroup(): ?string
    {
        return 'Citas';
    }

    protected static ?string $navigationLabel = 'Citas';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['cliente', 'mascota', 'veterinario']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            // --- CLIENTE ---
            Select::make('cliente_id')
                ->label('Cliente')
                ->relationship('cliente', 'nombre')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->createOptionForm([
                    TextInput::make('nombre')->label('Nombre del Cliente')->required(),
                    TextInput::make('cedula')->label('Cédula del Cliente')->required(),
                    TextInput::make('correo')->label('Correo del Cliente')->email(),
                    TextInput::make('telefono')->label('Número de Teléfono')->tel(),
                    TextInput::make('direccion')->label('Dirección'),
                ])
                ->createOptionUsing(function (array $data): int {
                    return \App\Models\Cliente::create($data)->id;
                }),

            // --- MASCOTA (filtered by client) ---
            Select::make('mascota_id')
                ->label('Seleccionar Mascota')
                ->required()
                ->options(function (Get $get): Collection {
                    $clienteId = $get('cliente_id');
                    if (!$clienteId) {
                        return collect();
                    }
                    return Mascota::where('cliente_id', $clienteId)
                        ->pluck('nombre', 'id');
                })
                ->disabled(fn(Get $get): bool => !$get('cliente_id'))
                ->hint(fn(Get $get) => !$get('cliente_id') ? 'Primero seleccione un cliente' : null)
                ->live()
                ->createOptionForm([
                    TextInput::make('nombre')->label('Nombre de la Mascota')->required(),
                    TextInput::make('especie')->label('Especie')->required(),
                    TextInput::make('raza')->label('Raza'),
                    DatePicker::make('fecha_nacimiento')->label('Fecha de Nacimiento')->native(false),
                ])
                ->createOptionUsing(function (array $data, Get $get): int {
                    $data['cliente_id'] = $get('cliente_id');
                    return Mascota::create($data)->id;
                }),

            // --- VETERINARIO ---
            Select::make('veterinario_id')
                ->label('Veterinario')
                ->required()
                ->options(function (): array {
                    return User::whereHas('nivel', function ($q) {
                        $q->where('nombre', 'Veterinario');
                    })->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->preload(),

            // --- FECHA Y HORA ---
            DateTimePicker::make('fecha_hora')
                ->label('Fecha y Hora de la Cita')
                ->required()
                ->native(false)
                ->minDate(now()),

            // --- MOTIVO ---
            Textarea::make('motivo')
                ->label('Motivo de la Cita')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('mascota.nombre')->label('Mascota')->searchable(),
                TextColumn::make('veterinario.name')->label('Veterinario')->searchable(),
                TextColumn::make('fecha_hora')->label('Fecha y Hora')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('motivo')->label('Motivo')->limit(40),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'tomada' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->defaultSort('fecha_hora', 'asc')
            ->filters([
                Filter::make('solo_pendientes')
                    ->label('Solo pendientes')
                    ->toggle()
                    ->default(true)
                    ->query(fn(Builder $query) => $query->where('estado', 'pendiente')),

                Filter::make('fecha_rango')
                    ->label('Rango de fechas')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn($q, $v) => $q->whereDate('fecha_hora', '>=', $v))
                            ->when($data['hasta'], fn($q, $v) => $q->whereDate('fecha_hora', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $ind = [];
                        if ($data['desde'] ?? null)
                            $ind[] = 'Desde: ' . $data['desde'];
                        if ($data['hasta'] ?? null)
                            $ind[] = 'Hasta: ' . $data['hasta'];
                        return $ind;
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                // Tomar Cita — solo administradores
                Action::make('tomar_cita')
                    ->label('Tomar Cita')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->url(
                        fn($record) =>
                        \App\Filament\Resources\HistorialMedico\HistorialMedicoResource::getUrl('create', ['cita_id' => $record->id])
                    )
                    ->visible(
                        fn($record) =>
                        $record->estado === 'pendiente' &&
                        in_array(auth()->user()?->nivel?->nombre, ['Administrador', 'Veterinario'])
                    ),

                // Eliminar con contraseña de administrador
                Action::make('delete_with_password')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Cita')
                    ->modalDescription('Esta acción es irreversible. Ingrese la contraseña del administrador para confirmar.')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('password')
                            ->label('Contraseña del Administrador')
                            ->password()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = \App\Models\User::whereHas('nivel', fn($q) => $q->where('nombre', 'Administrador'))
                            ->first();

                        if (!$admin || !\Illuminate\Support\Facades\Hash::check($data['password'], $admin->password)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Contraseña incorrecta')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Cita eliminada correctamente')
                            ->success()
                            ->send();
                    }),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => ListCitas::route('/'),
            'create' => CreateCita::route('/create'),
            'edit' => EditCita::route('/{record}/edit'),
        ];
    }
}
