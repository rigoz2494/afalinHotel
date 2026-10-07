<?php

namespace App\Filament\Resources\Rooms\Tables;

use App\Models\Currency;
use App\Models\Room;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RoomsTable
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
                TextColumn::make('name')
                    ->label(__('Name'))
                    // `->getStateUsing()`, not `->formatStateUsing()`: the
                    // latter only ever receives the *whole* state, but
                    // Filament auto-detects an array-cast attribute like
                    // this {en, ru} pair as a "list" state and calls the
                    // formatter once per element — i.e. once with the
                    // string "Standard Double Room", then again with
                    // "Стандарт...", never with the array itself. This
                    // computes the display string directly instead.
                    ->getStateUsing(fn (Room $record): string => $record->name['en'] ?? $record->name['ru'] ?? '—')
                    ->sortable(),
                TextColumn::make('bed_type')
                    ->label(__('Bed'))
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label(__('Guests'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_price')
                    ->label(__('Base price'))
                    ->prefix(fn (): string => Currency::baseSymbol())
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label(__('Discount'))
                    ->suffix('%')
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
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
