<?php

namespace App\Filament\Pages;

use App\Models\HotelSetting;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class ManageHotelSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.manage-hotel-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Site Settings');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Landing page');
    }

    public function mount(): void
    {
        $settings = HotelSetting::query()->pluck('value', 'key');

        $this->form->fill([
            'hotel_name' => $settings->get('hotel_name'),
            'tagline' => $settings->get('tagline'),
            'promo_banner' => $settings->get('promo_banner'),
            'hero_images' => $settings->get('hero_images', []),
            'contacts' => $settings->get('contacts', []),
            'section_headings' => HotelSetting::defaultedSectionHeadings($settings->get('section_headings', [])),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('Homepage hero'))
                    ->description(__('Controls the title, tagline, promo banner and background slideshow on the public homepage.'))
                    ->schema([
                        TextInput::make('hotel_name')
                            ->label(__('Hotel name'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('tagline')
                            ->label(__('Tagline'))
                            ->maxLength(150),
                        Textarea::make('promo_banner')
                            ->label(__('Promo banner'))
                            ->helperText(__('Shown as a pill above the "View rooms" button. Leave blank to hide it.'))
                            ->rows(2),
                        FileUpload::make('hero_images')
                            ->label(__('Hero slideshow photos'))
                            ->helperText(__('These fade into one another every 5 seconds behind the hero title. Uploads are resized to 1920px wide and converted to WebP.'))
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
                            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'hero-slideshow', 1920))
                            ->disk('public')
                            ->imagePreviewHeight('120')
                            ->panelLayout('grid'),
                    ]),

                Section::make(__('Contact details'))
                    ->description(__('Shown in the Contact section and used for the WhatsApp quick-connect link. Use the field name "phone" for the phone number — it\'s automatically turned into a tap-to-call link for mobile guests.'))
                    ->schema([
                        KeyValue::make('contacts')
                            ->label(null)
                            ->keyLabel(__('Field'))
                            ->valueLabel(__('Value'))
                            ->addActionLabel(__('Add contact field'))
                            ->reorderable(),
                    ]),

                Section::make(__('Section headings'))
                    ->description(__('The main title shown at the top of each landing-page section, and in the navigation menu, in each language.'))
                    ->schema([
                        Tabs::make('section_headings_locale')
                            ->tabs([
                                Tab::make(__('English'))
                                    ->columns(2)
                                    ->schema(self::sectionHeadingFields('en')),
                                Tab::make(__('Russian'))
                                    ->columns(2)
                                    ->schema(self::sectionHeadingFields('ru')),
                            ]),
                    ]),
            ]);
    }

    /**
     * One TextInput per section, for a single locale tab. `section_headings.rooms.en`
     * dot-notation naturally builds the nested {section: {locale: value}} structure.
     *
     * @return array<int, TextInput>
     */
    private static function sectionHeadingFields(string $locale): array
    {
        $sections = [
            'rooms' => __('Rooms'),
            'pricing' => __('Pricing'),
            'about' => __('About Us'),
            'faq' => __('FAQ'),
            'contact' => __('Contact'),
        ];

        return array_map(
            fn (string $section, string $label): TextInput => TextInput::make("section_headings.{$section}.{$locale}")
                ->label($label)
                ->maxLength(100),
            array_keys($sections),
            array_values($sections),
        );
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            HotelSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}
