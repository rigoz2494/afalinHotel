<?php

namespace App\Filament\Resources\CallbackRequests\Tables;

use App\Enums\CallbackRequestStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->formatStateUsing(fn (?array $state): string => $state === null
                        ? '—'
                        : trans_choice('{1} :count room|[2,*] :count rooms', count($state), ['count' => count($state)])),
                TextColumn::make('currency')
                    ->label(__('Currency'))
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
