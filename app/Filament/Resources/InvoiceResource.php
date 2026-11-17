<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;


class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles de la Factura de Venta')
                    ->description('Asocia un cliente y registra los montos fiscales de la venta.')
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nombre') // Conecta automáticamente con el Modelo Cliente
                            ->searchable() // Permite buscar clientes escribiendo su nombre
                            ->preload() // Precarga la lista para mayor velocidad
                            ->required(),

                        TextInput::make('numero_factura')
                            ->label('Número de Factura')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Ej. FAC-0001'),

                        DatePicker::make('fecha_emision')
                            ->label('Fecha de Emisión')
                            ->required()
                            ->default(now()), // Por defecto pone la fecha de hoy

                        DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required(),

                        TextInput::make('subtotal')
                            ->numeric()
                            ->required()
                            ->prefix('$') // Añade el símbolo de moneda visualmente
                            ->placeholder('0.00'),

                        TextInput::make('impuestos')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('$'),

                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Se calculará automáticamente'),

                        Select::make('estado')
                            ->options([
                                'Pendiente' => 'Pendiente por Cobrar',
                                'Pagada' => 'Pagada',
                                'Vencida' => 'Vencida / Expirada',
                                'Anulada' => 'Anulada',
                            ])
                            ->required()
                            ->default('Pendiente'),

                        Textarea::make('notas')
                            ->label('Notas u Observaciones')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name') // Muestra directamente el nombre del cliente relacionado
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->sortable(),


                TextColumn::make('status')
                    ->badge() // Convierte el texto en una etiqueta de color
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',     // Verde
                        'pending' => 'warning',   // Amarillo/Naranja
                        'overdue' => 'danger',      // Rojo
                        'cancelled' => 'gray',        // Gris
                    })
                    ->sortable(),
            ])
            ->filters([
                // Espacio ideal para filtrar por estados (Vencidas, Pagadas) que añadiremos después
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function (Invoice $record) {
                        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $record]);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            "Factura-{$record->invoice_number}.pdf"
                        );
                    }),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
