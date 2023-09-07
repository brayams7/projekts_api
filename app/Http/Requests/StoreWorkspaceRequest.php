<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreWorkspaceRequest extends FormRequest
{

    // protected $redirect = false;

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
      if(request()->isMethod("post")){
        return [
          'name' => 'required|unique:workspaces|max:128',
          'initials' => 'required|max:2',
          'description' => 'string',
          'color' => 'required|max:12',
          'user_id' => 'required|integer',
          'workspace_type_id' => 'required|integer'
        ];
      }else{
        return [
          'name' => 'required|max:128',
          'initials' => 'string|max:2',
          'description' => 'required|string',
          'color' => 'required|string|max:12'
        ];
      }

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
            'name.unique' => 'El nombre ya existe',
            'name.max' => 'Debe tener un máximo de 128 carcatéres',
            'color.max' => 'El campo debe tener un máximo de 12 carcatéres',
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
