<?php

namespace App\Http\Requests\User;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
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
            'gender'       => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserGender::cases())),
            ],
            'address'       => 'sometimes|required|array',
            'blood_type'   => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserBloodType::cases())),
            ],
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('gender')) {
            $this->merge([
                'gender' => strtolower($this->gender),
            ]);
        }

        if($this->has('blood_type'))
        {
            $this->merge([
                'blood_type' => strtoupper($this->blood_type)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'El correo ya está registrado por otro usuario.',
            'email.email' => 'El correo no tiene un formato válido.',
            'name.required' => 'El nombre es obligatorio.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in'          => 'El género no es válido.',
            'last_name.required' => 'El apellido es obligatorio.',
            'birthdate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birthdate.date_format' => 'La fecha de nacimiento debe tener el formato AAAA-MM-DD.',
            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'blood_type.required' => 'El tipo de sangre es obligatorio.',
            'blood_type.in'      => 'El tipo de sangre no es válido.',
            'address.array' => 'La dirección debe ser un arreglo válido.',
        ];
    }
}
