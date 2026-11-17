<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Proveedor')
                    ->description('Registra los datos fiscales y de contacto del proveedor para tus gastos.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre / Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej. Corporación de Servicios Electricos'),

                        TextInput::make('tax_id')
                            ->label('RIF / Documento de Identidad')
                            ->required()
                            ->unique(ignoreRecord: true) // Evita registrar el mismo proveedor dos veces
                            ->maxLength(255)
                            ->placeholder('Ej. J-987654321'),

                        TextInput::make('contact_email')
                            ->label('Correo Electrónico de Contacto')
                            ->email() // Valida el formato de correo
                            ->maxLength(255)
                            ->placeholder('proveedor@empresa.com'),
                    ])->columns(2), // Divide la sección en 2 columnas visuales
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Proveedor / Empresa')
                    ->searchable() // Habilita la barra de búsqueda para este campo
                    ->sortable(),  // Permite ordenar de la A a la Z

                TextColumn::make('tax_id')
                    ->label('RIF / Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_email')
                    ->label('Correo Electrónico')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Alta')
                    ->dateTime('d/m/Y h:i A') // Formato legible para el usuario
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto para limpiar la vista
            ])
            ->filters([
                // Espacio libre para filtros avanzados más adelante
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Botón de edición rápida en la fila
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Permite borrar en lote
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
