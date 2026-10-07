<?php

namespace App\Filament\Resources\RoomPrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.name')
                    ->label(__('Room'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pricingPeriod.name')
                    ->label(__('Month / season'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pricingPeriod.sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label(__('Promotional discount'))
                    ->suffix('%')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('pricingPeriod.sort_order')
            ->filters([
                SelectFilter::make('room')
                    ->label(__('Room'))
                    ->relationship('room', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('pricingPeriod')
                    ->label(__('Month / season'))
                    ->relationship('pricingPeriod', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
