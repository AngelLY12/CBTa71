<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'role.exists' => 'El rol proporcionado no existe.',
        ];
    }
}
