<?php

namespace App\Filament\Resources\CallbackRequests\Schemas;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\Currency;
use Filament\Forms\Components\Placeholder;
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
                        Toggle::make('wants_balcony')
                            ->label(__('Wants a room with a balcony'))
                            ->disabled(),
                        TextInput::make('room_number')
                            ->label(__('Room number browsed'))
                            ->helperText(__('From the Section 2 "View Available Rooms" modal — a preference, not a confirmed assignment.'))
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->label(__('IP address'))
                            ->disabled(),
                    ]),

                // One cohesive block for both free-text fields a guest can
                // write in any language: the original right beside its
                // automatic Russian translation, so a Russian-speaking
                // manager never has to go hunting for the translated
                // version in a separate place on the page.
                Section::make(__('Translations'))
                    ->description(__('Each guest field alongside its automatic Russian translation.'))
                    ->columns(2)
                    ->schema([
                        Textarea::make('message')
                            ->label(__('Guest message'))
                            ->disabled(),
                        Placeholder::make('message_translated')
                            ->label(__('Translated message (Automatically):'))
                            ->content(fn (?CallbackRequest $record): ?string => $record?->message_translated)
                            ->visible(fn (?CallbackRequest $record): bool => filled($record?->message_translated)),
                        Textarea::make('special_requests')
                            ->label(__('Special requests for check-in'))
                            ->disabled(),
                        Placeholder::make('special_requests_translated')
                            ->label(__('Translated request (Automatically):'))
                            ->content(fn (?CallbackRequest $record): ?string => $record?->special_requests_translated)
                            ->visible(fn (?CallbackRequest $record): bool => filled($record?->special_requests_translated)),
                    ]),

                Section::make(__('Currency'))
                    ->columns(2)
                    ->schema([
                        // A plain-text marker of which currency the guest had
                        // selected on the frontend, unmistakable at a glance —
                        // `currency` below is already that value; this is just
                        // how it's shown.
                        Placeholder::make('currency_marker')
                            ->label(__('Currency'))
                            ->content(fn (?CallbackRequest $record): ?string => $record?->currency
                                ? __('Customer Currency: :code', ['code' => $record->currency])
                                : null),
                        TextInput::make('exchange_rate')
                            ->label(__('Exchange rate'))
                            ->helperText(__('Locked when the guest submitted the request.'))
                            ->disabled(),
                        Placeholder::make('total_price')
                            ->label(__('Total (server-calculated)'))
                            ->content(fn (?CallbackRequest $record): string => Currency::symbolFor($record?->currency).number_format((float) $record?->total_price, 2))
                            ->columnSpanFull(),
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

                Section::make(__('Selected services'))
                    ->visible(fn (?CallbackRequest $record): bool => filled($record?->services))
                    ->schema([
                        Repeater::make('services')
                            ->label(null)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Service'))
                                    ->disabled(),
                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->prefix(fn (?CallbackRequest $record): string => Currency::symbolFor($record?->currency))
                                    ->disabled(),
                                TextInput::make('quantity')
                                    ->label(__('Qty'))
                                    ->disabled(),
                            ])
                            ->columns(3)
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
