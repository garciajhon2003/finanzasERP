<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\User;
use Filament\Notifications\Notification;

class InvoiceObserver
{
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status')) {
            $newStatus = match ($invoice->status) {
                'paid' => 'Pagada',
                'overdue' => 'Vencida',
                'pending' => 'Pendiente',
                default => $invoice->status,
            };

            $admins = User::role('super_admin')->get();

            Notification::make()
                ->title('Cambio de Estado en Factura')
                ->body("La factura #{$invoice->invoice_number} ahora está marcada como: {$newStatus}.")
                ->icon($invoice->status === 'paid' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                ->color($invoice->status === 'paid' ? 'success' : 'warning')
                ->sendToDatabase($admins);
        }
    }
}
