<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieListRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_public' => ['boolean'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama list wajib diisi.',
            'name.max' => 'Nama list maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'cover_photo.image' => 'Cover harus berupa gambar.',
            'cover_photo.mimes' => 'Cover harus berformat jpg, jpeg, png, atau webp.',
            'cover_photo.max' => 'Cover maksimal 2MB.',
        ];
    }
}
