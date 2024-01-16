<?php

namespace App\Http\Requests\Auth;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerificationEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'code'=>'required|size:4',
            'email'=>'required|email'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array {
        return [
            'required' => 'El campo es requerido',
            'size'=>'La longitud del código debe ser 4',
            'email'=>'El correo tiene un formato incorrecto'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest($validator->errors());
        throw new HttpResponseException(response()->json($r,$r->code));
    }
}
