<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'currentPassword' => 'required|string|min:8',
            'newPassword' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'currentPassword.required' => 'Debes proporcionar tu contraseña actual.',
            'currentPassword.string' => 'La contraseña actual debe ser una cadena de texto.',
            'currentPassword.min' => 'La contraseña actual debe tener al menos 8 caracteres.',
            'newPassword.required' => 'Debes proporcionar una nueva contraseña.',
            'newPassword.string' => 'La nueva contraseña debe ser una cadena de texto.',
            'newPassword.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
