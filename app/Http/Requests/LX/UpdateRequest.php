<?php

namespace App\Http\Requests\LX;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
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
            //
            'pro_name' => 'required',
            'pro_date' => 'required',
            'pro_address' => 'required',
            'host' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'pro_name.required' => '项目名称不能为空',
            'pro_date.required'=> '举办日期不能为空',
            'pro_address.required'=> '比赛地点不能为空',
            'host.required'=> '主办单位不能为空',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(json_fail('参数错误!',$validator->errors()->all(),422)));
    }

}
