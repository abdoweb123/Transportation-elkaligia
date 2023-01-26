<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManuallyFuelRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'bus_id'=>'required',
            'distance'=>'required',
            'comments'=>'required',
        ];
    }

    public function messages()
    {
        return [
          'bus_id.required'=>'كود الحافلة مطلوب',
          'distance.required'=>'المسافة مطلوبة',
          'comments.required'=>'التعليق مطلوب',
        ];
    }


}
