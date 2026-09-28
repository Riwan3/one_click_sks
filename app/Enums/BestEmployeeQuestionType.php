<?php

namespace App\Enums;

enum BestEmployeeQuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case ShortText = 'short_text';
    case Essay = 'essay';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Multiple Choice',
            self::ShortText => 'Teks Singkat',
            self::Essay => 'Essay',
        };
    }

    public function isManuallyGraded(): bool
    {
        return $this !== self::MultipleChoice;
    }
}
