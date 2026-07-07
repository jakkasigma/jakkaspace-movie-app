<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GrantPlusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'period' => ['required', 'in:monthly,yearly'],
            'tier' => ['required', 'in:plus,plus_plus'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Pilih user.',
            'user_id.exists' => 'User tidak ditemukan.',
            'period.required' => 'Pilih periode subscription.',
            'tier.required' => 'Pilih tier subscription.',
        ];
    }
}
