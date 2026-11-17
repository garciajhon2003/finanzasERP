<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Widgets\CashFlowChart;
use App\Filament\Widgets\ExpensesByCategoryChart;
use App\Filament\Widgets\FinancialStatsOverview;
use Filament\View\PanelsRenderHook;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Vite;


class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('dashboard')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                FinancialStatsOverview::class,
                CashFlowChart::class,
                ExpensesByCategoryChart::class,

            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('FinanzasPro ERP')
            ->font('Plus Jakarta Sans') // Fuente moderna de Google Fonts integrada en Filament
            ->colors([
                'primary' => Color::Blue,     // Azul corporativo para botones, enlaces y activos
                'gray' => Color::Slate,       // Gris azulado elegante para fondos y tarjetas
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->sidebarCollapsibleOnDesktop()   // Permite colapsar el menú lateral para ganar espacio
            ->spa()
            ->assets([
                Css::make('custom-theme', Vite::asset('resources/css/filament/admin/theme.css')),
            ])
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn() => request()->routeIs('filament.admin.pages.dashboard') ? view('filament.custom-header') : ''
            )
        ;
    }
}
