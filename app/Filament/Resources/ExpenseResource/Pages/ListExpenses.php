<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Imports\ExpenseImporter;
use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(ExpenseImporter::class)
                ->label('Importar Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success'),

            Actions\CreateAction::make(),
        ];
    }
}
