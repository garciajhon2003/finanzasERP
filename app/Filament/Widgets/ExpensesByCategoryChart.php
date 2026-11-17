<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class ExpensesByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Gastos por Categoría';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '275px';


    protected function getData(): array
    {
        $categories = Category::where('type', 'expense')
            ->withSum(['expenses' => fn($query) => $query->where('status', 'approved')], 'amount')
            ->get()
            ->filter(fn($category) => $category->expenses_sum_amount > 0)
            ->values();

        // Paleta vibrante de respaldo por si alguna categoría tiene el color vacío (null)
        $fallbackColors = [
            '#2563eb', // Azul
            '#10b981', // Esmeralda
            '#f59e0b', // Ámbar
            '#8b5cf6', // Violeta
            '#ef4444', // Rojo
            '#06b6d4', // Cian
            '#ec4899', // Rosa
        ];

        $backgroundColors = $categories->map(function ($cat, $index) use ($fallbackColors) {
            if (! empty($cat->color) && str_starts_with($cat->color, '#')) {
                return $cat->color;
            }

            return $fallbackColors[$index % count($fallbackColors)];
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Gastado ($)',
                    'data' => $categories->pluck('expenses_sum_amount')->toArray(),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'layout' => [
                'padding' => [
                    'left' => 20,
                    'right' => 20,
                    'top' => 10,
                    'bottom' => 10,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'left', // Mueve las categorías al lado izquierdo
                    'align' => 'center',  // Las centra verticalmente respecto al gráfico
                    'labels' => [
                        'padding' => 20,  // Separación vertical entre cada categoría
                        'boxWidth' => 15, // Tamaño del cuadrito de color
                        'font' => [
                            'size' => 13,
                        ],
                    ],
                ],
            ],
        ];
    }
}
