<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Models\Expense;
use App\Models\Invoice;

// Ruta pública para obtener el token
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
});

// Dentro del grupo auth:sanctum en routes/api.php:
Route::get('/dashboard-stats', function () {
    $income = Invoice::where('status', 'paid')->sum('total');
    $expenses = Expense::where('status', 'approved')->sum('amount');
    $receivable = Invoice::whereIn('status', ['pending', 'overdue'])->sum('total');
    $pendingExpenses = Expense::where('status', 'pending')->sum('amount');

    return response()->json([
        'net_balance' => (float) ($income - $expenses),
        'accounts_receivable' => (float) $receivable,
        'pending_expenses' => (float) $pendingExpenses,
    ]);
});

Route::get('/expenses', function () {
    return response()->json([
        'data' => Expense::with(['category', 'supplier'])->latest('expense_date')->limit(15)->get(),
    ]);
});
