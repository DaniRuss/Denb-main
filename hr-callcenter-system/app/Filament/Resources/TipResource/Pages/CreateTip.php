<?php

namespace App\Filament\Resources\TipResource\Pages;

use App\Filament\Resources\TipResource;
use App\Services\TipWorkflowService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTip extends CreateRecord
{
    protected static string $resource = TipResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TipWorkflowService::class)->submitCallTip($data, auth()->user());
    }

    protected function getRedirectUrl(): string
    {
        return TipResource::getUrl('index');
    }
}
