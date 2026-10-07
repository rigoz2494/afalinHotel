<?php

namespace App\Filament\Resources\Currencies\Tables;

use App\Models\Currency;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('symbol')
                    ->label(__('Symbol')),
                TextColumn::make('symbol_position')
                    ->label(__('Position'))
                    ->badge(),
                TextColumn::make('exchange_rate')
                    ->label(__('Exchange rate'))
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                IconColumn::make('is_base')
                    ->label(__('Base'))
                    ->boolean(),
                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->disabled(fn (Currency $record): bool => $record->is_base),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Currency $record): bool => $record->is_base),
            ]);
    }
}
