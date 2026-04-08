<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\AwarenessStatsOverview;
use App\Filament\Widgets\LatestEngagementsWidget;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            // Performance: skip Echo/realtime wiring when not using Pusher/Laravel Echo
            ->broadcasting(false)
            // Avoids indexing every searchable resource on each request (major CPU win)
            ->globalSearch(false)
            // Client-side navigations + link prefetch: fewer full page loads
            ->spa(condition: true, hasPrefetching: true)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(\Filament\Support\Enums\Width::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                \App\Intelligence\AwarenessLogAnalytic::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AwarenessStatsOverview::class,
                LatestEngagementsWidget::class,
                \App\Intelligence\StrategyEfficiencyVisual::class,
                \App\Widgets\CaseManagementWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                function (): string {
                    $html = '
                    <link rel="manifest" href="/manifest.json">
                    <meta name="theme-color" content="#3b82f6">
                    <meta name="mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <meta name="apple-mobile-web-app-title" content="ዴንብ">';

                    // Only apply CSS overrides if we are NOT on the login page
                    if (!request()->routeIs('filament.admin.auth.login')) {
                        $html .= '
                        <link rel="stylesheet" href="/filament.css?v=' . time() . '">';
                    }

                    return $html;
                }
            );
    }
}
