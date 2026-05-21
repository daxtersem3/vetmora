<?php

namespace App\Filament\Resources\HistorialMedico\Pages;

use App\Filament\Resources\HistorialMedico\HistorialMedicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHistorialMedico extends ListRecords
{
    protected static string $resource = HistorialMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo Historial'),
        ];
    }
}
