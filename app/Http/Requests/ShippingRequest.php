<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'description'=>'required',
            'user_id'=>'required'
        ];
    }

    public function messages()
    {
        return [
          'description.required'=>'االوصف مطلوب',
          'user_id.required'=>'اسم المستخدم مطلوب'
        ];
    }
}
