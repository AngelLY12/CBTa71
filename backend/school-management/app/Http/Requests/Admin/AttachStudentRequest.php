<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttachStudentRequest extends FormRequest
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
            'user_id' => 'required|int',
            'career_id' => 'required|int',
            'n_control' => 'required|string',
            'semestre' => 'required|int',
            'group' => 'required|string',
            'workshop' => 'required|string'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es obligatorio.',
            'user_id.int' => 'El ID del usuario debe ser un número entero.',
            'career_id.required' => 'El ID de la carrera es obligatorio.',
            'career_id.int' => 'El ID de la carrera debe ser un número entero.',
            'n_control.required' => 'El número de control es obligatorio.',
            'semestre.required' => 'El semestre es obligatorio.',
            'semestre.int' => 'El semestre debe ser un número entero.',
            'group.required' => 'El grupo es obligatorio.',
            'workshop.required' => 'El taller es obligatorio.',
        ];
    }
}
