<?php

namespace App\Filament\Resources\RoomPrices\Schemas;

use App\Models\Room;
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
                    // `name` is now a bilingual {en, ru} pair, not a plain
                    // string, so `relationship()`'s title-attribute shortcut
                    // can't pluck it directly — the label is built by hand.
                    ->relationship('room', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Room $room): string => $room->name['en'] ?? $room->name['ru'] ?? '—')
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
                    ->suffix('%'),
                TextInput::make('price_override')
                    ->label(__('Price override'))
                    ->helperText(__('A literal price for this room in this month, replacing the formula outright — e.g. when the real rate doesn\'t follow the season modifier closely enough, or for a non-numeric display like "900/1300". Leave blank to use the formula.'))
                    ->maxLength(50)
                    ->columnSpanFull(),
            ]);
    }
}
