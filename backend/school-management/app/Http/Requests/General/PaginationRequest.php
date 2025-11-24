<?php

namespace App\Http\Requests\General;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
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
            'forceRefresh' => ['sometimes', 'boolean'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('forceRefresh')) {
            $this->merge([
                'forceRefresh' => filter_var($this->forceRefresh, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'forceRefresh.boolean' => 'El valor de forceRefresh debe ser verdadero o falso.',
            'perPage.integer' => 'perPage debe ser un número entero.',
            'perPage.min' => 'perPage debe ser al menos 1.',
            'perPage.max' => 'perPage no puede ser mayor a 200.',
            'page.integer' => 'page debe ser un número entero.',
            'page.min' => 'page debe ser al menos 1.',
        ];
    }
}
