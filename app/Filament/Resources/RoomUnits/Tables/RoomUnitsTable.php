<?php

namespace App\Filament\Resources\RoomUnits\Tables;

use App\Models\Room;
use App\Models\RoomUnit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RoomUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('')
                    ->disk('public')
                    ->limit(1)
                    ->circular(),
                TextColumn::make('room.name')
                    ->label(__('Room type'))
                    // See the identical comment in RoomsTable.php:
                    // formatStateUsing() is called once per element for an
                    // array-cast attribute, not once with the {en, ru} pair.
                    ->getStateUsing(fn (RoomUnit $record): string => $record->room->name['en'] ?? $record->room->name['ru'] ?? '—')
                    ->sortable(),
                TextColumn::make('number')
                    ->label(__('Number'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('has_balcony')
                    ->label(__('Balcony'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('Available'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('room')
                    ->label(__('Room type'))
                    ->options(fn (): array => Room::query()->ordered()->get()
                        ->mapWithKeys(fn (Room $room): array => [$room->id => $room->name['en'] ?? $room->name['ru'] ?? '—'])
                        ->all())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_balcony')
                    ->label(__('Balcony')),
                TernaryFilter::make('is_active')
                    ->label(__('Available')),
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
