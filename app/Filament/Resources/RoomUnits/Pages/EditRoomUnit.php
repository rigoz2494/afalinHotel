<?php

namespace App\Filament\Resources\RoomUnits\Pages;

use App\Filament\Resources\RoomUnits\RoomUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoomUnit extends EditRecord
{
    protected static string $resource = RoomUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
