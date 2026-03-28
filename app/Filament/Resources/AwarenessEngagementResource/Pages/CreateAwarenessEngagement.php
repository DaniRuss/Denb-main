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
            ->title('Engagement logged and submitted for approval.')
            ->body('Your Woreda Coordinator will review your submission.')
            ->success();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OfflineCreateWidget::class,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
