<?php

namespace App\Services;

use App\Exceptions\TranslationException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Translates short admin-entered text via a free, no-key API. This only ever
 * runs when an admin clicks the "Auto-Translate" button — there is no
 * scheduled job or queue listener behind it.
 */
class TranslationService
{
    private const string ENDPOINT = 'https://api.mymemory.translated.net/get';

    public function translate(string $text, string $from, string $to): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        try {
            $response = Http::timeout(10)->get(self::ENDPOINT, [
                'q' => $text,
                'langpair' => "{$from}|{$to}",
            ]);
        } catch (Throwable $exception) {
            throw new TranslationException(__('Could not reach the translation service.'), previous: $exception);
        }

        if ($response->failed()) {
            throw new TranslationException(__('The translation service returned an error.'));
        }

        $translated = $response->json('responseData.translatedText');

        if (! is_string($translated) || $translated === '') {
            throw new TranslationException(__('The translation service did not return a translation.'));
        }

        return $translated;
    }

    /**
     * Fills in whichever pair of FAQ fields is empty, from whichever pair has
     * text — detecting the actual language that was typed, rather than
     * assuming the primary `question`/`answer` fields are always English.
     *
     * If the primary pair is the one with text but it turns out to be
     * written in Russian, it's moved into the "_ru" pair unchanged and the
     * primary pair becomes its English translation, so the primary fields
     * always end up English and "_ru" always ends up Russian, regardless of
     * which field the admin actually typed into.
     *
     * @param  array{question?: string|null, answer?: string|null, question_ru?: string|null, answer_ru?: string|null}  $fields
     * @return array<string, string> the fields to fill in; never includes a field that already had text
     */
    public function translateFaqFields(array $fields): array
    {
        $primaryQuestion = trim($fields['question'] ?? '');
        $primaryAnswer = trim($fields['answer'] ?? '');
        $russianQuestion = trim($fields['question_ru'] ?? '');
        $russianAnswer = trim($fields['answer_ru'] ?? '');

        $primaryHasText = $primaryQuestion !== '' || $primaryAnswer !== '';
        $russianHasText = $russianQuestion !== '' || $russianAnswer !== '';

        if ($primaryHasText && $russianHasText) {
            throw new TranslationException(__('Both languages already have content. Clear one side to translate it from the other.'));
        }

        if (! $primaryHasText && ! $russianHasText) {
            throw new TranslationException(__('Type a question and answer first.'));
        }

        if ($russianHasText) {
            // The "_ru" pair is unambiguous: translate it into the primary pair.
            return [
                'question' => $this->translate($russianQuestion, 'ru', 'en'),
                'answer' => $this->translate($russianAnswer, 'ru', 'en'),
            ];
        }

        if ($this->containsCyrillic($primaryQuestion.' '.$primaryAnswer)) {
            // Written in Russian despite sitting in the primary fields: keep it
            // as the "_ru" value, and translate it into the primary fields.
            return [
                'question_ru' => $primaryQuestion,
                'answer_ru' => $primaryAnswer,
                'question' => $this->translate($primaryQuestion, 'ru', 'en'),
                'answer' => $this->translate($primaryAnswer, 'ru', 'en'),
            ];
        }

        return [
            'question_ru' => $this->translate($primaryQuestion, 'en', 'ru'),
            'answer_ru' => $this->translate($primaryAnswer, 'en', 'ru'),
        ];
    }

    private function containsCyrillic(string $text): bool
    {
        return (bool) preg_match('/[\x{0400}-\x{04FF}]/u', $text);
    }
}
