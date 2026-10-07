<?php

namespace App\Filament\Resources\CallbackRequests\Pages;

use App\Filament\Resources\CallbackRequests\CallbackRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCallbackRequest extends EditRecord
{
    protected static string $resource = CallbackRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
