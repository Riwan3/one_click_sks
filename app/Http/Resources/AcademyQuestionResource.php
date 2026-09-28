<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademyQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_type' => $this->question_type,
            'question' => $this->question,
            'option_a' => $this->option_a,
            'option_b' => $this->option_b,
            'option_c' => $this->option_c,
            'option_d' => $this->option_d,
            'correct' => $this->correct,
            'explanation' => $this->explanation,
        ];
    }
}
