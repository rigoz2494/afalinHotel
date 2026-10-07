<?php

namespace App\Filament\Resources\RoomPrices\Pages;

use App\Filament\Resources\RoomPrices\RoomPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoomPrice extends EditRecord
{
    protected static string $resource = RoomPriceResource::class;

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
