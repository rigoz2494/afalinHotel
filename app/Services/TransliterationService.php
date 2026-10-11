<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Room names are often Russian, and their slugs feed directly into this
 * app's public, indexable `/rooms/{slug}` URLs — `Str::slug()`'s own
 * Cyrillic handling uses a GOST-style scheme (e.g. "х" -> "x", "ю" -> "iu")
 * that doesn't match how Russian search engines and readers expect a
 * transliterated URL to look. This instead uses the common "practical"
 * Russian transliteration (e.g. "х" -> "h", "ю" -> "yu") before handing
 * off to Str::slug() for the usual lowercasing/punctuation cleanup —
 * "Люкс двухкомнатный" becomes "lyuks-dvuhkomnatnyj", not "liuks-dvuxkomnatnyi".
 */
class TransliterationService
{
    /**
     * @var array<string, string>
     */
    private const array CYRILLIC_MAP = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    /**
     * A URL-safe slug from a name that may be Russian, English, or a mix
     * of both — any Cyrillic letters are transliterated first; plain Latin
     * text passes through untouched.
     */
    public function slug(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $transliterated = strtr(Str::lower($value), self::CYRILLIC_MAP);

        return Str::slug($transliterated);
    }
}
