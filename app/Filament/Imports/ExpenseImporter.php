<?php

namespace App\Filament\Imports;

use App\Models\Expense;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;

class ExpenseImporter extends Importer
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('concept')
                ->label('Concepto')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('amount')
                ->label('Monto')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0.01']),

            ImportColumn::make('expense_date')
                ->label('Fecha (AAAA-MM-DD)')
                ->requiredMapping()
                ->rules(['required', 'date']),

            ImportColumn::make('category')
                ->label('Categoría')
                ->requiredMapping()
                ->relationship(resolveUsing: 'name')
                ->rules(['required']),

            ImportColumn::make('supplier')
                ->label('Proveedor (Opcional)')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('status')
                ->label('Estado (pending, approved, rejected)')
                ->rules(['nullable', 'in:pending,approved,rejected']),
        ];
    }

    public function resolveRecord(): ?Expense
    {
        $expense = new Expense();
        $expense->user_id = Auth::id() ?? 1;
        $expense->status = $this->data['status'] ?? 'pending';

        return $expense;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de gastos finalizó: ' . number_format($import->successful_rows) . ' ' . str('registro')->plural($import->successful_rows) . ' importados.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron por errores de formato.';
        }

        return $body;
    }
}
