<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->label(__('Question'))
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->label(__('Answer'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Section::make(__('Russian translation'))
                    ->description(__('Shown to guests browsing the site in Russian. Type a question and answer in either language above, then click "Auto-Translate" to fill in the other — it detects which language you wrote, so it works either way. Review before saving. Left blank, the English text is shown instead.'))
                    ->schema([
                        TextInput::make('question_ru')
                            ->label(__('Question (Russian)'))
                            ->maxLength(200)
                            ->columnSpanFull(),
                        Textarea::make('answer_ru')
                            ->label(__('Answer (Russian)'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                TextInput::make('sort_order')
                    ->label(__('Display order'))
                    ->helperText(__('Lower numbers appear first in the FAQ accordion.'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label(__('Visible on the landing page'))
                    ->default(true),
            ]);
    }
}
