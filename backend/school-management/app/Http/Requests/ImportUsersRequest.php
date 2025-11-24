<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
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
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ];
    }

     public function messages(): array
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.file'     => 'Debe proporcionar un archivo válido.',
            'file.mimes'    => 'El archivo debe ser de tipo XLSX, XLS o CSV.',
        ];
    }
}
