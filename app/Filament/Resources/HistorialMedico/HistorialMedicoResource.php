<?php

namespace App\Filament\Resources\HistorialMedico;

use App\Filament\Resources\HistorialMedico\Pages\CreateHistorialMedico;
use App\Filament\Resources\HistorialMedico\Pages\EditHistorialMedico;
use App\Filament\Resources\HistorialMedico\Pages\ListHistorialMedico;
use App\Models\Cita;
use App\Models\HistorialMedico as HistorialMedicoModel;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class HistorialMedicoResource extends Resource
{
    protected static ?string $model = HistorialMedicoModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): ?string
    {
        return 'Historial Médico';
    }

    protected static ?string $navigationLabel = 'Historial Médico';
    protected static ?string $modelLabel = 'Historial Médico';
    protected static ?string $pluralModelLabel = 'Historial Médico';

    // ── Eager loading ────────────────────────────────────────────────────────
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['cita.cliente', 'cita.mascota', 'cita.veterinario']);
    }

    // ── Acceso por rol ──────────────────────────────────────────────────────
    private static function nivelUsuario(): string
    {
        return auth()->user()?->nivel?->nombre ?? '';
    }

    public static function canCreate(): bool
    {
        return in_array(static::nivelUsuario(), ['Administrador', 'Veterinario']);
    }

    public static function canEdit($record): bool
    {
        return in_array(static::nivelUsuario(), ['Administrador', 'Veterinario']);
    }

    public static function canDelete($record): bool
    {
        return in_array(static::nivelUsuario(), ['Administrador', 'Veterinario']);
    }

    // ── Formulario ──────────────────────────────────────────────────────────
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('cita_id')
                ->label('Cita')
                ->options(function (?HistorialMedicoModel $record) {
                    return Cita::with(['cliente', 'mascota'])
                        ->doesntHave('historialMedico')
                        ->when($record, fn($q) => $q->orWhere('id', $record->cita_id))
                        ->get()
                        ->mapWithKeys(fn($c) =>
                            [$c->id => "#{$c->id} - {$c->cliente?->nombre} / {$c->mascota?->nombre} ({$c->fecha_hora?->format('d/m/Y H:i')})"])
                        ->toArray();
                })
                ->searchable()
                ->required(),

            DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->native(false)
                ->default(now()),

            Textarea::make('sintomas')->label('Síntomas')->rows(3)->columnSpanFull(),
            Textarea::make('diagnostico')->label('Diagnóstico')->rows(3)->columnSpanFull(),
            Textarea::make('tratamiento')->label('Tratamiento')->rows(3)->columnSpanFull(),
            Textarea::make('observaciones')->label('Observaciones')->rows(3)->columnSpanFull(),

            TextInput::make('peso')->label('Peso (kg)')->numeric()->step(0.01),
            TextInput::make('temperatura')->label('Temperatura (°C)')->numeric()->step(0.01),

            CheckboxList::make('sistemas_evaluados')
                ->label('Sistemas Evaluados')
                ->options([
                    'Tegumentario' => 'Tegumentario',
                    'Ocular' => 'Ocular',
                    'Respiratorio' => 'Respiratorio',
                    'Cardíaco' => 'Cardíaco',
                    'Nervioso' => 'Nervioso',
                    'Reproductivo' => 'Reproductivo',
                    'Urinario' => 'Urinario',
                    'Oftalmológico' => 'Oftalmológico',
                    'Derm.' => 'Derm.',
                    'Digestivo' => 'Digestivo',
                    'Inmunización' => 'Inmunización',
                    'Linfático' => 'Linfático',
                    'FAC' => 'FAC',
                    'FAT' => 'FAT',
                    'Reflejo T' => 'Reflejo T',
                    'TLC' => 'TLC',
                ])
                ->columns(4)->columnSpanFull(),

            CheckboxList::make('examenes_realizados')
                ->label('Exámenes Realizados')
                ->options([
                    'Hematología' => 'Hematología',
                    'Hematozoarios' => 'Hematozoarios',
                    'Química' => 'Química',
                    'Prueba Rápida' => 'Prueba Rápida',
                    'Orina' => 'Orina',
                    'Coprológico' => 'Coprológico',
                    'Ecografía' => 'Ecografía',
                    'Rayos X' => 'Rayos X',
                    'Respiran' => 'Respiran',
                    'Antibiograma' => 'Antibiograma',
                ])
                ->columns(4)->columnSpanFull(),
        ]);
    }

    // ── Tabla ───────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cita.cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('cita.mascota.nombre')->label('Mascota')->searchable(),
                TextColumn::make('cita.veterinario.name')->label('Veterinario'),
                TextColumn::make('fecha')->label('Fecha')->date()->sortable(),
                TextColumn::make('diagnostico')->label('Diagnóstico')->limit(40),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                Filter::make('fecha_rango')
                    ->label('Rango de fechas')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false)
                            ->maxDate(now()),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false)
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn($q, $v) => $q->whereDate('fecha', '>=', $v))
                            ->when($data['hasta'], fn($q, $v) => $q->whereDate('fecha', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $ind = [];
                        if ($data['desde'])
                            $ind[] = 'Desde: ' . $data['desde'];
                        if ($data['hasta'])
                            $ind[] = 'Hasta: ' . $data['hasta'];
                        return $ind;
                    }),

                Filter::make('hoy')
                    ->label('Solo hoy')
                    ->query(fn(Builder $q) => $q->whereDate('fecha', now()))
                    ->toggle(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                // ── PDF ──────────────────────────────────────────────
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn($record) => route('historial-medico.pdf', $record))
                    ->openUrlInNewTab(),


                // ── Editar con contraseña ─────────────────────────
                Action::make('editar_historial')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->visible(fn() => static::canEdit(null))
                    ->form([
                        TextInput::make('password')
                            ->label('Contraseña del Administrador')
                            ->password()
                            ->required(),
                    ])
                    ->modalHeading('Verificar identidad')
                    ->modalDescription('Ingrese la contraseña del administrador para editar este historial.')
                    ->action(function ($record, array $data) {
                        $admin = User::whereHas('nivel', fn($q) => $q->where('nombre', 'Administrador'))->first();
                        if (!$admin || !Hash::check($data['password'], $admin->password)) {
                            Notification::make()->title('Contraseña incorrecta')->danger()->send();
                            return;
                        }
                        redirect(static::getUrl('edit', ['record' => $record]));
                    }),

                // ── Eliminar con contraseña ───────────────────────
                Action::make('eliminar_historial')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn() => static::canDelete(null))
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Historial Médico')
                    ->modalDescription('Ingrese la contraseña del administrador para confirmar la eliminación.')
                    ->form([
                        TextInput::make('password')
                            ->label('Contraseña del Administrador')
                            ->password()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $admin = User::whereHas('nivel', fn($q) => $q->where('nombre', 'Administrador'))->first();
                        if (!$admin || !Hash::check($data['password'], $admin->password)) {
                            Notification::make()->title('Contraseña incorrecta')->danger()->send();
                            return;
                        }
                        $record->delete();
                        Notification::make()->title('Historial eliminado')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHistorialMedico::route('/'),
            'create' => CreateHistorialMedico::route('/create'),
            'edit' => EditHistorialMedico::route('/{record}/edit'),
        ];
    }
}
