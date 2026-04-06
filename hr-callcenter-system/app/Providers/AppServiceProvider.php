<?php

namespace App\Providers;

use App\Models\ShiftSwap;
use App\Observers\ShiftSwapObserver;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Patched grid: empty cells use weekday of Ethiopian day 1 (30-day months). Overrides package asset after composer updates.
        FilamentAsset::register([
            AlpineComponent::make('filament-ethiopic-calendar', resource_path('js/filament-ethiopic-calendar.js')),
        ], 'agelgil/filament-ethiopic-calendar');

        View::prependNamespace(
            'filament-ethiopic-calendar',
            resource_path('views/vendor/filament-ethiopic-calendar')
        );

        ShiftSwap::observe(ShiftSwapObserver::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): HtmlString => new HtmlString('
                <style>
                    /* Expand the outermost containers */
                    .fi-main-ctn, .fi-page, .fi-main, .fi-sc-form {
                        max-width: none !important;
                        width: 100% !important;
                    }
                    /* Force any grid inside the form to be 1-column or elements to span full */
                    .fi-sc-form .fi-grid {
                        grid-template-columns: 1fr !important;
                    }
                    .fi-sc-form .fi-grid > * {
                        grid-column: span 1 / span 1 !important;
                    }
                    /* Ensure tabs and other large components use all space */
                    .fi-tabs {
                        width: 100% !important;
                    }
                    .fi-main-sidebar {
                        min-height: 0;
                    }
                    .fi-main-sidebar .fi-sidebar-nav {
                        min-height: 0;
                    }
                </style>
            '),
        );

        // Scroll sidebar so the active (clicked) item stays in view. Filament bundle uses scrollTo math that often snaps to top.
        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): HtmlString => new HtmlString(<<<'HTML'
<script>
(function () {
    function scrollActiveIntoView() {
        var nav = document.querySelector('.fi-main-sidebar .fi-sidebar-nav');
        if (!nav) return;
        var el = document.querySelector('.fi-main-sidebar .fi-sidebar-item.fi-active');
        if (!el || el.offsetParent === null) {
            el = document.querySelector('.fi-main-sidebar .fi-sidebar-group.fi-active');
        }
        if (!el || el.offsetParent === null) return;
        el.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }

    function run() {
        setTimeout(scrollActiveIntoView, 60);
    }

    document.addEventListener('DOMContentLoaded', run);
    document.addEventListener('livewire:navigated', run);
})();
</script>
HTML
            ),
        );
    }
}
