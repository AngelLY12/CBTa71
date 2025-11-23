<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('userId');
        return [
            'name'          => 'sometimes|required|string',
            'last_name'     => 'sometimes|required|string',
            'email'         => 'sometimes|required|email|unique:users,email,' . $userId,
            'phone_number'  => 'sometimes|required|string',
            'birthdate'     => 'sometimes|required|date|date_format:Y-m-d',
            'gender'        => 'sometimes|required|string',
            'address'       => 'sometimes|required|array',
            'blood_type'    => 'sometimes|required|string',
        ];
    }

    public function messages(): array
    {
        return [
        'email.unique' => 'El correo ya está registrado por otro usuario.',
        'email.email' => 'El correo no tiene un formato válido.',
        'name.required' => 'El nombre es obligatorio.',
        'last_name.required' => 'El apellido es obligatorio.',
        'birthdate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
        'birthdate.date_format' => 'La fecha de nacimiento debe tener el formato AAAA-MM-DD.',
        'phone_number.required' => 'El número de teléfono es obligatorio.',
        'address.array' => 'La dirección debe ser un arreglo válido.',        ];
    }
}
