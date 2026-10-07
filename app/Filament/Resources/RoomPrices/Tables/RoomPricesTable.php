<?php

namespace App\Filament\Resources\RoomPrices\Tables;

use App\Models\Room;
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
                    ->formatStateUsing(fn (?array $state): string => $state['en'] ?? $state['ru'] ?? '—')
                    ->sortable(),
                TextColumn::make('price_override')
                    ->label(__('Price override'))
                    ->placeholder('—'),
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
                    // `name` is now a bilingual {en, ru} pair, not a plain
                    // string, so `relationship()`'s title-attribute shortcut
                    // can't pluck it directly — the options are built by hand.
                    ->options(fn (): array => Room::query()->ordered()->get()
                        ->mapWithKeys(fn (Room $room): array => [$room->id => $room->name['en'] ?? $room->name['ru'] ?? '—'])
                        ->all())
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
