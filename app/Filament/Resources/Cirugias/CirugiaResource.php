<?php

namespace App\Filament\Resources\Cirugias;

use App\Filament\Resources\Cirugias\Pages\CreateCirugia;
use App\Filament\Resources\Cirugias\Pages\EditCirugia;
use App\Filament\Resources\Cirugias\Pages\ListCirugias;
use App\Models\Cirugia;
use App\Models\Mascota;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

class CirugiaResource extends Resource
{
    protected static ?string $model = Cirugia::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    public static function getNavigationGroup(): ?string
    {
        return 'Citas';
    }

    protected static ?string $navigationLabel = 'Cirugías';
    protected static ?string $modelLabel = 'Cirugía';
    protected static ?string $pluralModelLabel = 'Cirugías';
    protected static ?int $navigationSort = 2;

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

            // --- CIRUJANO (Veterinario) ---
            Select::make('veterinario_id')
                ->label('Cirujano (Veterinario)')
                ->required()
                ->options(function (): array {
                    return User::whereHas('nivel', function ($q) {
                        $q->where('nombre', 'Veterinario');
                    })->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->preload(),

            // --- FECHA ---
            DatePicker::make('fecha')
                ->label('Fecha de la Cirugía')
                ->required()
                ->native(false)
                ->minDate(now())
                ->live(),

            // --- HORA (slots predefinidos) ---
            Select::make('hora')
                ->label('Hora de la Cirugía')
                ->required()
                ->options(function (Get $get, ?Cirugia $record) {
                    $fecha = $get('fecha');
                    $allSlots = [
                        '09:00' => '9:00 AM',
                        '12:00' => '12:00 PM',
                        '16:00' => '4:00 PM',
                        '19:00' => '7:00 PM',
                    ];

                    if (!$fecha) {
                        return $allSlots;
                    }

                    // Find already-booked slots for the selected date
                    $booked = Cirugia::where('fecha', $fecha)
                        ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                        ->pluck('hora')
                        ->toArray();

                    // Remove booked slots
                    return collect($allSlots)
                        ->reject(fn($label, $key) => in_array($key, $booked))
                        ->toArray();
                })
                ->disabled(fn(Get $get): bool => !$get('fecha'))
                ->hint(fn(Get $get) => !$get('fecha') ? 'Primero seleccione una fecha' : null)
                ->live(),

            // --- MOTIVO ---
            Textarea::make('motivo')
                ->label('Motivo de la Cirugía')
                ->rows(3)
                ->columnSpanFull(),

            // --- OBSERVACIONES ---
            Textarea::make('observaciones')
                ->label('Observaciones')
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
                TextColumn::make('veterinario.name')->label('Cirujano')->searchable(),
                TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('hora')->label('Hora')->sortable(),
                TextColumn::make('motivo')->label('Motivo')->limit(40),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'realizada' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->defaultSort('fecha', 'asc')
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
                            ->when($data['desde'], fn($q, $v) => $q->whereDate('fecha', '>=', $v))
                            ->when($data['hasta'], fn($q, $v) => $q->whereDate('fecha', '<=', $v));
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

                // Descargar PDF (solo si realizada)
                Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn($record) => $record->estado === 'realizada')
                    ->url(fn($record) => route('cirugia.pdf', $record))
                    ->openUrlInNewTab(),

                // Marcar como realizada
                Action::make('marcar_realizada')
                    ->label('Marcar Realizada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar Cirugía como Realizada')
                    ->modalDescription('¿Está seguro de que desea marcar esta cirugía como realizada?')
                    ->visible(
                        fn($record) =>
                        $record->estado === 'pendiente' &&
                        in_array(auth()->user()?->nivel?->nombre, ['Administrador', 'Veterinario'])
                    )
                    ->action(function ($record) {
                        $record->update(['estado' => 'realizada']);
                        \Filament\Notifications\Notification::make()
                            ->title('Cirugía marcada como realizada')
                            ->success()
                            ->send();
                    }),

                // Eliminar con contraseña
                Action::make('delete_with_password')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Cirugía')
                    ->modalDescription('Esta acción es irreversible. Ingrese la contraseña del administrador para confirmar.')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('password')
                            ->label('Contraseña del Administrador')
                            ->password()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = User::whereHas('nivel', fn($q) => $q->where('nombre', 'Administrador'))
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
                            ->title('Cirugía eliminada correctamente')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCirugias::route('/'),
            'create' => CreateCirugia::route('/create'),
            'edit' => EditCirugia::route('/{record}/edit'),
        ];
    }
}
