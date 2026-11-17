<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Organizamos los campos dentro de una sección visual limpia
                Section::make('Información General del Cliente')
                    ->description('Introduce los datos fiscales y de contacto del cliente.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej. Inversiones Newport C.A.'),

                        TextInput::make('tax_id')
                            ->label('RIF / Documento de Identidad')
                            ->required()
                            ->unique(ignoreRecord: true) // Evita RIFs duplicados en la base de datos
                            ->maxLength(255)
                            ->placeholder('Ej. J-123456789'),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email() // Valida que tenga formato de correo real
                            ->maxLength(255)
                            ->placeholder('ejemplo@cliente.com'),

                        TextInput::make('phone')
                            ->label('Teléfono de Contacto')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Ej. 0412-1234567'),

                        Textarea::make('direccion')
                            ->label('Dirección Fiscal')
                            ->maxLength(65535)
                            ->columnSpanFull(), // Ocupa todo el ancho del formulario
                    ])->columns(2), // Organiza los campos en 2 columnas
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable() // Añade barra de búsqueda para este campo
                    ->sortable(),  // Permite ordenar alfabéticamente

                TextColumn::make('tax_id')
                    ->label('RIF / Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono'),

                TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y h:i A') // Formato de fecha legible
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Opción de ocultar la columna
            ])
            ->filters([
                // Espacio listo para añadir filtros más adelante (ej. por estado o mes)
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Botón de editar directo en la fila
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Permite borrar varios a la vez
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
