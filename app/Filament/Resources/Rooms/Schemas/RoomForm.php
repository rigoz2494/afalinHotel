<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Models\Currency;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Room details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->helperText(__('Keep it to 2-3 short sentences — long descriptions get cut off on the room card.'))
                            ->placeholder(__('e.g. A bright, quiet room with a king bed and a view of the garden.'))
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('base_price')
                            ->label(__('Base price per night'))
                            ->helperText(__('The starting rate for this room. Seasonal Prices multiplies it automatically for each month — you never need to update it by hand for a season change.'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (): string => Currency::baseSymbol()),
                        TextInput::make('discount_percentage')
                            ->label(__('Fallback discount'))
                            ->helperText(__('Used only when a month in Seasonal Prices has no discount of its own.'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ]),

                Section::make(__('Amenities'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('capacity')
                            ->label(__('Capacity (guests)'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(2),
                        TextInput::make('bed_type')
                            ->label(__('Bed type'))
                            ->required()
                            ->maxLength(50),
                        TagsInput::make('furniture')
                            ->label(__('Furniture & amenities'))
                            ->helperText(__('Press enter after each item, e.g. "Armchair", "Wardrobe".'))
                            ->columnSpanFull(),
                        TagsInput::make('furniture_ru')
                            ->label(__('Furniture & amenities (Russian)'))
                            ->helperText(__('Shown to guests browsing the site in Russian. Left blank, the English tags above are shown instead.'))
                            ->columnSpanFull(),
                        Toggle::make('has_tv')
                            ->label(__('Has TV')),
                        Toggle::make('has_air_conditioning')
                            ->label(__('Has air conditioning')),
                    ]),

                Section::make(__('Photos'))
                    ->schema([
                        FileUpload::make('images')
                            ->label(__('Room photos'))
                            // An empty state here is normal for a new or not-yet-photographed
                            // room, not a bug: it just means no real photo has been uploaded.
                            // The public site shows a generic placeholder until then, which
                            // this field can't preview, since it isn't a real managed file.
                            ->helperText(fn (Get $get): string => filled($get('images'))
                                ? __('Recommended: landscape photos at least 1200px wide, JPG or PNG. They\'re automatically compressed to WebP, so file size isn\'t a concern.')
                                : __('No photos uploaded yet — the public site shows a generic placeholder for this room until you add real ones here. Recommended: landscape photos at least 1200px wide, JPG or PNG — they\'re automatically compressed to WebP.'))
                            ->image()
                            ->acceptedFileTypes(ImageOptimizer::ACCEPTED_MIME_TYPES)
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->maxSize(10240)
                            ->previewable()
                            ->deletable()
                            ->openable()
                            ->downloadable()
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'rooms', 1200))
                            ->disk('public')
                            ->imagePreviewHeight('120')
                            ->panelLayout('grid'),
                    ]),

                Section::make(__('Visibility'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('Display order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('Lower numbers appear first on the landing page.')),
                        Toggle::make('is_active')
                            ->label(__('Visible on the landing page'))
                            ->default(true),
                    ]),
            ]);
    }
}
