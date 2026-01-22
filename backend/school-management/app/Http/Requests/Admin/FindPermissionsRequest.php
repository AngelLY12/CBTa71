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
            'curps' => ['prohibits:role', 'required_without:role', 'array'],
            'curps.*' => ['required', 'string', 'size:18', 'exists:users,curp'],
            'role' => ['prohibits:curps', 'required_without:curps', 'string', 'exists:roles,name'],
        ];
    }

    protected function prepareForValidation()
    {
        $curps = $this->input('curps');

        if (is_string($curps)) {
            $curps = array_map('trim', explode(',', $curps));
            $curps = array_filter($curps);
        }

        if (!empty($curps)) {
            $this->merge(['curps' => $curps]);
        }
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
