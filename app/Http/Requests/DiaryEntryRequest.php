<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiaryEntryRequest extends FormRequest
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
            'watched_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mood' => ['nullable', 'string', 'max:50'],
            'is_rewatch' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'watched_at.required' => 'Tanggal menonton wajib diisi.',
            'watched_at.before_or_equal' => 'Tanggal menonton tidak boleh di masa depan.',
            'notes.max' => 'Catatan maksimal 2000 karakter.',
            'mood.in' => 'Mood yang dipilih tidak valid.',
        ];
    }
}
