<?php

namespace App\Filament\Resources\Asistencias;

use App\Filament\Resources\Asistencias\Pages\CreateAsistencia;
use App\Filament\Resources\Asistencias\Pages\EditAsistencia;
use App\Filament\Resources\Asistencias\Pages\ListAsistencias;
use App\Models\Asistencia;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->nivel_id === 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->label('Empleado')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('check_in')
                    ->label('Entrada')
                    ->required(),
                DateTimePicker::make('check_out')
                    ->label('Salida'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label('Entrada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->label('Salida')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('check_in', 'desc')
            ->filters([
                Filter::make('rango_fecha')
                    ->label('Rango de Fecha')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->default(Carbon::today()),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->default(Carbon::today()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn(Builder $q, $date): Builder => $q->whereDate('check_in', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn(Builder $q, $date): Builder => $q->whereDate('check_in', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: ' . Carbon::parse($data['desde'])->toFormattedDateString();
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: ' . Carbon::parse($data['hasta'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAsistencias::route('/'),
            'create' => CreateAsistencia::route('/create'),
            'edit' => EditAsistencia::route('/{record}/edit'),
        ];
    }
}
