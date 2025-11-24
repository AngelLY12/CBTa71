<?php

namespace App\Http\Requests\Admin;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string',
            'last_name'  => 'required|string',
            'email'  => 'required|email',
            'phone_number'  => 'required|string',
            'birthdate' => 'sometimes|required|date|date_format:Y-m-d',
            'gender'       => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserGender::cases())),
            ],
            'curp' => 'required|string',
            'address' => 'sometimes|required|array',
            'blood_type'   => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserBloodType::cases())),
            ],
            'registration_date' => 'sometimes|required|date|date_format:Y-m-d',
            'status' => [
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserStatus::cases()))
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'birthdate.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birthdate.date_format' => 'La fecha de nacimiento debe tener el formato AAAA-MM-DD.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in'          => 'El género no es válido.',
            'curp.required' => 'La CURP es obligatoria.',
            'address.array' => 'La dirección debe ser un arreglo válido.',
            'blood_type.required' => 'El tipo de sangre es obligatorio.',
            'blood_type.in'      => 'El tipo de sangre no es válido.',
            'registration_date.date' => 'La fecha de registro debe ser una fecha válida.',
            'registration_date.date_format' => 'La fecha de registro debe tener el formato AAAA-MM-DD.',
            'status.required' => 'El estatus es obligatorio.',
            'status.in' => 'El estatus proporcionado no es válido.',
        ];
    }
}
