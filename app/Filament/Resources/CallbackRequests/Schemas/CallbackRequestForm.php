<?php

namespace App\Filament\Resources\CallbackRequests\Schemas;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\Currency;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CallbackRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Guest'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->disabled(),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->disabled(),
                        Textarea::make('message')
                            ->label(__('Guest message'))
                            ->disabled()
                            ->columnSpanFull(),
                        Toggle::make('wants_balcony')
                            ->label(__('Wants a room with a balcony'))
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->label(__('IP address'))
                            ->disabled(),
                    ]),

                Section::make(__('Currency'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('currency')
                            ->label(__('Currency'))
                            ->disabled(),
                        TextInput::make('exchange_rate')
                            ->label(__('Exchange rate'))
                            ->helperText(__('Locked when the guest submitted the request.'))
                            ->disabled(),
                    ]),

                Section::make(__('Selected rooms'))
                    ->schema([
                        Repeater::make('rooms')
                            ->label(null)
                            ->schema([
                                TextInput::make('room_name')
                                    ->label(__('Room'))
                                    ->disabled(),
                                TextInput::make('period')
                                    ->label(__('Month'))
                                    ->disabled(),
                                TextInput::make('price')
                                    ->label(__('Price'))
                                    // A booking's prices are in the currency it was made in.
                                    ->prefix(fn (?CallbackRequest $record): string => Currency::symbolFor($record?->currency))
                                    ->disabled(),
                                TextInput::make('quantity')
                                    ->label(__('Qty'))
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Follow-up'))
                    ->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(CallbackRequestStatus::class)
                            ->default(CallbackRequestStatus::New)
                            ->required(),
                    ]),
            ]);
    }
}
