<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddMemberToWorkspaceRequest extends FormRequest
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
            'username' => 'string',
            'email' => 'string|email|required',
            'token' => 'string',
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
            'string' => 'El campo debe ser de tipo string',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest([
            'data'=>$validator->errors(),
        ]);
        throw new HttpResponseException(response()->json($r,$r->code));

    }
}
