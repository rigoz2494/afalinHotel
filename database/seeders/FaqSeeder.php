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
            ],
            [
                'Is parking available on site?',
                'Yes, we offer complimentary private parking for all registered guests, including secure overnight parking.',
            ],
            [
                'Do you allow pets?',
                'Well-behaved pets are welcome in select rooms for a small daily fee. Let us know in advance so we can prepare the room.',
            ],
            [
                'Is breakfast included in the room rate?',
                'A seasonal breakfast buffet is included with most rates and served daily from 7:00 to 10:30 AM in our restaurant.',
            ],
            [
                'Can I cancel or modify my reservation?',
                "Reservations can be cancelled free of charge up to 48 hours before arrival. Inside that window, one night's rate may apply.",
            ],
        ];

        foreach ($faqs as $index => [$question, $answer]) {
            Faq::updateOrCreate(['question' => $question], [
                'answer' => $answer,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
