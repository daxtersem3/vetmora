<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BackedEnum;


class RespaldoBD extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Respaldo BD';

    protected static ?string $title = 'Respaldo de Base de Datos';

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.pages.respaldo-bd';

    /**
     * Only nivel_id = 1 (Administrador) can see this page.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->nivel_id === 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargar')
                ->label('Descargar Respaldo')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('db.backup.download'))
                ->openUrlInNewTab(false)
                ->extraAttributes(['style' => 'display:none']),

            Action::make('importar')
                ->label('Importar Respaldo')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Importar archivo .sql')
                ->modalDescription('⚠️ Esta acción reemplazará los datos actuales de la base de datos. Asegúrate de tener un respaldo antes de continuar.')
                ->modalWidth('lg')
                ->form([
                    FileUpload::make('sql_file')
                        ->label('Archivo .sql')
                        ->disk('local')
                        ->directory('db-imports')
                        ->visibility('private')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $disk = \Illuminate\Support\Facades\Storage::disk('local');

                    if (!$disk->exists($data['sql_file'])) {
                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body('No se encontró el archivo subido.')
                            ->send();
                        return;
                    }

                    $path = $disk->path($data['sql_file']);

                    // Validate by extension
                    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'sql') {
                        Notification::make()
                            ->danger()
                            ->title('Archivo inválido')
                            ->body('Solo se permiten archivos con extensión .sql')
                            ->send();
                        $disk->delete($data['sql_file']);
                        return;
                    }

                    $sql = $disk->get($data['sql_file']);

                    if (empty(trim($sql))) {
                        Notification::make()
                            ->danger()
                            ->title('Archivo vacío')
                            ->body('El archivo .sql no contiene datos.')
                            ->send();
                        $disk->delete($data['sql_file']);
                        return;
                    }

                    try {
                        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
                        \DB::unprepared($sql);
                        \DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');

                        $disk->delete($data['sql_file']); // Clean up

                        Notification::make()
                            ->success()
                            ->title('Restauración exitosa')
                            ->body('La base de datos ha sido restaurada correctamente.')
                            ->send();
                    } catch (\Throwable $e) {
                        \DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
                        $disk->delete($data['sql_file']);

                        Notification::make()
                            ->danger()
                            ->title('Error al importar')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->extraAttributes(['style' => 'display:none']),
        ];
    }
}
