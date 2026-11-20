<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'What time are check-in and check-out?',
                'Check-in is from 3:00 PM and check-out is until 11:00 AM. Early check-in and late check-out can be arranged, subject to availability.',
                'Во сколько заезд и выезд?',
                'Заезд с 15:00, выезд до 11:00. Ранний заезд и поздний выезд возможны при наличии свободных номеров.',
            ],
            [
                'Is parking available on site?',
                'Yes, we offer complimentary private parking for all registered guests, including secure overnight parking.',
                'Есть ли парковка?',
                'Да, для гостей доступна бесплатная частная парковка, включая охраняемую ночную стоянку.',
            ],
            [
                'Do you allow pets?',
                'Well-behaved pets are welcome in select rooms for a small daily fee. Let us know in advance so we can prepare the room.',
                'Можно ли приехать с питомцем?',
                'В некоторые номера можно заселиться с питомцем за небольшую ежедневную плату. Сообщите нам заранее.',
            ],
            [
                'Is breakfast included in the room rate?',
                'A seasonal breakfast buffet is included with most rates and served daily from 7:00 to 10:30 AM in our restaurant.',
                'Входит ли завтрак в стоимость номера?',
                'Сезонный завтрак включён в большинство тарифов и подаётся с 7:00 до 10:30 в ресторане.',
            ],
            [
                'Can I cancel or modify my reservation?',
                "Reservations can be cancelled free of charge up to 48 hours before arrival. Inside that window, one night's rate may apply.",
                'Можно ли отменить или изменить бронирование?',
                'Бронь можно отменить бесплатно не позднее чем за 48 часов до заезда. При более поздней отмене может взиматься стоимость одной ночи.',
            ],
        ];

        foreach ($faqs as $index => [$question, $answer, $questionRu, $answerRu]) {
            Faq::firstOrCreate(['question' => $question], [
                'answer' => $answer,
                'question_ru' => $questionRu,
                'answer_ru' => $answerRu,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
