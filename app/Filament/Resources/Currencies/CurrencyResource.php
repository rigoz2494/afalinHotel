<?php

namespace App\Filament\Resources\Currencies;

use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Filament\Resources\Currencies\Schemas\CurrencyForm;
use App\Filament\Resources\Currencies\Tables\CurrenciesTable;
use App\Models\Currency;
use App\Models\HotelSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('currency');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Currencies');
    }

    public static function getNavigationLabel(): string
    {
        return __('Currencies');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('System Settings');
    }

    /**
     * Hidden from the sidebar in a single-language "Global Language Mode"
     * — there's only ever one currency active in that mode, so a manager
     * choosing between currencies has nothing to decide. The resource,
     * its model and its routes stay fully intact; this only affects
     * whether the nav link is rendered (see the vendor method's own
     * security note: it never blocks direct URL access, which is
     * intentional here — an admin who needs it can still reach it).
     */
    public static function shouldRegisterNavigation(): bool
    {
        return HotelSetting::restrictedLocale() === null;
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
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
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
