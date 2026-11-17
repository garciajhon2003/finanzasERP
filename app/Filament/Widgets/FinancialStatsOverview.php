<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Ingresos cobrados este mes
        $monthlyIncome = Invoice::where('status', 'paid')
            ->whereMonth('issue_date', now()->month)
            ->whereYear('issue_date', now()->year)
            ->sum('total');

        // Gastos aprobados este mes
        $monthlyExpenses = Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $netBalance = $monthlyIncome - $monthlyExpenses;

        // Facturas por cobrar (pendientes o vencidas)
        $accountsReceivable = Invoice::whereIn('status', ['pending', 'overdue'])->sum('total');
        $overdueCount = Invoice::where('status', 'overdue')->count();

        // Gastos pendientes de aprobación
        $pendingExpensesAmount = Expense::where('status', 'pending')->sum('amount');
        $pendingExpensesCount = Expense::where('status', 'pending')->count();

        return [
            Stat::make('Balance Mensual Neto', '$' . number_format($netBalance, 2))
                ->description('Ingresos vs. Gastos aprobados este mes')
                ->descriptionIcon($netBalance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netBalance >= 0 ? 'success' : 'danger'),

            Stat::make('Facturas por Cobrar', '$' . number_format($accountsReceivable, 2))
                ->description($overdueCount . ' facturas vencidas requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueCount > 0 ? 'warning' : 'info'),

            Stat::make('Gastos por Aprobar', '$' . number_format($pendingExpensesAmount, 2))
                ->description($pendingExpensesCount . ' solicitudes pendientes de revisión')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
