<?php

namespace App\Filament\Pages;

use App\Models\HotelSetting;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
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

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manage-hotel-settings';

    /**
     * Every channel starts blank and switched off — see WebhookDispatchService,
     * which only ever fires at a channel that is both active and has a URL.
     *
     * @var array<string, array<string, mixed>>
     */
    private const array DEFAULT_WEBHOOKS = [
        'telegram' => ['url' => null, 'channel_id' => null, 'active' => false],
        'whatsapp' => ['url' => null, 'active' => false],
        'custom' => ['url' => null, 'auth_header' => null, 'active' => false],
    ];

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
        return __('System Settings');
    }

    public function mount(): void
    {
        $settings = HotelSetting::query()->pluck('value', 'key');

        $this->form->fill([
            'hotel_name' => HotelSetting::defaultedHotelName($settings->get('hotel_name')),
            'tagline' => HotelSetting::normalizedTagline($settings->get('tagline')) ?? ['en' => null, 'ru' => null],
            'promo_banner' => HotelSetting::normalizedPromoBanner($settings->get('promo_banner')) ?? ['en' => null, 'ru' => null],
            'hero_images' => $settings->get('hero_images', []),
            'contacts' => $settings->get('contacts', []),
            'section_headings' => HotelSetting::defaultedSectionHeadings($settings->get('section_headings', [])),
            'locale_mode' => $settings->get('locale_mode', 'multilingual'),
            'webhooks' => array_replace_recursive(self::DEFAULT_WEBHOOKS, $settings->get('webhooks', [])),
            'about_stats' => HotelSetting::defaultedAboutStats($settings->get('about_stats', [])),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('Platform'))
                    ->description(__('Restricts the entire public site to a single language and currency, instead of letting guests choose.'))
                    ->schema([
                        Select::make('locale_mode')
                            ->label(__('Global Language Mode'))
                            ->helperText(__('Strict Russian locks the site to Russian and Rubles; Strict English locks it to English and US Dollars. The language and currency switchers are hidden from guests in either mode.'))
                            ->options([
                                'multilingual' => __('Full Multilingual'),
                                'ru_only' => __('Strict Russian Mode'),
                                'en_only' => __('Strict English Mode'),
                            ])
                            ->default('multilingual')
                            ->required(),
                    ]),

                Section::make(__('Homepage hero'))
                    ->description(__('Controls the title, tagline, promo banner and background slideshow on the public homepage.'))
                    ->schema([
                        ...((($restricted = HotelSetting::restrictedLocale()) !== null)
                            ? [
                                TextInput::make("hotel_name.{$restricted}")
                                    ->label(__('Hotel name'))
                                    ->required()
                                    ->maxLength(100),
                                Hidden::make('hotel_name.'.($restricted === 'en' ? 'ru' : 'en')),
                            ]
                            : [
                                Tabs::make('hotel_name_locale')
                                    ->label(__('Hotel name'))
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->schema([
                                                TextInput::make('hotel_name.en')
                                                    ->label(__('Hotel name'))
                                                    ->required()
                                                    ->maxLength(100),
                                            ]),
                                        Tab::make(__('Russian'))
                                            ->schema([
                                                TextInput::make('hotel_name.ru')
                                                    ->label(__('Hotel name'))
                                                    ->required()
                                                    ->maxLength(100),
                                            ]),
                                    ]),
                            ]
                        ),
                        ...($restricted !== null
                            ? [
                                TextInput::make("tagline.{$restricted}")
                                    ->label(__('Tagline'))
                                    ->maxLength(150),
                                Hidden::make('tagline.'.($restricted === 'en' ? 'ru' : 'en')),
                            ]
                            : [
                                Tabs::make('tagline_locale')
                                    ->label(__('Tagline'))
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->schema([
                                                TextInput::make('tagline.en')
                                                    ->label(__('Tagline'))
                                                    ->maxLength(150),
                                            ]),
                                        Tab::make(__('Russian'))
                                            ->schema([
                                                TextInput::make('tagline.ru')
                                                    ->label(__('Tagline'))
                                                    ->maxLength(150),
                                            ]),
                                    ]),
                            ]
                        ),
                        ...($restricted !== null
                            ? [
                                Textarea::make("promo_banner.{$restricted}")
                                    ->label(__('Promo banner'))
                                    ->helperText(__('Shown as a pill above the "View rooms" button. Leave blank to hide it.'))
                                    ->rows(2),
                                Hidden::make('promo_banner.'.($restricted === 'en' ? 'ru' : 'en')),
                            ]
                            : [
                                Tabs::make('promo_banner_locale')
                                    ->label(__('Promo banner'))
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->schema([
                                                Textarea::make('promo_banner.en')
                                                    ->label(__('Promo banner'))
                                                    ->helperText(__('Shown as a pill above the "View rooms" button. Leave both languages blank to hide it.'))
                                                    ->rows(2),
                                            ]),
                                        Tab::make(__('Russian'))
                                            ->schema([
                                                Textarea::make('promo_banner.ru')
                                                    ->label(__('Promo banner'))
                                                    ->helperText(__('Shown to guests browsing the site in Russian. Left blank, the English text above is shown instead.'))
                                                    ->rows(2),
                                            ]),
                                    ]),
                            ]
                        ),
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
                        ...(($restricted = HotelSetting::restrictedLocale()) !== null
                            ? [
                                Grid::make(2)->schema(self::sectionHeadingFields($restricted)),
                                ...self::hiddenSectionHeadingFields($restricted === 'en' ? 'ru' : 'en'),
                            ]
                            : [
                                Tabs::make('section_headings_locale')
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->columns(2)
                                            ->schema(self::sectionHeadingFields('en')),
                                        Tab::make(__('Russian'))
                                            ->columns(2)
                                            ->schema(self::sectionHeadingFields('ru')),
                                    ]),
                            ]
                        ),
                    ]),

                Section::make(__('About Us Statistics'))
                    ->description(__('The three marketing badges shown on the About Us section (e.g. "20+ Years", "48 Rooms").'))
                    ->schema([
                        ...(($restricted = HotelSetting::restrictedLocale()) !== null
                            ? [
                                Grid::make(3)->schema(self::aboutStatsFields($restricted)),
                                ...self::hiddenAboutStatsFields($restricted === 'en' ? 'ru' : 'en'),
                            ]
                            : [
                                Tabs::make('about_stats_locale')
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->columns(3)
                                            ->schema(self::aboutStatsFields('en')),
                                        Tab::make(__('Russian'))
                                            ->columns(3)
                                            ->schema(self::aboutStatsFields('ru')),
                                    ]),
                            ]
                        ),
                    ]),

                Section::make(__('Notification Settings'))
                    ->description(__('Forward every new booking to these channels at once — see each one\'s own hint below. Only a channel that is both switched on and has a URL filled in actually receives anything.'))
                    ->schema([
                        Section::make(__('Telegram Webhook'))
                            ->description(__('Connect an external router or a direct link for Telegram bots here.'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('webhooks.telegram.url')
                                    ->label(__('Endpoint URL'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('webhooks.telegram.channel_id')
                                    ->label(__('Channel Identifier'))
                                    ->maxLength(100),
                                Toggle::make('webhooks.telegram.active')
                                    ->label(__('Active'))
                                    ->default(false),
                            ]),

                        Section::make(__('WhatsApp Webhook'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('webhooks.whatsapp.url')
                                    ->label(__('API Instance Gateway URL'))
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('webhooks.whatsapp.active')
                                    ->label(__('Active'))
                                    ->default(false),
                            ]),

                        Section::make(__('Custom Corporate Gateway'))
                            ->description(__('A backup channel for integrating with corporate messengers (MAX, MyTeam, TamTam).'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('webhooks.custom.url')
                                    ->label(__('Universal Webhook Endpoint URL'))
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('webhooks.custom.active')
                                    ->label(__('Active'))
                                    ->default(false),
                                Textarea::make('webhooks.custom.auth_header')
                                    ->label(__('Authorization Header'))
                                    ->rows(2)
                                    ->columnSpanFull(),
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

    /**
     * Preserves the non-restricted language's section headings, unseen,
     * while only one language's fields are shown — the same reasoning as
     * every other Hidden field on this page.
     *
     * @return array<int, Hidden>
     */
    private static function hiddenSectionHeadingFields(string $locale): array
    {
        return array_map(
            fn (string $section): Hidden => Hidden::make("section_headings.{$section}.{$locale}"),
            ['rooms', 'pricing', 'about', 'faq', 'contact'],
        );
    }

    /**
     * One grouped Fieldset per badge (label + value), for a single locale
     * tab. `about_stats.years.label.en` dot-notation naturally builds the
     * nested {badge: {field: {locale: value}}} structure.
     *
     * @return array<int, Fieldset>
     */
    private static function aboutStatsFields(string $locale): array
    {
        $badges = [
            'years' => __('Years badge'),
            'rooms' => __('Rooms badge'),
            'guests' => __('Guests badge'),
        ];

        return array_map(
            fn (string $badge, string $label): Fieldset => Fieldset::make($label)
                ->schema([
                    TextInput::make("about_stats.{$badge}.label.{$locale}")
                        ->label(__('Label'))
                        ->maxLength(30),
                    TextInput::make("about_stats.{$badge}.value.{$locale}")
                        ->label(__('Value'))
                        ->maxLength(20),
                ]),
            array_keys($badges),
            array_values($badges),
        );
    }

    /**
     * Preserves the non-restricted language's badge text, unseen, while
     * only one language's fields are shown — the same reasoning as every
     * other Hidden field on this page.
     *
     * @return array<int, Hidden>
     */
    private static function hiddenAboutStatsFields(string $locale): array
    {
        return collect(['years', 'rooms', 'guests'])
            ->flatMap(fn (string $badge): array => [
                Hidden::make("about_stats.{$badge}.label.{$locale}"),
                Hidden::make("about_stats.{$badge}.value.{$locale}"),
            ])
            ->all();
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
