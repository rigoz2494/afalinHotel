<?php

namespace App\Filament\Resources\AdditionalServices\Schemas;

use App\Models\Currency;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AdditionalServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Service'))
                    ->columns(2)
                    ->schema([
                        Tabs::make('name_locale')
                            ->label(__('Name'))
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make(__('English'))
                                    ->schema([
                                        TextInput::make('name.en')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(100),
                                    ]),
                                Tab::make(__('Russian'))
                                    ->schema([
                                        TextInput::make('name.ru')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(100),
                                    ]),
                            ]),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->helperText(__('A flat price — not seasonal, unlike the rooms in the pricing matrix.'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (): string => Currency::baseSymbol()),
                        TextInput::make('sort_order')
                            ->label(__('Display order'))
                            ->numeric()
                            ->default(0),
                    ]),

                Toggle::make('is_active')
                    ->label(__('Shown on the public site'))
                    ->default(true),
            ]);
    }
}
