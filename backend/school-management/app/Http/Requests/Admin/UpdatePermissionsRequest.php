<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionsRequest extends FormRequest
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

            'role' => ['nullable', 'string', 'exists:roles,name'],

            'permissionsToAdd' => ['nullable', 'array'],
            'permissionsToAdd.*' => ['string', 'exists:permissions,name'],

            'permissionsToRemove' => ['nullable', 'array'],
            'permissionsToRemove.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $curps = $this->input('curps');
            $role = $this->input('role');

            $hasCurps = !empty($curps);
            $hasRole = !empty($role);

            if ($hasCurps && $hasRole) {
                $validator->errors()->add(
                    'curps',
                    'No puedes especificar CURPs y rol al mismo tiempo.'
                );
            }

            if (!$hasCurps && !$hasRole) {
                $validator->errors()->add(
                    'curps',
                    'Debes proporcionar al menos un array de CURPs o un rol.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'curps.array' => 'El campo curps debe ser un arreglo.',
            'curps.*.exists' => 'Una o más CURPs no existen en el sistema.',

            'role.exists' => 'El rol especificado no existe.',

            'permissionsToAdd.array' => 'permissionsToAdd debe ser un arreglo.',
            'permissionsToAdd.*.exists' => 'Un permiso a agregar no existe.',

            'permissionsToRemove.array' => 'permissionsToRemove debe ser un arreglo.',
            'permissionsToRemove.*.exists' => 'Un permiso a remover no existe.',
        ];
    }
}
