<?php

namespace App\Filament\Pages;

use App\Models\HotelSetting;
use App\Models\User;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
            'about_story' => HotelSetting::defaultedAboutStory($settings->get('about_story')),
            'preloader_phrases' => HotelSetting::defaultedPreloaderPhrases($settings->get('preloader_phrases')),
            // Never backed by a HotelSetting row — this is a thin, virtual
            // gateway onto the currently logged-in admin's own `users` row.
            // The password side is always blank: a password is never
            // redisplayed once set, only ever replaced.
            'admin_credentials' => [
                'email' => auth()->user()?->email,
                'password' => null,
            ],
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
                        ...($restricted !== null
                            ? [
                                Textarea::make("about_story.{$restricted}")
                                    ->label(__('About Us Story Text'))
                                    ->helperText(__('A blank line between paragraphs starts a new one on the page.'))
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Hidden::make('about_story.'.($restricted === 'en' ? 'ru' : 'en')),
                            ]
                            : [
                                Tabs::make('about_story_locale')
                                    ->label(__('About Us Story Text'))
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make(__('English'))
                                            ->schema([
                                                Textarea::make('about_story.en')
                                                    ->label(__('About Us Story Text'))
                                                    ->helperText(__('A blank line between paragraphs starts a new one on the page.'))
                                                    ->rows(6),
                                            ]),
                                        Tab::make(__('Russian'))
                                            ->schema([
                                                Textarea::make('about_story.ru')
                                                    ->label(__('About Us Story Text'))
                                                    ->rows(6),
                                            ]),
                                    ]),
                            ]
                        ),
                    ]),

                Section::make(__('Preloader Loading Phrases'))
                    ->description(__('Playful status lines the loading screen cycles through at random — purely decorative, never a real progress report.'))
                    ->schema([
                        Repeater::make('preloader_phrases')
                            ->label(null)
                            // Only the active site language's phrase is ever
                            // shown to a guest (see Preloader.vue), so in a
                            // single-language mode there's nothing to gain
                            // from showing the other language's field here
                            // — it would just confuse a non-technical admin
                            // into thinking both are required.
                            ->schema(fn (): array => (($restricted = HotelSetting::restrictedLocale()) !== null)
                                ? [
                                    TextInput::make($restricted)
                                        ->label(__('Phrase'))
                                        ->required()
                                        ->maxLength(100)
                                        ->columnSpanFull(),
                                    Hidden::make($restricted === 'en' ? 'ru' : 'en'),
                                ]
                                : [
                                    TextInput::make('en')
                                        ->label(__('English'))
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('ru')
                                        ->label(__('Russian'))
                                        ->required()
                                        ->maxLength(100),
                                ])
                            ->columns(2)
                            ->addActionLabel(__('Add a phrase'))
                            ->reorderableWithDragAndDrop()
                            ->minItems(1),
                    ]),

                Section::make(__('Admin Account Settings'))
                    ->description(__('Change the login email or password for your own admin account. Neither field is required — leave one or both blank to leave them unchanged.'))
                    ->schema([
                        TextInput::make('admin_credentials.email')
                            ->label(__('Admin Email/Login'))
                            ->helperText(__('The email address you sign in to the dashboard with. Changing it takes effect immediately — make sure you can still access that inbox before saving.'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('admin_credentials.password')
                            ->label(__('New Admin Password'))
                            ->helperText(__('Only fill this in if you want to change your password. It is never shown again once saved, for your own security.'))
                            ->placeholder(__('Leave blank to keep your current password unchanged.'))
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8),
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
                                    ->helperText(__('Paste your Telegram bot\'s webhook link here. Bookings will arrive instantly in the messenger as a neat card. Leave this blank if you don\'t need this integration.'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('webhooks.telegram.channel_id')
                                    ->label(__('Channel Identifier'))
                                    ->helperText(__('The unique ID of your Telegram chat or group. To find it, add the bot @userinfobot to your chat, or forward a message from the group to the bot @ShowJsonBot. A group\'s ID usually starts with a minus sign (e.g. -100123456789).'))
                                    ->maxLength(100),
                                Toggle::make('webhooks.telegram.active')
                                    ->label(__('Active'))
                                    ->helperText(self::webhookActiveHelperText())
                                    ->default(false),
                            ]),

                        Section::make(__('WhatsApp Webhook'))
                            ->description(__('Sends new booking details straight to a WhatsApp number or service, once you\'ve connected one below.'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('webhooks.whatsapp.url')
                                    ->label(__('API Instance Gateway URL'))
                                    ->helperText(__('The gateway address for sending WhatsApp messages (e.g. via Green-API or a similar service). The system will send a JSON package with the guest\'s contact details here to automatically notify the manager.'))
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('webhooks.whatsapp.active')
                                    ->label(__('Active'))
                                    ->helperText(self::webhookActiveHelperText())
                                    ->default(false),
                            ]),

                        Section::make(__('Custom Corporate Gateway'))
                            ->description(__('A backup channel for integrating with corporate messengers (MAX, MyTeam, TamTam).'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('webhooks.custom.url')
                                    ->label(__('Universal Webhook Endpoint URL'))
                                    ->helperText(__('A backup channel for direct integration with corporate messengers (MAX by Rostelecom, TamTam, VK Teams). Paste the webhook URL created in your corporate chatbot\'s own settings here. Laravel will send a secure JSON package with the full booking details to it.'))
                                    ->url()
                                    ->maxLength(255),
                                Toggle::make('webhooks.custom.active')
                                    ->label(__('Active'))
                                    ->helperText(self::webhookActiveHelperText())
                                    ->default(false),
                                Textarea::make('webhooks.custom.auth_header')
                                    ->label(__('Authorization Header'))
                                    ->helperText(__('A secret access code the receiving system requires, usually given to you by a developer (often starting with the word "Bearer"). Leave this blank unless you were specifically told to enter something here.'))
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

    /**
     * The same plain-language reminder under every webhook's "Active"
     * toggle — flipping it on without a working address above it does
     * nothing but silently fail, which is confusing for a non-technical
     * admin to debug.
     */
    private static function webhookActiveHelperText(): string
    {
        return __('Only turn this on after the address above is filled in and has been tested — otherwise nothing will be sent anywhere, and the booking itself is never affected either way.');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Never a HotelSetting row — this updates the real `users` table
        // directly instead, and is unset here so a plaintext password
        // never ends up written into the generic settings JSON blob below.
        $this->updateAdminCredentials($data['admin_credentials'] ?? []);
        unset($data['admin_credentials']);

        foreach ($data as $key => $value) {
            HotelSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // The password is never shown again, and the email reflects
        // whatever is actually saved (unchanged if the new one was
        // rejected as a duplicate) — never whatever was just typed.
        $this->data['admin_credentials'] = [
            'email' => auth()->user()?->email,
            'password' => null,
        ];

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }

    /**
     * Updates the currently logged-in admin's own `users` row — not
     * whichever account happens to be first in the table, so one admin's
     * edits here can never silently hijack a different admin's login in a
     * future multi-admin setup. Blank fields are left alone; a changed
     * email that collides with a different user's is rejected with its
     * own notification instead of crashing on the column's unique
     * constraint.
     *
     * @param  array{email?: string|null, password?: string|null}  $credentials
     */
    private function updateAdminCredentials(array $credentials): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $email = trim((string) ($credentials['email'] ?? ''));

        if ($email !== '' && $email !== $user->email) {
            if (User::query()->where('email', $email)->whereKeyNot($user->getKey())->exists()) {
                Notification::make()
                    ->title(__('That email is already in use by another account — the login email was not changed.'))
                    ->danger()
                    ->send();
            } else {
                $user->email = $email;
            }
        }

        $password = $credentials['password'] ?? null;

        if (filled($password)) {
            // Not Hash::make() directly: User::$password already casts to
            // 'hashed' (see the model), which calls it internally. Hashing
            // it again here would double-hash it and permanently lock the
            // admin out, since the stored hash would no longer match any
            // plaintext password at all.
            $user->password = $password;
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }
}
