<?php

namespace App\Filament\Resources\Cirugias\Pages;

use App\Filament\Resources\Cirugias\CirugiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCirugias extends ListRecords
{
    protected static string $resource = CirugiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Crear Cirugía'),
        ];
    }
}
