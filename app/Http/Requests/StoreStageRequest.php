<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules():array
    {

        return [
            'name' => 'required|max:128',
            //'description' => 'string',
            'color' => 'max:12',
            'is_default' => 'required|integer',
            'is_final' => 'required|integer',
            //'order'=>'required|integer'
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
            'integer' => 'El campo debe de ser de tipo entero',
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
