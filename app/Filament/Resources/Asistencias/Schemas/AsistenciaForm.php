<?php

namespace App\Filament\Resources\Asistencias\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AsistenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('ID de Empleado')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('check_in')
                    ->label('Entrada')
                    ->required(),
                DateTimePicker::make('check_out')
                    ->label('Salida'),
            ]);
    }
}
