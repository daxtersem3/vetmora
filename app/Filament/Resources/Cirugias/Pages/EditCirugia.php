<?php

namespace App\Filament\Resources\Cirugias\Pages;

use App\Filament\Resources\Cirugias\CirugiaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCirugia extends EditRecord
{
    protected static string $resource = CirugiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
