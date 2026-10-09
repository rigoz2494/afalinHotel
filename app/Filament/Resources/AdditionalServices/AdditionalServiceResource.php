<?php

namespace App\Filament\Resources\AdditionalServices;

use App\Filament\Resources\AdditionalServices\Pages\CreateAdditionalService;
use App\Filament\Resources\AdditionalServices\Pages\EditAdditionalService;
use App\Filament\Resources\AdditionalServices\Pages\ListAdditionalServices;
use App\Filament\Resources\AdditionalServices\Schemas\AdditionalServiceForm;
use App\Filament\Resources\AdditionalServices\Tables\AdditionalServicesTable;
use App\Models\AdditionalService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdditionalServiceResource extends Resource
{
    protected static ?string $model = AdditionalService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return __('Additional Service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Additional Services');
    }

    public static function getNavigationLabel(): string
    {
        return __('Additional Services');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Landing page');
    }

    public static function form(Schema $schema): Schema
    {
        return AdditionalServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditionalServicesTable::configure($table);
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
            'index' => ListAdditionalServices::route('/'),
            'create' => CreateAdditionalService::route('/create'),
            'edit' => EditAdditionalService::route('/{record}/edit'),
        ];
    }
}
