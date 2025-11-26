<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="FindPermissionsRequest",
 *     type="object",
 *     @OA\Property(
 *         property="curps",
 *         type="array",
 *         description="Array de CURPs de los usuarios a consultar permisos (opcional, no enviar si se usa role)",
 *         example={"LOPA800101HDFRNL09", "MARA900202MDFRTN05"},
 *         @OA\Items(
 *             type="string",
 *             example="LOPA800101HDFRNL09",
 *             description="CURP de un usuario existente"
 *         )
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *        description="Nombre del rol para consultar permisos (opcional, no enviar si se usan curps)",
 *        example="admin"
 *     )
 * )
 */

class FindPermissionsRequest extends FormRequest
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
            'curps' => ['nullable', 'array'],
            'curps.*' => ['string', 'exists:users,curp'],
            'curps.*' => ['string', 'size:18'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ];
    }

    protected function prepareForValidation()
    {
        $curps = $this->query('curps');

        if (!empty($curps) && !is_array($curps)) {
            $curps = [$curps];
        }

        $this->merge([
            'curps' => $curps,
            'role'  => $this->query('role')
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $curps = $this->input('curps');
            $role  = $this->input('role');

            if (empty($curps) && empty($role)) {
                $validator->errors()->add('curps', 'Debes enviar curps o role.');
                $validator->errors()->add('role', 'Debes enviar curps o role.');
            }

            if (!empty($curps) && !empty($role)) {
                $validator->errors()->add('curps', 'Solo debes enviar curps o role, no ambos.');
                $validator->errors()->add('role', 'Solo debes enviar curps o role, no ambos.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'curps.array' => 'El campo curps debe ser un array.',
            'curps.*.exists' => 'Una o más CURPs no existen en el sistema.',
            'curps.*.size' => 'Las CURPs deben ser de 18 caracteres',
            'role.exists' => 'El rol proporcionado no existe.',
        ];
    }
}
