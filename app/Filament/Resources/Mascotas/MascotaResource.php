<?php

namespace App\Filament\Resources\Mascotas;

use App\Filament\Resources\Mascotas\Pages\CreateMascota;
use App\Filament\Resources\Mascotas\Pages\EditMascota;
use App\Filament\Resources\Mascotas\Pages\ListMascotas;
use App\Models\Mascota;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MascotaResource extends Resource
{
    protected static ?string $model = Mascota::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['cliente']);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Clientes';
    }


    protected static ?string $navigationLabel = 'Lista de Mascotas';

    protected static ?string $modelLabel = 'Mascota';

    protected static ?string $pluralModelLabel = 'Mascotas';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('nombre')
                ->label('Nombre de la Mascota')
                ->required()
                ->validationMessages(['required' => 'El nombre de la mascota es obligatorio.'])
                ->maxLength(100),
            TextInput::make('especie')
                ->label('Especie')
                ->required()
                ->validationMessages(['required' => 'La especie es obligatoria.'])
                ->maxLength(100),
            TextInput::make('raza')
                ->label('Raza')
                ->maxLength(100),
            DatePicker::make('fecha_nacimiento')
                ->label('Fecha de Nacimiento')
                ->native(false),
            Select::make('cliente_id')
                ->label('Seleccione el Cliente')
                ->relationship('cliente', 'nombre')
                ->searchable()
                ->preload()
                ->required()
                ->validationMessages(['required' => 'Debe seleccionar un cliente.']),
            \Filament\Forms\Components\FileUpload::make('foto')
                ->label('Foto de la Mascota (opcional)')
                ->image()
                ->imageResizeMode('cover')
                ->imageCropAspectRatio('1:1')
                ->disk('public')
                ->directory('mascotas')
                ->visibility('public')
                ->nullable(),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('especie')->label('Especie')->searchable(),
                TextColumn::make('raza')->label('Raza'),
                TextColumn::make('fecha_nacimiento')->label('Fecha Nacimiento')->date(),
                TextColumn::make('cliente.nombre')->label('Propietario')->searchable()->sortable(),
            ])
            ->defaultSort('nombre')
            ->recordActions([
                \Filament\Actions\Action::make('historial')
                    ->label('Historial')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'Historial Médico - ' . $record->nombre)
                    ->modalContent(fn ($record) => view('filament.components.historial-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMascotas::route('/'),
            'create' => CreateMascota::route('/create'),
            'edit' => EditMascota::route('/{record}/edit'),
        ];
    }
}
