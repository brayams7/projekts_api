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
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
// ...

public function rules()
{
    //$id = $this->route('id');

    return [
        // 'username' => [
        //     'string',
        //     Rule::unique('users', 'username')->ignore($id),
        // ],
        // 'name' => 'string',
        // 'picture_url' => [
        //     'sometimes',
        //     'image',
        //     Rule::dimensions()->maxWidth(1000)->maxHeight(1000),
        // ],
        // 'email' => [
        //     'email',
        //     Rule::unique('users', 'email')->ignore($id),
        // ],
    ];

}

public function messages()
{
    return [
        'username.unique' => 'El nombre de usuario ya está en uso.',
        'picture_url.image' => 'El campo :attribute debe ser una imagen válida.',
        'picture_url.dimensions' => 'La imagen no debe superar los 1000x1000 píxeles.',
        'email.unique' => 'El correo electrónico ya está en uso.',
    ];
}



    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest($validator->errors());
        throw new HttpResponseException(response()->json($r,$r->code));
    }
}
