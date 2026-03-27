<?php

namespace App\Filament\Resources\AwarenessEngagementResource\Pages;

use App\Filament\Resources\AwarenessEngagementResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAwarenessEngagement extends CreateRecord
{
    protected static string $resource = AwarenessEngagementResource::class;

    /**
     * Force-inject the authenticated user's location data and submission status
     * before saving, ensuring newly created engagements are always matched to
     * the paramilitary user's assigned Woreda — and immediately visible to the
     * Woreda Coordinator without a separate "Submit" click.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Always stamp the creator
        $data['created_by'] = $user->id;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Engagement logged and submitted for approval.')
            ->body('Your Woreda Coordinator will review your submission.')
            ->success();
    }
}
