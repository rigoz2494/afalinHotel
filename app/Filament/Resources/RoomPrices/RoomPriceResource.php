<?php

namespace App\Filament\Resources\RoomPrices;

use App\Filament\Resources\RoomPrices\Pages\CreateRoomPrice;
use App\Filament\Resources\RoomPrices\Pages\EditRoomPrice;
use App\Filament\Resources\RoomPrices\Pages\ListRoomPrices;
use App\Filament\Resources\RoomPrices\Schemas\RoomPriceForm;
use App\Filament\Resources\RoomPrices\Tables\RoomPricesTable;
use App\Models\RoomPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoomPriceResource extends Resource
{
    protected static ?string $model = RoomPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('promotional discount');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Promotional discounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('Promo Discounts');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Landing page');
    }

    public static function form(Schema $schema): Schema
    {
        return RoomPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomPricesTable::configure($table);
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
            'index' => ListRoomPrices::route('/'),
            'create' => CreateRoomPrice::route('/create'),
            'edit' => EditRoomPrice::route('/{record}/edit'),
        ];
    }
}
