<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?string $modelLabel = 'Registro de Auditoría';

    protected static ?string $pluralModelLabel = 'Historial de Auditoría';

    // Bloqueamos creación, edición y borrado para mantener la integridad del historial
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->placeholder('Sistema')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Módulo')
                    ->formatStateUsing(fn(?string $state): string => $state ? class_basename($state) : 'Sistema')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Acción')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'created' => 'Creación',
                        'updated' => 'Modificación',
                        'deleted' => 'Eliminación',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('properties.attributes')
                    ->label('Datos Registrados / Nuevos')
                    ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : $state)
                    ->limit(50)
                    ->tooltip(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('description')
                    ->label('Tipo de Acción')
                    ->options([
                        'created' => 'Creación',
                        'updated' => 'Modificación',
                        'deleted' => 'Eliminación',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
