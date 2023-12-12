<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => 'nullable|max:512',
            'hours' => 'required|numeric',
            'minutes' => 'required|numeric',
            'full_minutes' => 'required|numeric',
            'date' => 'nullable|date_format:d/m/Y',
            'day' => 'nullable|numeric',
            'month' => 'nullable|numeric',
            'year' => 'nullable|numeric',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo es requerido',
            'numeric' => 'El campo debe ser un entero',
            'date:date_format' => 'El formato válido es dd/mm/yyyy',
            'date' => 'El formato válido es dd/mm/yyyy',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest($validator->errors());
        throw new HttpResponseException(response()->json($r,$r->code));
    }
}
