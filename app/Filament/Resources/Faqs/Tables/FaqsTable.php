<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use App\Models\HotelSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label(__('Question'))
                    // In Strict Russian mode this listing is the only place
                    // a non-English-reading admin sees FAQs at a glance, so
                    // it shows the Russian translation when there is one —
                    // the Edit form already does; this table just didn't.
                    // Multilingual and Strict English modes are unchanged:
                    // the baseline English question, with the "Russian"
                    // column beside it showing translation status.
                    ->getStateUsing(fn (Faq $record): string => HotelSetting::restrictedLocale() === 'ru'
                        ? ($record->question_ru ?? $record->question)
                        : $record->question)
                    ->limit(60)
                    ->searchable(),
                IconColumn::make('question_ru')
                    ->label(__('Russian'))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedMinusCircle)
                    ->tooltip(fn (Faq $record): string => $record->question_ru
                        ? __('Translated to Russian')
                        : __('Not yet translated')),
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
