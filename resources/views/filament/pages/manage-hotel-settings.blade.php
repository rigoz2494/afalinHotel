<x-filament-panels::page>
    @if ($this->section !== 'account')
        <nav aria-label="{{ __('Site Settings') }}" class="flex flex-wrap gap-2">
            @foreach ($this->getSettingsLinks() as $section => $label)
                <x-filament::button
                    tag="a"
                    :href="$this->getUrl(['section' => $section])"
                    :color="$this->section === $section ? 'primary' : 'gray'"
                    :aria-current="$this->section === $section ? 'page' : null"
                >
                    {{ $label }}
                </x-filament::button>
            @endforeach
        </nav>
    @endif

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                {{ __('Save settings') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
