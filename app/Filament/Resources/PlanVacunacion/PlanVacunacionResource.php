<?php

namespace App\Filament\Resources\PlanVacunacion;

use App\Filament\Resources\PlanVacunacion\Pages\ListPlanVacunaciones;
use App\Models\Mascota;
use App\Models\PlanVacunacion;
use App\Models\PlanVacunacionTipo;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
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

class PlanVacunacionResource extends Resource
{
    protected static ?string $model = PlanVacunacion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Plan de Vacunación';
    protected static ?string $modelLabel = 'Plan de Vacunación';
    protected static ?string $pluralModelLabel = 'Planes de Vacunación';
    protected static ?string $slug = 'plan-vacunacion';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tipo', 'cliente', 'mascota'])
            ->withCount([
                'dosis as dosis_total',
                'dosis as dosis_realizadas' => fn($q) => $q->where('realizado', true),
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
                TextColumn::make('tipo.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Perro' => 'info',
                        'Gato' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mascota.nombre')
                    ->label('Mascota')
                    ->searchable(),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('vacunas_pendientes')
                    ->label('Vacunas')
                    ->state(function (PlanVacunacion $record): string {
                        $total = $record->dosis_total ?? 0;
                        $done = $record->dosis_realizadas ?? 0;
                        $pending = $total - $done;
                        return "{$done}/{$total} (faltan {$pending})";
                    })
                    ->badge()
                    ->color(function (PlanVacunacion $record): string {
                        $total = $record->dosis_total ?? 0;
                        $done = $record->dosis_realizadas ?? 0;
                        if ($done === $total && $total > 0)
                            return 'success';
                        if ($done > 0)
                            return 'warning';
                        return 'gray';
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'completado' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('solo_activos')
                    ->label('Solo activos')
                    ->toggle()
                    ->default(true)
                    ->query(fn(Builder $query) => $query->where('estado', 'activo')),
            ])
            ->recordActions([
                // Ver plan y gestionar dosis (modal)
                Action::make('ver_plan')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn($record) => "Plan de Vacunación — {$record->tipo->nombre}")
                    ->modalWidth('2xl')
                    ->modalDescription(function ($record) {
                        $cliente = $record->cliente->nombre ?? '-';
                        $mascota = $record->mascota->nombre ?? '-';
                        $inicio = $record->fecha_inicio->format('d/m/Y');
                        $total = $record->dosis()->count();
                        $done = $record->dosis()->where('realizado', true)->count();

                        return "Cliente: {$cliente} | Mascota: {$mascota} | Inicio: {$inicio} | Progreso: {$done}/{$total}";
                    })
                    ->fillForm(function ($record) {
                        $data = [];
                        // Explicitly query only dosis for THIS plan
                        $dosis = \App\Models\PlanVacunacionDosis::where('plan_id', $record->id)
                            ->orderBy('semana')
                            ->get();
                        foreach ($dosis as $d) {
                            $data["dosis_{$d->id}"] = $d->realizado;
                        }
                        return $data;
                    })
                    ->form(function ($record) {
                        $fields = [];
                        $record->load(['cliente', 'mascota']);
                        $telefono = $record->cliente->telefono ?? null;

                        // Explicitly query dosis for THIS plan only
                        $dosisList = \App\Models\PlanVacunacionDosis::where('plan_id', $record->id)
                            ->orderBy('semana')
                            ->get();

                        foreach ($dosisList as $dosis) {
                            $fechaProg = $dosis->fecha_programada->format('d/m/Y');
                            $label = "Semana {$dosis->semana} — {$dosis->nombre} (Fecha: {$fechaProg})";

                            if ($dosis->realizado && $dosis->fecha_realizado) {
                                $label .= ' ✅ ' . $dosis->fecha_realizado->format('d/m/Y');
                            }

                            $fields[] = Checkbox::make("dosis_{$dosis->id}")
                                ->label($label)
                                ->disabled($record->estado === 'completado');

                            // WhatsApp button for pending doses
                            if (!$dosis->realizado && $telefono) {
                                $mascotaNombre = $record->mascota->nombre ?? 'su mascota';
                                $msg = urlencode(
                                    "Buenos días, le hablamos de VetMora. A su querida mascota {$mascotaNombre} le toca: {$dosis->nombre} (Semana {$dosis->semana}, programada para el {$fechaProg}). ¡Le esperamos!"
                                );
                                $tel = preg_replace('/[^0-9]/', '', $telefono);
                                $waUrl = "https://wa.me/{$tel}?text={$msg}";

                                $fields[] = Placeholder::make("wa_{$dosis->id}")
                                    ->label('')
                                    ->content(new HtmlString(
                                        "<a href=\"{$waUrl}\" target=\"_blank\" style=\"display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:#25D366;color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;margin-bottom:8px;\">" .
                                        "📱 Enviar recordatorio por WhatsApp — {$dosis->nombre}</a>"
                                    ));
                            }
                        }

                        if ($record->estado === 'completado') {
                            $fields[] = Placeholder::make('aviso_completado')
                                ->label('')
                                ->content(new HtmlString(
                                    '<div style="color:#3b82f6;font-weight:bold;padding:8px;background:#eff6ff;border-radius:6px;text-align:center;">✅ Plan de vacunación completado</div>'
                                ));
                        }

                        return $fields;
                    })
                    ->modalSubmitActionLabel('Guardar Cambios')
                    ->action(function ($record, array $data) {
                        if ($record->estado === 'completado') {
                            Notification::make()
                                ->title('Plan ya completado')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Explicitly query dosis for THIS plan only
                        $dosisList = \App\Models\PlanVacunacionDosis::where('plan_id', $record->id)->get();

                        foreach ($dosisList as $dosis) {
                            /** @var \App\Models\PlanVacunacionDosis $dosis */
                            $key = "dosis_{$dosis->id}";
                            $checked = $data[$key] ?? false;

                            $dosis->update([
                                'realizado' => $checked,
                                'fecha_realizado' => $checked ? ($dosis->fecha_realizado ?? now()) : null,
                            ]);
                        }

                        // Check if all doses are complete for THIS plan only
                        $total = \App\Models\PlanVacunacionDosis::where('plan_id', $record->id)->count();
                        $done = \App\Models\PlanVacunacionDosis::where('plan_id', $record->id)->where('realizado', true)->count();

                        if ($total > 0 && $done === $total) {
                            $record->update(['estado' => 'completado']);

                            Notification::make()
                                ->title('¡Plan de vacunación completado!')
                                ->body('Todas las vacunas han sido aplicadas.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Vacunas actualizadas')
                                ->success()
                                ->send();
                        }
                    }),

                // Eliminar con contraseña de admin
                Action::make('eliminar_plan')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Plan de Vacunación')
                    ->modalDescription('Ingrese la contraseña del administrador para confirmar.')
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

                        $record->dosis()->delete();
                        $record->delete();

                        Notification::make()
                            ->title('Plan eliminado correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                // Crear nuevo plan
                Action::make('crear_plan')
                    ->label('Nuevo Plan de Vacunación')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Crear Plan de Vacunación')
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

                        Select::make('tipo_id')
                            ->label('Plan de Vacunación')
                            ->options(function () {
                                return PlanVacunacionTipo::pluck('nombre', 'id');
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Show preview of vaccines
                            }),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->native(false)
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $plan = PlanVacunacion::create([
                            'cliente_id' => $data['cliente_id'],
                            'mascota_id' => $data['mascota_id'],
                            'tipo_id' => $data['tipo_id'],
                            'fecha_inicio' => $data['fecha_inicio'],
                            'estado' => 'activo',
                        ]);

                        // Create doses from template
                        PlanVacunacion::crearDosis($plan);

                        $tipoNombre = PlanVacunacionTipo::find($data['tipo_id'])->nombre ?? '';
                        $totalDosis = $plan->dosis()->count();

                        Notification::make()
                            ->title('Plan de vacunación creado')
                            ->body("Plan {$tipoNombre} con {$totalDosis} vacunas programadas.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlanVacunaciones::route('/'),
        ];
    }
}
