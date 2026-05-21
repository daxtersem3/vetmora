<?php

namespace App\Filament\Widgets;

use App\Models\Cita;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CitasPendientesWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Citas Pendientes')
            ->query(
                fn() => Cita::with(['cliente', 'mascota', 'veterinario'])
                    ->where('estado', 'pendiente')
                    ->where('fecha_hora', '>=', now())
                    ->orderBy('fecha_hora', 'asc')
            )
            ->columns([
                TextColumn::make('fecha_hora')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('mascota.nombre')
                    ->label('Mascota'),
                TextColumn::make('veterinario.name')
                    ->label('Veterinario'),
                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(40),
            ])
            ->paginated(false);
    }
}

