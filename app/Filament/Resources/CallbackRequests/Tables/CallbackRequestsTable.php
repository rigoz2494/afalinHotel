<?php

namespace App\Filament\Resources\CallbackRequests\Tables;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CallbackRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('rooms')
                    ->label(__('Rooms'))
                    // `->getStateUsing()`, not `->formatStateUsing()`: the
                    // latter gets called once per line item for a list-cast
                    // attribute like this one, not once with the whole
                    // list — `count($state)` on a single line item (an
                    // associative array of its own fields) was quietly
                    // showing the field count instead of the room count.
                    ->getStateUsing(fn (CallbackRequest $record): string => $record->rooms === null
                        ? '—'
                        : trans_choice('{1} :count room|[2,*] :count rooms', count($record->rooms), ['count' => count($record->rooms)])),
                TextColumn::make('currency')
                    ->label(__('Currency'))
                    ->sortable(),
                IconColumn::make('wants_balcony')
                    ->label(__('Balcony'))
                    ->icon(fn (bool $state): Heroicon => $state ? Heroicon::OutlinedSparkles : Heroicon::OutlinedMinus)
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                    ->tooltip(fn (bool $state): string => $state ? __('Wants a room with a balcony') : __('No balcony preference'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (CallbackRequestStatus $state): string => match ($state) {
                        CallbackRequestStatus::New => 'info',
                        CallbackRequestStatus::Contacted => 'warning',
                        CallbackRequestStatus::Closed => 'success',
                        CallbackRequestStatus::Cancelled => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Submitted'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(CallbackRequestStatus::class),
                TernaryFilter::make('wants_balcony')
                    ->label(__('Balcony preference')),
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
