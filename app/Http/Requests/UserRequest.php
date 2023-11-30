<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Models\CustomResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
    public function rules(): array{

        return [
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'picture_url' => 'nullable|image',
            'email' => 'email',
            'color'=>'string|max:12|nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo es requerido',
            'string' => 'El campo debe ser un texto',
            'image' => 'El campo debe ser una imagen',
            'email' => 'El campo debe ser un correo electrónico',
            'email.unique' => 'El correo electrónico ya está en uso',
        ];
    }



    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest($validator->errors());
        throw new HttpResponseException(response()->json($r,$r->code));
    }
}
