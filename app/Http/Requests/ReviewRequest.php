<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'body' => ['nullable', 'string', 'max:5000'],
            'has_spoiler' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 10.',
            'body.max' => 'Review maksimal 5000 karakter.',
        ];
    }
}
