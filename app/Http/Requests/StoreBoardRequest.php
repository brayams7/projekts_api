<?php

namespace App\Http\Requests;

use App\Models\CustomResponse;
use App\Rules\SvgMimeType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBoardRequest extends FormRequest
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
        if(request()->isMethod("post")){
            return [
                'name' => 'required|string|max:128',
                'description' => 'nullable|string',
                'bg_color' => 'nullable|string|max:12',
                //'bg_image'=>'nullable|image|mimes:jpeg,png,jpg,svg,svg+xml,image/svg+xml,image/svg|max:2048',
                'bg_image'=>['nullable', new SvgMimeType],
                'user_id' => 'required|uuid',
                'workspace_id' => 'required|uuid',
                'have_default_stages' => 'required|boolean',
            ];
        }else{
            return [
                'name' => 'required|string|max:128',
                'description' => 'nullable|string',
                'bg_color' => 'nullable|string|max:12',
                'bg_image'=>'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                'user_id' => 'required|integer',
                'workspace_id' => 'required|integer',
                // 'status'=>'required|integer'
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
        if(request()->isMethod('post')){
            return [
                'required' => 'El campo es requerido',
                'name.max' => 'Debe tener un máximo de 128 carcatéres',
                'bg_color.max' => 'El campo debe tener un máximo de 12 carcatéres',
                'bg_image.max' => 'El campo debe tener un máximo de 2 mb',
                'have_default_stages.boolean'=>'El campo debe ser un boolean'
            ];
        }else{
            return [
                'required' => 'El campo es requerido',
                // 'name.max' => 'Debe tener un máximo de 128 carcatéres',
                // 'bg_color.max' => 'El campo debe tener un máximo de 12 carcatéres',
                // 'bg_image.max' => 'El campo debe tener un máximo de 2 mb'
            ];
        }

    }

    public function failedValidation(Validator $validator)
    {
        $r = CustomResponse::badRequest([
            'data'=>$validator->errors(),
        ]);
        throw new HttpResponseException(response()->json($r,$r->code));

    }
}
