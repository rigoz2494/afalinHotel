<?php

namespace App\Filament\Resources\Faqs\Concerns;

use App\Exceptions\TranslationException;
use App\Services\TranslationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Shared by CreateFaq and EditFaq: a header action that detects which
 * language was actually typed and fills in its opposite, so the behavior
 * can't drift between the two pages.
 */
trait HasTranslateFaqAction
{
    protected function translateFaqAction(): Action
    {
        return Action::make('translateFaq')
            ->label(__('Auto-Translate'))
            ->tooltip(__('Fill in a question and answer in either language, then click this — it detects which language you wrote and translates it into the other side automatically.'))
            ->icon(Heroicon::OutlinedLanguage)
            ->color('gray')
            // Manual only: nothing else ever calls this but the admin's click.
            ->action(function (TranslationService $translator): void {
                try {
                    $this->data = array_merge(
                        $this->data,
                        $translator->translateFaqFields($this->data),
                    );

                    Notification::make()
                        ->title(__('Translated'))
                        ->body(__('Review the translated fields below before saving.'))
                        ->success()
                        ->send();
                } catch (TranslationException $exception) {
                    Notification::make()
                        ->title(__('Translation failed'))
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
