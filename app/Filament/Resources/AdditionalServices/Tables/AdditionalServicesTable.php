<?php

namespace App\Filament\Resources\AdditionalServices\Tables;

use App\Models\AdditionalService;
use App\Models\Currency;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AdditionalServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    // See the identical comment in RoomsTable.php.
                    ->getStateUsing(fn (AdditionalService $record): string => $record->name['en'] ?? $record->name['ru'] ?? '—')
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->prefix(fn (): string => Currency::baseSymbol())
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Shown'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Shown')),
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
