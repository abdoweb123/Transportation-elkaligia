<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EfficiencyFuelsRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'bus_id'=>'required',
            'volume'=>'required',
            'total_cost'=>'required',
        ];
    }

    public function messages()
    {
        return [
          'bus_id.required'=>'كود الحافلة مطلوب',
          'volume.required'=>'كمية البنزين مطلوبة',
          'total_cost.required'=>'المبلغ الكلي المدفوع مطلوب',
        ];
    }
}
