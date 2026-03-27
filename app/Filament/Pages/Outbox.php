<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Outbox — shows IndexedDB drafts pending sync.
 *
 * This page is entirely client-side rendered (Alpine.js + our offline JS modules).
 * The PHP class only handles routing, navigation visibility, and authorization.
 */
class Outbox extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Outbox | ሳጥን';
    protected static string|\UnitEnum|null $navigationGroup = 'Awareness Management';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.outbox';

    /** Only visible to paramilitary officers */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('paramilitary');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Outbox — Pending Sync | የሚጠበቁ መዝገቦች';
    }

    /** Navigation badge showing pending count (updated by Alpine) */
    public static function getNavigationBadge(): ?string
    {
        // The actual count is stored in IndexedDB — we return null here
        // and let the client-side offline-ui.js inject the badge via DOM.
        return null;
    }
}
