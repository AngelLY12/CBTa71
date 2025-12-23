<?php

namespace App\Http\Requests\Payments\Staff;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdatePaymentConceptRelationsRequest",
 *     type="object",
 *      @OA\Property(
 *          property="is_global",
 *          type="boolean",
 *          description="Indica si aplica globalmente",
 *          example=false
 *      ),
 *     @OA\Property(
 *         property="applies_to",
 *         ref="#/components/schemas/PaymentConceptAppliesTo",
 *     ),
 *     @OA\Property(
 *         property="semestres",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array de semestres a los que aplica (opcional)",
 *         example={1,2,3}
 *     ),
 *     @OA\Property(
 *         property="careers",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         description="Array de IDs de carreras a los que aplica (opcional)",
 *         example={3,5}
 *     ),
 *     @OA\Property(
 *         property="students",
 *         type="array",
 *         @OA\Items(type="string"),
 *         description="Array de IDs de estudiantes a los que aplica (opcional)",
 *         example={"21","22","23"}
 *     ),
 *     @OA\Property(
 *         property="replaceRelations",
 *         type="boolean",
 *         description="Indica si se deben reemplazar las relaciones existentes",
 *         example=true
 *     ),
 *      @OA\Property(
 *         property="exceptionStudents",
 *         type="array",
 *         description="Array de CURPs de estudiantes a los que no aplica el concepto por alguna razón(opcional)",
 *         @OA\Items(type="string"),
 *         example={"11","60","90"}
 *     ),
 *     @OA\Property(
 *         property="replaceExceptions",
 *         type="boolean",
 *         description="Indica si se deben reemplazar las relaciones de exceptions",
 *         example=true
 *     ),
 *     @OA\Property(
 *          property="removeAllExceptions",
 *          type="boolean",
 *          description="Indica si se deben eliminar todas las exceptions",
 *          example=true
 *      ),
 *     @OA\Property(
 *          property="applicantTags",
 *          type="array",
 *          description="Array de casos especiales para aplicar un concepto (opcional)",
 *          @OA\Items(type="string"),
 *          example={"applicants"}
 *      ),
 * )
 */
class UpdatePaymentConceptRelationsRequest extends FormRequest
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
            'is_global'        => 'sometimes|required|boolean',
            'applies_to'       => ['nullable','string','in:' . implode(',', array_map(fn($case) => $case->value, PaymentConceptAppliesTo::cases()))],
            'semestres'      => 'nullable|array',
            'semestres.*'    => 'integer',
            'careers'        => 'nullable|array',
            'careers.*'      => 'integer',
            'students'       => 'nullable|array',
            'students.*'     => 'string',
            'exceptionStudents'    => 'nullable|array',
            'exceptionStudents.*'  => 'string',
            'replaceRelations' => 'sometimes|required|boolean',
            'replaceExceptions' => 'sometimes|required|boolean',
            'removeAllExceptions' => 'sometimes|required|boolean',
            'applicantTags' => 'nullable|array',
            'applicantTags.*' => 'string|in:' . implode(',', array_map(fn($case) => $case->value, PaymentConceptApplicantType::cases())),
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('is_global')) {
            $this->merge([
                'is_global' => filter_var($this->is_global, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('replaceRelations')) {
            $this->merge([
                'replaceRelations' => filter_var($this->replaceRelations, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('replaceExceptions')) {
            $this->merge([
                'replaceExceptions' => filter_var($this->replaceExceptions, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
        if ($this->has('removeAllExceptions')) {
            $this->merge([
                'removeAllExceptions' => filter_var($this->removeAllExceptions, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('applies_to')) {
            $this->merge([
                'applies_to' => strtolower($this->applies_to),
            ]);
        }
        if ($this->has('applicantTags')) {
            $this->merge([
                'applicantTags' => array_map('strtolower', $this->applicantTags),
            ]);
        }
        foreach (['students', 'exceptionStudents'] as $field) {
            if ($this->has($field) && is_array($this->$field)) {
                $this->merge([
                    $field => array_map(fn($value) => strip_tags($value), $this->$field),
                ]);
            }
        }

    }

    public function messages(): array
    {
        return [
            'is_global.boolean'     => 'El campo is_global debe ser booleano.',
            'applies_to.in'         => 'El valor de applies_to no es válido.',
            'semestres.array'        => 'Semestres debe ser un arreglo.',
            'semestres.*.integer'    => 'Cada semestre debe ser un número entero.',
            'careers.array'          => 'Careers debe ser un arreglo.',
            'careers.*.integer'      => 'Cada career debe ser un número entero.',
            'students.array'         => 'Students debe ser un arreglo.',
            'students.*.string'      => 'Cada student debe ser una cadena válida.',
            'exceptionStudents.array'     => 'exceptionStudents debe ser un arreglo.',
            'exceptionStudents.*.string'  => 'Cada exceptionStudent debe ser una cadena válida.',
            'replaceRelations.boolean' => 'replaceRelations debe ser booleano.',
            'replaceExceptions.boolean' => 'replaceExceptions debe ser booleano.',
            'removeAllExceptions.boolean' => 'removeAllExceptions debe ser booleano.',
            'applicantTags.array' => 'ApplicantTags debe ser un arreglo.',
            'applicantTags.*.string' => 'Cada applicantTag debe ser una cadena válida.',
            'applicantTags.*.in' => 'Cada applicantTag debe ser uno de los valores permitidos: ' . implode(', ', array_map(fn($case) => $case->value, PaymentConceptApplicantType::cases())),
        ];
    }
}
