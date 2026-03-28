<?php

namespace App\Filament\Resources\AwarenessEngagementResource\Pages;

use App\Filament\Resources\AwarenessEngagementResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class CreateAwarenessEngagement extends CreateRecord
{
    protected static string $resource = AwarenessEngagementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Engagement logged as draft.')
            ->body('You can review your entry before submitting it for approval.')
            ->success();
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
