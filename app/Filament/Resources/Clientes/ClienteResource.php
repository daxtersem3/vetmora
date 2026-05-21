<?php

namespace App\Filament\Resources\Clientes;

use App\Filament\Resources\Clientes\Pages\CreateCliente;
use App\Filament\Resources\Clientes\Pages\EditCliente;
use App\Filament\Resources\Clientes\Pages\ListClientes;
use App\Models\Cliente;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount('mascotas');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Clientes';
    }


    protected static ?string $navigationLabel = 'Lista de Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),
            TextInput::make('cedula')
                ->label('Cédula')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),
            TextInput::make('correo')
                ->label('Correo')
                ->email()
                ->maxLength(255),
            TextInput::make('telefono')
                ->label('Teléfono')
                ->tel()
                ->maxLength(20),
            TextInput::make('direccion')
                ->label('Dirección')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('cedula')->label('Cédula')->searchable(),
                TextColumn::make('correo')->label('Correo')->searchable(),
                TextColumn::make('telefono')->label('Teléfono'),
                TextColumn::make('mascotas_count')
                    ->label('Mascotas')
                    ->counts('mascotas')
                    ->sortable(),
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
            'index' => ListClientes::route('/'),
            'create' => CreateCliente::route('/create'),
            'edit' => EditCliente::route('/{record}/edit'),
        ];
    }
}
