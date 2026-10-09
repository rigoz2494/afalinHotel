<?php

namespace App\Filament\Resources\RoomUnits;

use App\Filament\Resources\RoomUnits\Pages\CreateRoomUnit;
use App\Filament\Resources\RoomUnits\Pages\EditRoomUnit;
use App\Filament\Resources\RoomUnits\Pages\ListRoomUnits;
use App\Filament\Resources\RoomUnits\Schemas\RoomUnitForm;
use App\Filament\Resources\RoomUnits\Tables\RoomUnitsTable;
use App\Models\RoomUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoomUnitResource extends Resource
{
    protected static ?string $model = RoomUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('Specific Room');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Specific Rooms');
    }

    public static function getNavigationLabel(): string
    {
        return __('Specific Rooms');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Landing page');
    }

    public static function form(Schema $schema): Schema
    {
        return RoomUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomUnits::route('/'),
            'create' => CreateRoomUnit::route('/create'),
            'edit' => EditRoomUnit::route('/{record}/edit'),
        ];
    }
}
