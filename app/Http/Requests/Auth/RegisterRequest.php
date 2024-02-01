<?php

namespace App\Http\Requests\Auth;

use App\Models\CustomResponse;
use App\Rules\MimeTypeRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
    public function rules(): array {
        return [
            'username'=>'required',
            'email' => 'required|email|max:255',
            'password'=>'min:8',
            'name' => 'required',
            //'picture_url'=>['nullable',new MimeTypeRule],
//            'role_id'=>'required,uuid'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array {
        return [
            'required' => 'El Campo es requerido',
            'email:unique'=>'el correo ya existe',
            'email'=>'El email es incorrecto',
            'min'=>'La contraseña debe tener al menos 8 caractéres',
            'picture_url:mime'=>'Los formatos válidos son: jpg,png,jpeg',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest($validator->errors());
        throw new HttpResponseException(response()->json($r,$r->code));
    }
}
