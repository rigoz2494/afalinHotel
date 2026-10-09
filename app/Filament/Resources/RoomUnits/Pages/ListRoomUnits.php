<?php

namespace App\Filament\Resources\RoomUnits\Pages;

use App\Filament\Resources\RoomUnits\RoomUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoomUnits extends ListRecords
{
    protected static string $resource = RoomUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
