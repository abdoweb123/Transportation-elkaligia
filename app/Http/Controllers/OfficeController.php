<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfficeRequest;
use App\Models\Admin;
use App\Models\City;
use App\Models\Office;
use App\Models\Station;
use Illuminate\Http\Request;

class OfficeController extends Controller
{

    /*** get all offices ***/
    public function index()
    {
        $offices = Office::latest()->paginate(5);
        $cities = City::select('id','name')->get();
        $stations = Station::select('id','name')->get();
        return view('pages.Offices.index', compact('offices','cities','stations'));
    }



    /*** create an office ***/
    public function store(OfficeRequest $request)
    {
        try {
            $office = new Office();
            $office->name = ['en' => $request->name_en, 'ar' => $request->name_ar];
            $office->station_id = $request->station_id;
            $office->admin_id = auth('admin')->id();
            $office->save();
            return redirect()->route('offices.index')->with('alert-success','تم تسجيل البيانات بنجاح');
        }
        catch (\Exception $exception)
        {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }



    /*** update the office ***/
    public function update(OfficeRequest $request)
    {
        try {
            $office = Office::findOrFail($request->id);
            $office->name = ['en' => $request->name_en, 'ar' => $request->name_ar];
            $office->station_id = $request->station_id;
            $office->admin_id = auth('admin')->id();
            $office->update();
            return redirect()->route('offices.index')->with('alert-success','تم تحديث البيانات بنجاح');
        }
        catch (\Exception $exception)
        {
            return redirect()->back()->withErrors(['error' => $exception->getMessage()]);
        }
    }



    /*** delete the office ***/
    public function destroy(Request $request)
    {
//        $checkAdmin = Admin::where('office_id',$request->id)->first();
//
//        if ($checkAdmin)
//        {
//            return redirect()->back()->with('alert-danger','لا يمكنك حذف هذا المكتب لارتباط بعض الموظفين به');
//        }

        $office = Office::findOrFail($request->id)->delete();
        return redirect()->route('offices.index')->with('alert-success','تم حذف البيانات بنجاح');
    }

} //end of class
