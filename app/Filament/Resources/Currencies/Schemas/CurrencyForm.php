<?php

namespace App\Filament\Resources\Currencies\Schemas;

use App\Enums\CurrencySymbolPosition;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('code')
                    ->label(__('Currency code'))
                    ->helperText(__('ISO 4217 code, e.g. USD, EUR, RUB.'))
                    ->required()
                    ->length(3)
                    ->alpha()
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): ?string => strtoupper((string) $state)),
                TextInput::make('symbol')
                    ->label(__('Symbol'))
                    ->helperText(__('Shown next to every price, e.g. $, €, ₽.'))
                    ->required()
                    ->maxLength(8),
                Select::make('symbol_position')
                    ->label(__('Symbol position'))
                    ->helperText(__('Most Western currencies go before the number; many others go after.'))
                    ->options(CurrencySymbolPosition::class)
                    ->default(CurrencySymbolPosition::Before)
                    ->required(),
                TextInput::make('exchange_rate')
                    ->label(__('Exchange rate'))
                    ->helperText(__('How many units of this currency equal 1 unit of the base currency.'))
                    ->required(fn (Get $get): bool => ! $get('is_base'))
                    ->numeric()
                    ->minValue(0.0001)
                    ->step(0.0001)
                    ->disabled(fn (Get $get): bool => (bool) $get('is_base')),
                TextInput::make('sort_order')
                    ->label(__('Display order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_base')
                    ->label(__('Base currency'))
                    ->helperText(__('Prices are stored in the base currency. Its rate is always 1.'))
                    ->live()
                    ->disabled(fn (?Currency $record): bool => (bool) $record?->is_base),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->helperText(__('Inactive currencies are hidden from guests.'))
                    ->default(true)
                    ->disabled(fn (Get $get): bool => (bool) $get('is_base')),
            ]);
    }
}
