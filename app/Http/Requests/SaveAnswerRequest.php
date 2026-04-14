<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_option_ids' => ['present', 'array'],
            'selected_option_ids.*' => ['integer'],
            'flagged' => ['required', 'boolean'],
        ];
    }
}
