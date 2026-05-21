<?php

namespace App\Filament\Resources\Membresias;

use App\Filament\Resources\Membresias\Pages\ListMembresias;
use App\Models\Mascota;
use App\Models\Membresia;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MembresiaResource extends Resource
{
    protected static ?string $model = Membresia::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Membresías';
    protected static ?string $modelLabel = 'Membresía';
    protected static ?string $pluralModelLabel = 'Membresías';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['cliente', 'mascota', 'servicios'])
            ->withCount([
                'servicios as servicios_total',
                'servicios as servicios_realizados' => fn($q) => $q->where('realizado', true),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_activacion')
                    ->label('Código')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mascota.nombre')
                    ->label('Mascota')
                    ->searchable(),
                TextColumn::make('fecha_activacion')
                    ->label('Activación')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'vigente' => 'success',
                        'vencida' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                TextColumn::make('servicios_completados')
                    ->label('Servicios')
                    ->state(function (Membresia $record): string {
                        $total = $record->servicios_total ?? 0;
                        $done = $record->servicios_realizados ?? 0;
                        return "{$done}/{$total}";
                    })
                    ->badge()
                    ->color(function (Membresia $record): string {
                        $total = $record->servicios_total ?? 0;
                        $done = $record->servicios_realizados ?? 0;
                        if ($done === $total && $total > 0)
                            return 'success';
                        if ($done > 0)
                            return 'warning';
                        return 'gray';
                    }),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('solo_vigentes')
                    ->label('Solo vigentes')
                    ->toggle()
                    ->default(false)
                    ->query(fn(Builder $query) => $query->where('estado', 'vigente')),
            ])
            ->recordActions([
                // Ver y gestionar servicios (modal)
                Action::make('gestionar_servicios')
                    ->label('Ver Servicios')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->modalHeading(fn($record) => "Membresía: {$record->codigo_activacion}")
                    ->modalDescription(function ($record) {
                        $estado = $record->estado === 'vigente'
                            ? '🟢 Vigente'
                            : '🔴 Vencida';
                        $cliente = $record->cliente->nombre ?? '-';
                        $mascota = $record->mascota->nombre ?? '-';
                        $activacion = $record->fecha_activacion->format('d/m/Y');
                        $vencimiento = $record->fecha_vencimiento->format('d/m/Y');

                        return "Cliente: {$cliente} | Mascota: {$mascota} | {$estado} | Vigencia: {$activacion} - {$vencimiento}";
                    })
                    ->fillForm(function ($record) {
                        $data = [];
                        foreach ($record->servicios as $s) {
                            $data["servicio_{$s->id}"] = $s->realizado;
                        }
                        return $data;
                    })
                    ->form(function ($record) {
                        $fields = [];
                        $record->load('servicios');

                        foreach ($record->servicios as $servicio) {
                            $label = $servicio->nombre;
                            if ($servicio->realizado && $servicio->fecha_realizado) {
                                $label .= ' (✅ Realizado el ' . $servicio->fecha_realizado->format('d/m/Y') . ')';
                            }

                            $fields[] = Checkbox::make("servicio_{$servicio->id}")
                                ->label($label)
                                ->disabled($record->estado === 'vencida');
                        }

                        if ($record->estado === 'vencida') {
                            $fields[] = Placeholder::make('aviso_vencida')
                                ->label('')
                                ->content(new HtmlString(
                                    '<div style="color:#ef4444;font-weight:bold;padding:8px;background:#fef2f2;border-radius:6px;text-align:center;">⚠️ Membresía vencida — No se pueden modificar los servicios. Use el botón "Renovar" para reactivar.</div>'
                                ));
                        }

                        return $fields;
                    })
                    ->modalSubmitActionLabel('Guardar Cambios')
                    ->action(function ($record, array $data) {
                        if ($record->estado === 'vencida') {
                            Notification::make()
                                ->title('Membresía vencida')
                                ->body('No se pueden modificar servicios en una membresía vencida.')
                                ->danger()
                                ->send();
                            return;
                        }

                        foreach ($record->servicios as $servicio) {
                            $key = "servicio_{$servicio->id}";
                            $checked = $data[$key] ?? false;

                            $servicio->update([
                                'realizado' => $checked,
                                'fecha_realizado' => $checked ? ($servicio->fecha_realizado ?? now()) : null,
                            ]);
                        }

                        // If all services are completed, mark membership as vencida
                        $record->refresh();
                        $total = $record->servicios()->count();
                        $done = $record->servicios()->where('realizado', true)->count();

                        if ($total > 0 && $done === $total) {
                            $record->update(['estado' => 'vencida']);

                            Notification::make()
                                ->title('Todos los servicios completados')
                                ->body('La membresía ha sido marcada como vencida. Puede renovarla con un nuevo código.')
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Servicios actualizados correctamente')
                                ->success()
                                ->send();
                        }
                    }),

                // Renovar (solo si vencida)
                Action::make('renovar')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn($record) => $record->estado === 'vencida')
                    ->requiresConfirmation()
                    ->modalHeading('Renovar Membresía')
                    ->modalDescription('Ingrese el nuevo código de activación de 9 caracteres para renovar la membresía.')
                    ->form([
                        TextInput::make('nuevo_codigo')
                            ->label('Nuevo Código de Activación')
                            ->required()
                            ->length(9)
                            ->alphaNum()
                            ->unique('membresias', 'codigo_activacion')
                            ->placeholder('Ej: ABC123XYZ'),
                    ])
                    ->action(function ($record, array $data) {
                        $hoy = now();
                        $record->update([
                            'codigo_activacion' => strtoupper($data['nuevo_codigo']),
                            'fecha_activacion' => $hoy,
                            'fecha_vencimiento' => $hoy->copy()->addMonths(3),
                            'estado' => 'vigente',
                        ]);

                        // Reset all services
                        $record->servicios()->update([
                            'realizado' => false,
                            'fecha_realizado' => null,
                        ]);

                        Notification::make()
                            ->title('Membresía renovada exitosamente')
                            ->body("Nueva vigencia: {$hoy->format('d/m/Y')} - {$hoy->copy()->addMonths(3)->format('d/m/Y')}")
                            ->success()
                            ->send();
                    }),
                // Eliminar con contraseña de admin (siempre visible)
                Action::make('eliminar_membresia')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Membresía')
                    ->modalDescription('Esta acción es irreversible. Ingrese la contraseña del administrador para confirmar.')
                    ->form([
                        TextInput::make('password')
                            ->label('Contraseña del Administrador')
                            ->password()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = \App\Models\User::whereHas('nivel', fn($q) => $q->where('nombre', 'Administrador'))
                            ->first();

                        if (!$admin || !\Illuminate\Support\Facades\Hash::check($data['password'], $admin->password)) {
                            Notification::make()
                                ->title('Contraseña incorrecta')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->servicios()->delete();
                        $record->delete();

                        Notification::make()
                            ->title('Membresía eliminada correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                // Activar nueva membresía
                Action::make('activar_membresia')
                    ->label('Activar Membresía')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Activar Nueva Membresía')
                    ->form([
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(function () {
                                return \App\Models\Cliente::pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Select::make('mascota_id')
                            ->label('Mascota')
                            ->required()
                            ->options(function (Get $get): Collection {
                                $clienteId = $get('cliente_id');
                                if (!$clienteId)
                                    return collect();
                                return Mascota::where('cliente_id', $clienteId)
                                    ->pluck('nombre', 'id');
                            })
                            ->disabled(fn(Get $get): bool => !$get('cliente_id'))
                            ->hint(fn(Get $get) => !$get('cliente_id') ? 'Primero seleccione un cliente' : null)
                            ->live(),

                        TextInput::make('codigo_activacion')
                            ->label('Código de Activación (9 caracteres)')
                            ->required()
                            ->length(9)
                            ->alphaNum()
                            ->unique('membresias', 'codigo_activacion')
                            ->placeholder('Ej: ABC123XYZ'),
                    ])
                    ->action(function (array $data) {
                        $hoy = now();

                        $membresia = Membresia::create([
                            'cliente_id' => $data['cliente_id'],
                            'mascota_id' => $data['mascota_id'],
                            'codigo_activacion' => strtoupper($data['codigo_activacion']),
                            'fecha_activacion' => $hoy,
                            'fecha_vencimiento' => $hoy->copy()->addMonths(3),
                            'estado' => 'vigente',
                        ]);

                        // Create the 3 services
                        Membresia::crearServicios($membresia);

                        Notification::make()
                            ->title('Membresía activada exitosamente')
                            ->body("Código: {$membresia->codigo_activacion} | Vigente hasta: {$membresia->fecha_vencimiento->format('d/m/Y')}")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembresias::route('/'),
        ];
    }
}
