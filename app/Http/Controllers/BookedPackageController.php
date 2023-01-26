<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookedPackageRequest;
use App\Models\BookedPackage;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;

class BookedPackageController extends Controller
{

    /*** get all booked packages ***/
    public function index()
    {
        $bookedPackages = BookedPackage::latest()->paginate(10);
        $packages = Package::select('id','title')->get();
        $users = User::select('id','name','mobile')->get();
        return view('pages.BookedPackages.index', compact('bookedPackages','packages','users'));
    }


    /*** edit page  ***/
    public function edit(BookedPackage $bookedPackage)
    {
        $packages = Package::select('id','title')->get();
        $users = User::select('id','name','mobile')->get();
        return view('pages.BookedPackages.edit',compact('bookedPackage','packages','users'));
    }



    /*** create booked package ***/
    public function store(BookedPackageRequest $request)
    {
        BookedPackage::create([
            'package_id'=>$request->package_id,
            'user_id'=>$request->user_id,
            'admin_id'=>auth('admin')->id(),
            'startDate'=>$request->startDate,
            'used'=>0,
            'active'=>1,
        ]);

        return redirect()->back()->with('alert-success','تم حفظ البيانات بنجاح');
    }



    /*** update booked package ***/
    public function update(Request $request, BookedPackage $bookedPackage)
    {

        $bookedPackage->package_id = $request['package_id'];
        $bookedPackage->startDate = $request['startDate'];

        if ($request->user_id != null)
        {
            $bookedPackage->user_id = $request['user_id'];
        }

        $bookedPackage->update();

        return redirect()->route('bookedPackages.index')->with('alert-success','تم تحديث البيانات بنجاح');
    }



    /*** delete booked packages ***/
    public function destroy(BookedPackage $bookedPackage)
    {
        $bookedPackage->delete();
        return redirect()->back()->with('alert-success','تم حذف البيانات بنجاح');
    }
}
