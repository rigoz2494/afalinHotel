<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageHotelSettings;
use App\Models\HotelSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ManageHotelSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_hero_images_are_stored_and_shown_back_in_the_form(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->fillForm([
                'hotel_name' => 'Test Hotel',
                'hero_images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $storedPaths = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->assertCount(2, $storedPaths);

        foreach ($storedPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        Livewire::test(ManageHotelSettings::class)
            ->assertFormSet(['hero_images' => $storedPaths]);
    }

    public function test_uploads_replace_the_previous_hero_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $remoteUrl = 'https://images.example.com/lobby.jpg';
        HotelSetting::query()->create(['key' => 'hero_images', 'value' => [$remoteUrl]]);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm([
                'hotel_name' => 'Test Hotel',
                'hero_images' => [UploadedFile::fake()->image('new.jpg')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $storedImages = HotelSetting::query()->where('key', 'hero_images')->firstOrFail()->value;

        $this->assertCount(1, $storedImages);
        $this->assertNotContains($remoteUrl, $storedImages);
        Storage::disk('public')->assertExists($storedImages[0]);
    }
}
