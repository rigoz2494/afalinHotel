<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Models\Currency;
use App\Models\Room;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                        Tabs::make('name_and_description_locale')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make(__('English'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name.en')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(100)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                        Textarea::make('description.en')
                                            ->label(__('Description'))
                                            ->helperText(__('Keep it to 2-3 short sentences — long descriptions get cut off on the room card.'))
                                            ->placeholder(__('e.g. A bright, quiet room with a king bed and a view of the garden.'))
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                                Tab::make(__('Russian'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name.ru')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(100),
                                        Textarea::make('description.ru')
                                            ->label(__('Description'))
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('base_price')
                            ->label(__('Base price per night'))
                            ->helperText(__('The starting rate for this room. Seasonal Prices multiplies it automatically for each month — you never need to update it by hand for a season change.'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (): string => Currency::baseSymbol()),
                        TextInput::make('discount_percentage')
                            ->label(__('Fallback discount'))
                            ->helperText(__('Used only when a month in Seasonal Prices has no discount of its own. A negative number (e.g. -15) is a markup instead of a discount.'))
                            ->numeric()
                            ->minValue(-100)
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
                        CheckboxList::make('amenities')
                            ->label(__('Furniture & appliances'))
                            ->helperText(__('Shown on the public site as a row of icon badges, in whichever language the guest is browsing in.'))
                            ->options(array_combine(Room::AMENITY_TAGS, [
                                __('Double bed'), __('Twin beds'), __('Sofa'), __('Armchair'),
                                __('Table'), __('Nightstand'), __('Chairs'), __('Wardrobe'),
                                __('Coat rack'), __('TV'), __('Air conditioning'), __('Fridge'), __('Safe'),
                            ]))
                            ->columns(3)
                            ->columnSpanFull(),
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
