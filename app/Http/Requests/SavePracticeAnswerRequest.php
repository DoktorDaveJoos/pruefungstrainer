<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePracticeAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_option_ids' => ['present', 'array'],
            'selected_option_ids.*' => ['integer', 'exists:answers,id'],
        ];
    }
}
