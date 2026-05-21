<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class ActiveStaffWidget extends BaseWidget
{
    protected static ?string $heading = 'Personal Activo (En turno)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->whereHas('asistencias', function ($query) {
                    $query->whereDate('check_in', Carbon::today())
                        ->whereNull('check_out');
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('datosPersonales.cedula')
                    ->label('Cédula'),
                Tables\Columns\TextColumn::make('asistencias.check_in') // Getting the latest check_in might need a custom attribute or subquery relation
                    ->label('Hora de Entrada')
                    ->state(function (User $record) {
                        return $record->asistencias()
                            ->whereDate('check_in', Carbon::today())
                            ->whereNull('check_out')
                            ->latest('check_in')
                            ->first()
                            ?->check_in
                            ->format('H:i:s');
                    }),
            ]);
    }
}
