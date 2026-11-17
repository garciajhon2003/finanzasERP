<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::view('dashboard', 'dashboard')->name('dashboard');
// });

// Redirige la raíz directamente al panel de FinanzasPro ERP
Route::redirect('/', '/dashboard')->name('home');

// Mantiene el nombre 'dashboard' disponible por compatibilidad interna
Route::redirect('/go-dashboard', '/dashboard')->name('dashboard');

require __DIR__ . '/settings.php';
