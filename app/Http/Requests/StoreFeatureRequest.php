<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeatureRequest extends FormRequest
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
    public function rules():array
    {

        return [
            'title' => 'required|max:256',
            'board_id' => 'required|integer',
            'stage_id' => 'required|integer',
            'due_date' => 'date_format:Y-m-d H:i:s'
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
            'title.max' => 'Debe tener un máximo de 256 carcatéres',
            'date_format' => 'El campo debe tener el siguiente formato: yyyy-MM-dd HH:mm:ss',
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
