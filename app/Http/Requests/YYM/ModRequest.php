<?php

namespace App\Http\Requests\YYM;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ModRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'pw' => 'required',
            'new_pw' => 'required',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw(new HttpResponseException(json_fail('参数错误', $validator->errors()->all(), 422)));
    }
}
