<?php

namespace App\Http\Requests\Auth;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name","last_name","email","password","phone_number","curp","status"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del usuario",
 *         example="Juan"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="Apellido del usuario",
 *         example="Pérez López"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Correo electrónico del usuario",
 *         example="juan.perez@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Contraseña del usuario",
 *         example="SecretPass123"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         description="Número de teléfono del usuario",
 *         example="+52 5512345678"
 *     ),
 *     @OA\Property(
 *         property="birthdate",
 *         type="string",
 *         format="date",
 *         description="Fecha de nacimiento del usuario (YYYY-MM-DD)",
 *         example="1990-05-12"
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         ref="#/components/schemas/UserGender"
 *     ),
 *     @OA\Property(
 *         property="curp",
 *         type="string",
 *         description="CURP del usuario",
 *         example="GODE561231HDFABC09"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="array",
 *         description="Dirección del usuario",
 *         @OA\Items(type="string", example="Calle Falsa 123, Col. Centro, CDMX"),
 *         example={"Calle Falsa 123", "Col. Centro", "Ciudad de México"}
 *     ),
 *     @OA\Property(
 *         property="blood_type",
 *         ref="#/components/schemas/UserBloodType"
 *     ),
 *     @OA\Property(
 *         property="registration_date",
 *         type="string",
 *         format="date",
 *         description="Fecha de registro del usuario (YYYY-MM-DD)",
 *         example="2025-01-15"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         ref="#/components/schemas/UserStatus"
 *     )
 * )
 */

class RegisterRequest extends FormRequest
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
            'password'  => 'required',
            'phone_number'  => 'required|string',
            'birthdate' => 'sometimes|required|date|date_format:Y-m-d',
            'gender'       => [
                'sometimes',
                'required',
                'string',
                'in:' . implode(',', array_map(fn($case) => $case->value, UserGender::cases())),
            ],
            'curp' => 'required|string|max:18',
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

    public function prepareForValidation()
    {
        $this->merge([
            'name' => $this->has('name') ? strip_tags($this->name) : null,
            'last_name' => $this->has('last_name') ? strip_tags($this->last_name) : null,
            'phone_number' => $this->has('phone_number') ? strip_tags($this->phone_number) : null,
            'curp' => $this->has('curp') ? strip_tags($this->curp) : null,
        ]);

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

        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
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
