<?php

namespace App\Filament\Resources\PricingPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PricingPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Month / season'))
                    ->searchable(),
                TextColumn::make('modifier_percentage')
                    ->label(__('Season modifier'))
                    ->suffix('%')
                    ->color(fn (float $state): string => match (true) {
                        $state > 0 => 'warning',
                        $state < 0 => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
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
