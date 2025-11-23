<?php

namespace App\Http\Requests\Payments\Staff;

use Illuminate\Foundation\Http\FormRequest;

class PaginationWithSearchRequest extends FormRequest
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
            'search'       => ['sometimes', 'string'],
            'perPage'      => ['sometimes', 'integer', 'min:1', 'max:200'],
            'page'         => ['sometimes', 'integer', 'min:1'],
            'forceRefresh' => ['sometimes', 'boolean'],
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
            'perPage.integer' => 'El valor de perPage debe ser un número entero.',
            'perPage.min'     => 'El valor de perPage debe ser al menos 1.',
            'perPage.max'     => 'El valor de perPage no puede ser mayor a 200.',
            'page.integer'    => 'El valor de page debe ser un número entero.',
            'page.min'        => 'El valor de page debe ser al menos 1.',
            'forceRefresh.boolean' => 'forceRefresh debe ser true o false.',
        ];
    }
}
