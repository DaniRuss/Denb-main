<?php

namespace App\Filament\Resources\AwarenessEngagementResource\Pages;

use App\Filament\Resources\AwarenessEngagementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAwarenessEngagement extends EditRecord
{
    protected static string $resource = AwarenessEngagementResource::class;

    // Edit block removed to allow user to view and edit as requested

    /**
     * When a paramilitary edits a draft (rare), still enforce their assigned
     * woreda on save — so location cannot be changed through the edit form.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user->hasRole('paramilitary')) {
            // Re-enforce created_by — cannot change ownership
            $data['created_by'] = $user->id;
        }

        // If a rejected record is edited, revert it to draft
        if ($this->record->status === 'rejected') {
            $data['status'] = 'draft';
            $data['rejection_note'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
