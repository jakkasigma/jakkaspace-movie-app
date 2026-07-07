<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:100',
                Rule::unique('themes', 'slug')->ignore($this->route('theme')),
            ],
            'avatar_border_css' => ['required', 'string'],
            'accent_color' => ['required', 'string', 'max:7'],
            'badge_icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tema wajib diisi.',
            'slug.required' => 'Slug tema wajib diisi.',
            'slug.unique' => 'Slug tema sudah digunakan.',
            'avatar_border_css.required' => 'CSS border avatar wajib diisi.',
            'accent_color.required' => 'Warna aksen wajib diisi.',
        ];
    }
}
