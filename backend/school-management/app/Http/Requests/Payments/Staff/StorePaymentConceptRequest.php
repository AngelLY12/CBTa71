<?php

namespace App\Http\Requests\Payments\Staff;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentConceptRequest extends FormRequest
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
            'concept_name' => 'required|string|max:50',
            'description'  => 'nullable|string|max:100',
            'status'       => ['sometimes', 'string', 'in:' . implode(',', array_map(fn($case) => $case->value, PaymentConceptStatus::cases()))],
            'start_date'   => 'required|date|date_format:Y-m-d',
            'end_date'     => 'nullable|date|date_format:Y-m-d',
            'amount'       => 'required|numeric',
            'is_global'    => 'required|boolean',
            'applies_to'   => ['required', 'string', 'in:' . implode(',', array_map(fn($case) => $case->value, PaymentConceptAppliesTo::cases()))],
            'semestres'    => 'nullable|array',
            'careers'      => 'nullable|array',
            'students'     => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'concept_name.required' => 'El nombre del concepto es obligatorio.',
            'concept_name.max'      => 'El nombre del concepto no puede exceder 50 caracteres.',
            'description.max'       => 'La descripción no puede exceder 100 caracteres.',
            'status.in'             => 'El estado proporcionado no es válido.',
            'start_date.date'       => 'La fecha de inicio debe ser una fecha válida.',
            'start_date.date_format'=> 'La fecha de inicio debe tener el formato YYYY-MM-DD.',
            'end_date.date'         => 'La fecha de fin debe ser una fecha válida.',
            'end_date.date_format'  => 'La fecha de fin debe tener el formato YYYY-MM-DD.',
            'amount.required'       => 'El monto es obligatorio.',
            'amount.numeric'        => 'El monto debe ser numérico.',
            'is_global.boolean'     => 'El campo is_global debe ser booleano.',
            'applies_to.in'         => 'El valor de applies_to no es válido.',
            'semestres.array'       => 'Semestres debe ser un arreglo.',
            'careers.array'         => 'Careers debe ser un arreglo.',
            'students.array'        => 'Students debe ser un arreglo.',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('is_global')) {
            $this->merge([
                'is_global' => filter_var($this->is_global, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

}
