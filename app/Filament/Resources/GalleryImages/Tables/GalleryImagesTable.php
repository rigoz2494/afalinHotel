<?php

namespace App\Filament\Resources\GalleryImages\Tables;

use App\Models\GalleryImage;
use App\Models\HotelSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('alt')
                    ->label(__('Caption'))
                    // `->getStateUsing()`, not `->formatStateUsing()` — see
                    // the identical comment in RoomsTable.php.
                    ->getStateUsing(fn (GalleryImage $record): string => HotelSetting::adminLabel($record->alt)),
                IconColumn::make('tall')
                    ->label(__('Portrait'))
                    ->boolean(),
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
            ->reorderable('sort_order')
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
