<?php

namespace App\Filament\Resources\RoomUnits\Schemas;

use App\Models\Room;
use App\Services\ImageOptimizer;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RoomUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Specific room'))
                    ->columns(2)
                    ->schema([
                        Select::make('room_id')
                            ->label(__('Room type'))
                            ->relationship('room', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Room $room): string => $room->name['en'] ?? $room->name['ru'] ?? '—')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('number')
                            ->label(__('Room number'))
                            ->helperText(__('e.g. "101" — must be unique within this room type.'))
                            ->required()
                            ->maxLength(20),
                        Toggle::make('has_balcony')
                            ->label(__('Has a balcony'))
                            ->helperText(__('This specific room — not every room of this type necessarily has one.')),
                        CheckboxList::make('amenities')
                            ->label(__('Amenities for this specific room'))
                            ->helperText(__('Leave blank to just show the room type\'s usual amenities instead.'))
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
                            ->label(__('This room\'s own photos'))
                            ->helperText(fn (Get $get): string => filled($get('images'))
                                ? __('Recommended: landscape photos at least 1200px wide, JPG or PNG. They\'re automatically compressed to WebP, so file size isn\'t a concern.')
                                : __('Left blank, the room type\'s own photos are shown for this room instead.'))
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
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'room-units', 1200))
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
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('Available for booking'))
                            ->default(true),
                    ]),
            ]);
    }
}
