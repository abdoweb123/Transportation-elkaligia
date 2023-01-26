<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'title'=>'required',
            'total'=>'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required'=>'اسم الرحلة مطلوب',
            'total.required'=>'المبلغ الكلي مطلوب',
        ];
    }


}
