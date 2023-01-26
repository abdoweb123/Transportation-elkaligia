<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageStoreRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'title'=>'required',
            'max_trips'=>'required',
            'stationFrom_id'=>'required',
            'stationTo_id'=>'required',
            'max_duration'=>'required',
            'total'=>'required',
            'type'=>'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required'=>'اسم الرحلة مطلوب',
            'max_trips.required'=>'أقصى عدد للرحلات مطلوب',
            'stationFrom_id.required'=>'محطة الانطلاق مطلوبة',
            'stationTo_id.required'=>'محطة الوصول مطلوبة',
            'max_duration.required'=>'مدة الاشتراك بالأيام مطلوبة',
            'total.required'=>'المبلغ الكلي مطلوب',
            'type.required'=>'نوع الرحلة مطلوبة',
        ];
    }


}
