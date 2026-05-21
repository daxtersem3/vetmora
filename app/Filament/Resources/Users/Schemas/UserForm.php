<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    protected static array $preguntasDisponibles = [
        '¿Cuál es el nombre de tu primera mascota?' => '¿Cuál es el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?' => '¿En qué ciudad naciste?',
        '¿Cuál es el nombre de tu madre?' => '¿Cuál es el nombre de tu madre?',
        '¿Cuál era el nombre de tu escuela primaria?' => '¿Cuál era el nombre de tu escuela primaria?',
        '¿Cuál es tu comida favorita?' => '¿Cuál es tu comida favorita?',
        '¿Cuál es el nombre de tu mejor amigo de infancia?' => '¿Cuál es el nombre de tu mejor amigo de infancia?',
        '¿Cuál fue tu primer trabajo?' => '¿Cuál fue tu primer trabajo?',
        '¿Cuál es el apodo de tu abuela?' => '¿Cuál es el apodo de tu abuela?',
        '¿Cuál es el modelo de tu primer carro?' => '¿Cuál es el modelo de tu primer carro?',
        '¿Cuál es tu película favorita?' => '¿Cuál es tu película favorita?',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'users', column: 'email', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Este correo electrónico ya está registrado.',
                    ]),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Select::make('nivel_id')
                    ->relationship('nivel', 'nombre')
                    ->createOptionForm([
                        TextInput::make('nombre')->required(),
                    ])
                    ->required(),

                Section::make('Datos Personales')
                    ->relationship('datosPersonales')
                    ->schema([
                        TextInput::make('cedula')->required(),
                        TextInput::make('direccion'),
                        TextInput::make('tipo_sangre'),
                        \Filament\Forms\Components\FileUpload::make('foto_path')
                            ->image()
                            ->disk('public')
                            ->directory('empleados')
                            ->visibility('public'),
                    ]),

                Section::make('Preguntas de Seguridad')
                    ->description('Se usan para recuperar tu contraseña si la olvidas. Las respuestas se guardan cifradas.')
                    ->relationship('preguntaSeguridad')
                    ->schema([
                        Select::make('pregunta_1')
                            ->label('Pregunta 1')
                            ->options(static::$preguntasDisponibles)
                            ->required(),
                        TextInput::make('respuesta_1')
                            ->label('Respuesta 1')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->placeholder('Escribe tu respuesta (se guarda cifrada)'),
                        Select::make('pregunta_2')
                            ->label('Pregunta 2')
                            ->options(static::$preguntasDisponibles)
                            ->required()
                            ->different('pregunta_1'),
                        TextInput::make('respuesta_2')
                            ->label('Respuesta 2')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->placeholder('Escribe tu respuesta (se guarda cifrada)'),
                    ]),
            ]);
    }
}
