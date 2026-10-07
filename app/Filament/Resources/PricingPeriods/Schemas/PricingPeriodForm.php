<?php

namespace App\Filament\Resources\PricingPeriods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PricingPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('Month / season name'))
                    ->helperText(__('Shown as a column header on the landing page, e.g. "June" or "Low season".'))
                    ->required()
                    ->maxLength(50),
                TextInput::make('modifier_percentage')
                    ->label(__('Season modifier'))
                    ->helperText(__('The markup or discount, in percent, applied to every room\'s base price for this period. For example: +20 for high season, -15 for low season, 0 to charge the base price unchanged.'))
                    ->required()
                    ->numeric()
                    ->minValue(-90)
                    ->maxValue(500)
                    ->default(0)
                    ->suffix('%'),
                TextInput::make('sort_order')
                    ->label(__('Display order'))
                    ->helperText(__('Lower numbers appear further left in the pricing table.'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label(__('Active on the landing page'))
                    ->helperText(__('Only active periods appear as pricing columns and as choices when setting a room price.'))
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}
