<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;


class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Gasto')
                    ->description('Registra los detalles financieros y el comprobante de respaldo.')
                    ->schema([
                        // Campo oculto que toma el ID del usuario autenticado
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id())
                            ->required(),

                        Forms\Components\TextInput::make('concept')
                            ->label('Concepto / Descripción')
                            ->placeholder('Ej: Pago de servicio eléctrico o compra de equipos')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('type', 'expense')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de Categoría')
                                    ->required(),
                                Forms\Components\Hidden::make('type')
                                    ->default('expense'),
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Color en Gráficos'),
                            ]),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Proveedor (Opcional)')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->required(),

                        Forms\Components\DatePicker::make('expense_date')
                            ->label('Fecha del Gasto')
                            ->default(now())
                            ->maxDate(now())
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente de Aprobación',
                                'approved' => 'Aprobado',
                                'rejected' => 'Rechazado',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('Comprobante (PDF o Imagen)')
                            ->directory('receipts')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120) // Máximo 5MB
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('concept')
                    ->label('Concepto')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->placeholder('Gasto interno')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('receipt_path')
                    ->label('Recibo')
                    ->icon(fn($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-x-mark')
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendientes',
                        'approved' => 'Aprobados',
                        'rejected' => 'Rechazados',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                // Botón rápido para aprobar gastos pendientes
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Gasto')
                    ->modalDescription('¿Confirmas que este gasto ha sido revisado y aprobado?')
                    ->visible(function (Expense $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $record->status === 'pending' && ($user?->hasRole('super_admin') ?? false);
                    })
                    ->action(function (Expense $record) {
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Gasto aprobado correctamente')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
