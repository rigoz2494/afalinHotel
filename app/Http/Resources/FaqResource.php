<?php

namespace App\Http\Resources;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Faq
 */
class FaqResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
            // Null until the admin translates it; the frontend falls back to
            // the English text above when these are missing.
            'question_ru' => $this->question_ru,
            'answer_ru' => $this->answer_ru,
        ];
    }
}
