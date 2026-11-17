<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CashFlowChart extends ChartWidget
{
    protected static ?string $heading = 'Flujo de Caja Anual (Ingresos vs. Gastos)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $incomeData = Trend::query(Invoice::where('status', 'paid'))
            ->dateColumn('issue_date')
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('total');

        $expenseData = Trend::query(Expense::where('status', 'approved'))
            ->dateColumn('expense_date')
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos ($)',
                    'data' => $incomeData->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981', // Línea Verde
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Gastos ($)',
                    'data' => $expenseData->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => '#ef4444', // Línea Roja
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
