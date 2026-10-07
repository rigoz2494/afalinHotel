<?php

namespace App\Filament\Resources\RoomPrices\Pages;

use App\Filament\Resources\RoomPrices\RoomPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoomPrices extends ListRecords
{
    protected static string $resource = RoomPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
