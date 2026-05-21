<?php

namespace App\Filament\Resources\HistorialMedico\Pages;

use App\Filament\Resources\HistorialMedico\HistorialMedicoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHistorialMedico extends CreateRecord
{
    protected static string $resource = HistorialMedicoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
