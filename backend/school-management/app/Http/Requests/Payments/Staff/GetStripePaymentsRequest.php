<?php

namespace App\Http\Requests\Payments\Staff;

use Illuminate\Foundation\Http\FormRequest;

class GetStripePaymentsRequest extends FormRequest
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
            'search'       => ['required', 'string'],
            'year'         => ['nullable', 'integer'],
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
            'search.required'       => 'El campo search es obligatorio.',
            'search.string'         => 'El campo search debe ser una cadena de texto.',
            'year.integer'          => 'El año debe ser un número entero.',
            'forceRefresh.boolean'  => 'forceRefresh debe ser un valor booleano.',
        ];
    }
}
