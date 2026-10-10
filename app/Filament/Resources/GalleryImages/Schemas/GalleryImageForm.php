<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use App\Models\HotelSetting;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class GalleryImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Photo'))
                    ->schema([
                        FileUpload::make('path')
                            ->label(__('Photo'))
                            ->helperText(__('Automatically compressed to WebP, so file size isn\'t a concern.'))
                            ->image()
                            ->acceptedFileTypes(ImageOptimizer::ACCEPTED_MIME_TYPES)
                            ->required()
                            ->maxSize(10240)
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'gallery', 1800))
                            ->disk('public')
                            ->imagePreviewHeight('180')
                            ->columnSpanFull(),

                        ...((($restricted = HotelSetting::restrictedLocale()) !== null)
                            ? [
                                TextInput::make("alt.{$restricted}")
                                    ->label(__('Caption'))
                                    ->helperText(__('Shown as the photo\'s caption in the lightbox. Optional.'))
                                    ->maxLength(150)
                                    ->columnSpanFull(),
                                Hidden::make('alt.'.($restricted === 'en' ? 'ru' : 'en')),
                            ]
                            : [
                                Tabs::make('alt_locale')
                                    ->label(__('Caption'))
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->schema([
                                                TextInput::make('alt.en')
                                                    ->label(__('Caption'))
                                                    ->helperText(__('Shown as the photo\'s caption in the lightbox. Optional.'))
                                                    ->maxLength(150),
                                            ]),
                                        Tab::make(__('Russian'))
                                            ->schema([
                                                TextInput::make('alt.ru')
                                                    ->label(__('Caption'))
                                                    ->maxLength(150),
                                            ]),
                                    ]),
                            ]
                        ),
                    ]),

                Section::make(__('Visibility'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('Display order'))
                            ->helperText(__('Or just drag rows into place from the list.'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('Shown on the public site'))
                            ->default(true),
                    ]),
            ]);
    }
}
