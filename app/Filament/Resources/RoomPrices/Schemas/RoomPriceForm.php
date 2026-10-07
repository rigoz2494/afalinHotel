<?php

namespace App\Filament\Resources\RoomPrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class RoomPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('room_id')
                    ->label(__('Room'))
                    ->relationship('room', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('pricing_period_id')
                    ->label(__('Month / season'))
                    ->helperText(__('Only active pricing periods are offered here. Manage the list under Pricing Periods.'))
                    ->relationship('pricingPeriod', 'name', fn ($query) => $query->active()->ordered())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('room_id', $get('room_id')),
                    )
                    ->validationMessages([
                        'unique' => __('This room already has a promotional discount for that month/season.'),
                    ]),
                TextInput::make('discount_percentage')
                    ->label(__('Promotional discount'))
                    ->helperText(__('An extra discount for this room in this month only, on top of the season modifier. Leave blank for none.'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->columnSpanFull(),
            ]);
    }
}
