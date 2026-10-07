<?php

namespace App\Filament\Resources\PricingPeriods;

use App\Filament\Resources\PricingPeriods\Pages\CreatePricingPeriod;
use App\Filament\Resources\PricingPeriods\Pages\EditPricingPeriod;
use App\Filament\Resources\PricingPeriods\Pages\ListPricingPeriods;
use App\Filament\Resources\PricingPeriods\Schemas\PricingPeriodForm;
use App\Filament\Resources\PricingPeriods\Tables\PricingPeriodsTable;
use App\Models\PricingPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PricingPeriodResource extends Resource
{
    protected static ?string $model = PricingPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('pricing period');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pricing Periods');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pricing Periods');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Landing page');
    }

    public static function form(Schema $schema): Schema
    {
        return PricingPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingPeriodsTable::configure($table);
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
            'index' => ListPricingPeriods::route('/'),
            'create' => CreatePricingPeriod::route('/create'),
            'edit' => EditPricingPeriod::route('/{record}/edit'),
        ];
    }
}
